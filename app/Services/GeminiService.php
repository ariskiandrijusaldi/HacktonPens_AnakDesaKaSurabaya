<?php

namespace App\Services;

use App\Services\Prompts\VoiceReportPrompt;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;
use Throwable;

/**
 * Layanan integrasi Google Gemini.
 *
 * Method lama generate() dipertahankan. Method baru difokuskan untuk
 * fitur "Laporan via Voice Note": analyzeVoiceReport(), convertToWavIfNeeded(),
 * dan ringkasanKlarifikasi().
 */
class GeminiService
{
    /**
     * Mime audio yang didukung langsung oleh Gemini.
     */
    private const SUPPORTED_AUDIO_MIME = [
        'audio/wav',
        'audio/x-wav',
        'audio/wave',
        'audio/mp3',
        'audio/mpeg',
        'audio/aiff',
        'audio/x-aiff',
        'audio/aac',
        'audio/ogg',
        'audio/oga',
        'audio/opus',
        'audio/flac',
        'audio/x-flac',
    ];

    private const ALLOWED_URGENSI = ['rendah', 'sedang', 'tinggi'];

    private const ALLOWED_STATUS_AUDIO = ['jelas', 'kurang_jelas', 'perlu_ulang'];

    /**
     * Ambang confidence yang memaksa status_audio menjadi "perlu_ulang".
     */
    private const CONFIDENCE_PERLU_ULANG = 40;

    /**
     * Method lama: hasilkan teks bebas dari sebuah prompt.
     *
     * Perilaku dipertahankan; hanya dipakai sebagai basis teks umum.
     */
    public function generate(string $prompt, ?string $systemInstruction = null): string
    {
        $payload = [
            'contents' => [[
                'role' => 'user',
                'parts' => [['text' => $prompt]],
            ]],
        ];

        if ($systemInstruction !== null && trim($systemInstruction) !== '') {
            $payload['systemInstruction'] = [
                'parts' => [['text' => $systemInstruction]],
            ];
        }

        return $this->extractText($this->callGemini($payload));
    }

    /**
     * Analisis laporan dari file audio voice note.
     *
     * @return array{
     *     transkrip: string,
     *     ringkasan: string,
     *     penjelasan_ai: string,
     *     kategori: string,
     *     urgensi: string,
     *     confidence: int,
     *     alasan_confidence: string,
     *     bagian_tidak_jelas: array<int, string>,
     *     pertanyaan_klarifikasi: array<int, string>,
     *     status_audio: string
     * }
     *
     * @throws RuntimeException bila file tidak ada, audio ditolak, atau respons tidak valid.
     */
    public function analyzeVoiceReport(string $audioPath, string $mimeType, ?string $konteks = null): array
    {
        $audio = $this->resolveAudioInput($audioPath, $mimeType);
        $bytes = $this->readAudioFile($audio['path']);

        $payload = [
            'systemInstruction' => [
                'parts' => [['text' => VoiceReportPrompt::systemInstruction()]],
            ],
            'contents' => [[
                'role' => 'user',
                'parts' => [
                    ['text' => VoiceReportPrompt::userPrompt($konteks)],
                    [
                        'inlineData' => [
                            'mimeType' => $audio['mimeType'],
                            'data' => base64_encode($bytes),
                        ],
                    ],
                ],
            ]],
            'generationConfig' => $this->generationConfig(),
        ];

        $text = $this->extractText($this->callGemini($payload));

        return $this->parseAndValidate($text);
    }

    /**
     * Konversi audio ke WAV bila mimeType tidak didukung Gemini.
     *
     * Memakai ffmpeg bila tersedia. Jika format sudah didukung, file
     * dikembalikan apa adanya. Jika ffmpeg tidak ada, melempar exception jelas.
     *
     * @return array{path: string, mimeType: string}
     *
     * @throws RuntimeException
     */
    public function convertToWavIfNeeded(string $audioPath, string $mimeType): array
    {
        if ($this->isSupportedAudioMime($mimeType)) {
            return ['path' => $audioPath, 'mimeType' => $mimeType];
        }

        if (! is_file($audioPath)) {
            throw new RuntimeException('File audio tidak ditemukan: '.$audioPath);
        }

        $ffmpeg = (new ExecutableFinder)->find('ffmpeg');

        if ($ffmpeg === null) {
            throw new RuntimeException(
                'Format audio "'.$mimeType.'" tidak didukung langsung oleh layanan AI, '
                .'dan ffmpeg tidak tersedia untuk konversi ke WAV. '
                .'Pasang ffmpeg, atau kirim audio dalam format WAV/MP3/OGG/AAC/FLAC.'
            );
        }

        $outputPath = $this->tempWavPath();

        $process = new Process([
            $ffmpeg,
            '-y',
            '-i', $audioPath,
            '-ar', '16000',
            '-ac', '1',
            '-f', 'wav',
            $outputPath,
        ]);
        $process->setTimeout(120);
        $process->run();

        if (! $process->isSuccessful() || ! is_file($outputPath)) {
            $detail = trim($process->getErrorOutput());
            @unlink($outputPath);

            throw new RuntimeException(
                'Gagal mengonversi audio ke WAV dengan ffmpeg.'
                .($detail !== '' ? ' Detail: '.mb_substr($detail, 0, 200) : '')
            );
        }

        return ['path' => $outputPath, 'mimeType' => 'audio/wav'];
    }

    /**
     * Gabungkan jawaban klarifikasi pengguna ke hasil analisis.
     *
     * Mengembalikan struktur output yang sama. Bila panggilan AI gagal,
     * dipakai penggabungan dasar agar tetap mengembalikan struktur lengkap.
     *
     * @param  array<string, mixed>  $hasilAnalisis
     * @return array<string, mixed>
     */
    public function ringkasanKlarifikasi(array $hasilAnalisis, string $jawabanPengguna): array
    {
        $hasilAnalisis = $this->normalizeResult($hasilAnalisis);

        $payload = [
            'systemInstruction' => [
                'parts' => [['text' => VoiceReportPrompt::clarificationInstruction()]],
            ],
            'contents' => [[
                'role' => 'user',
                'parts' => [[
                    'text' => VoiceReportPrompt::clarificationPrompt($hasilAnalisis, $jawabanPengguna),
                ]],
            ]],
            'generationConfig' => $this->generationConfig(),
        ];

        try {
            $text = $this->extractText($this->callGemini($payload));

            return $this->parseAndValidate($text);
        } catch (RuntimeException $e) {
            Log::warning('Gabung klarifikasi via AI gagal, memakai penggabungan dasar.', [
                'error' => $e->getMessage(),
            ]);

            return $this->mergeClarificationFallback($hasilAnalisis, $jawabanPengguna);
        }
    }

    // -----------------------------------------------------------------
    // Konfigurasi & pemanggilan API
    // -----------------------------------------------------------------

    private function generationConfig(): array
    {
        return [
            'responseMimeType' => 'application/json',
            'responseSchema' => $this->voiceReportSchema(),
            'temperature' => 0.2,
        ];
    }

    private function voiceReportSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'transkrip' => ['type' => 'string'],
                'ringkasan' => ['type' => 'string'],
                'penjelasan_ai' => ['type' => 'string'],
                'kategori' => ['type' => 'string'],
                'urgensi' => ['type' => 'string', 'enum' => self::ALLOWED_URGENSI],
                'confidence' => ['type' => 'integer'],
                'alasan_confidence' => ['type' => 'string'],
                'bagian_tidak_jelas' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                ],
                'pertanyaan_klarifikasi' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                ],
                'status_audio' => ['type' => 'string', 'enum' => self::ALLOWED_STATUS_AUDIO],
            ],
            'required' => [
                'transkrip',
                'ringkasan',
                'penjelasan_ai',
                'kategori',
                'urgensi',
                'confidence',
                'alasan_confidence',
                'bagian_tidak_jelas',
                'pertanyaan_klarifikasi',
                'status_audio',
            ],
        ];
    }

    /**
     * Panggil endpoint generateContent dengan retry untuk 429/5xx dan timeout.
     */
    private function callGemini(array $payload): array
    {
        $key = (string) config('services.gemini.key', '');

        if ($key === '') {
            throw new RuntimeException(
                'Konfigurasi Gemini belum diisi. Set GEMINI_API_KEY pada environment aplikasi.'
            );
        }

        $model = (string) config('services.gemini.model', 'gemini-flash-latest');
        $baseUrl = rtrim((string) config(
            'services.gemini.base_url',
            'https://generativelanguage.googleapis.com/v1beta'
        ), '/');
        $timeout = (int) config('services.gemini.timeout', 120);
        $maxRetry = max(0, (int) config('services.gemini.retry_times', 2));
        $retrySleepMs = max(0, (int) config('services.gemini.retry_sleep_ms', 1500));

        $url = $baseUrl.'/models/'.$model.':generateContent';

        $attempt = 0;

        while (true) {
            try {
                $response = Http::withHeaders(['x-goog-api-key' => $key])
                    ->acceptJson()
                    ->timeout($timeout)
                    ->post($url, $payload);
            } catch (ConnectionException $e) {
                if ($attempt < $maxRetry) {
                    $attempt++;
                    usleep($retrySleepMs * 1000);

                    continue;
                }

                throw new RuntimeException(
                    'Koneksi ke layanan AI timeout atau gagal. Periksa jaringan lalu coba lagi.',
                    0,
                    $e
                );
            }

            if ($response->successful()) {
                return $response->json() ?? [];
            }

            $status = $response->status();

            if ($this->isRetryableStatus($status) && $attempt < $maxRetry) {
                $attempt++;
                usleep($retrySleepMs * 1000);

                continue;
            }

            throw new RuntimeException(
                $this->friendlyHttpError($status, $response->json())
            );
        }
    }

    private function isRetryableStatus(int $status): bool
    {
        return $status === 429 || $status >= 500;
    }

    private function friendlyHttpError(int $status, ?array $body): string
    {
        $detail = trim((string) data_get($body, 'error.message', ''));

        return match (true) {
            $status === 429 => 'Kuota layanan AI sedang penuh (rate limit). Coba beberapa saat lagi.',
            $status === 403 => 'API key layanan AI tidak valid atau tidak memiliki akses.',
            $status === 400 && str_contains(mb_strtolower($detail), 'mime') => 'Format audio tidak didukung oleh layanan AI.',
            $status === 400 => 'Permintaan ke layanan AI ditolak. Periksa format atau isi audio.',
            $status >= 500 => 'Layanan AI sedang bermasalah (server error). Coba lagi nanti.',
            default => 'Layanan AI mengembalikan error (HTTP '.$status.').',
        }.$this->detailSuffix($detail);
    }

    private function detailSuffix(string $detail): string
    {
        $detail = trim((string) preg_replace('/\s+/', ' ', $detail));

        if ($detail === '') {
            return '';
        }

        return ' Detail: '.mb_substr($detail, 0, 200);
    }

    /**
     * Ambil teks dari respons kandidat; tangani blokir safety dan respons kosong.
     */
    private function extractText(array $response): string
    {
        $blockReason = data_get($response, 'promptFeedback.blockReason');

        if (! empty($blockReason)) {
            throw new RuntimeException(
                'Respons AI diblokir oleh filter keamanan ('.$blockReason.'). Tinjau audio secara manual.'
            );
        }

        $candidate = data_get($response, 'candidates.0');

        if (! is_array($candidate)) {
            throw new RuntimeException('Layanan AI tidak mengembalikan respons. Coba lagi.');
        }

        $finishReason = strtoupper((string) ($candidate['finishReason'] ?? ''));

        if (in_array($finishReason, ['SAFETY', 'PROHIBITED_CONTENT', 'BLOCKLIST'], true)) {
            throw new RuntimeException(
                'Respons AI diblokir oleh filter keamanan. Tinjau audio secara manual.'
            );
        }

        $text = '';
        foreach ((array) data_get($candidate, 'content.parts', []) as $part) {
            if (is_array($part) && isset($part['text']) && is_string($part['text'])) {
                $text .= $part['text'];
            }
        }

        if (trim($text) === '') {
            throw new RuntimeException(
                'Respons AI kosong. Coba lagi atau periksa kualitas audio.'
            );
        }

        return $text;
    }

    // -----------------------------------------------------------------
    // Pembacaan & penyiapan file audio
    // -----------------------------------------------------------------

    /**
     * @return array{path: string, mimeType: string}
     */
    private function resolveAudioInput(string $audioPath, string $mimeType): array
    {
        if ($this->isSupportedAudioMime($mimeType)) {
            return ['path' => $audioPath, 'mimeType' => $mimeType];
        }

        return $this->convertToWavIfNeeded($audioPath, $mimeType);
    }

    private function readAudioFile(string $audioPath): string
    {
        if (is_file($audioPath)) {
            $bytes = @file_get_contents($audioPath);

            if ($bytes === false || $bytes === '') {
                throw new RuntimeException('File audio tidak dapat dibaca atau kosong: '.$audioPath);
            }

            return $bytes;
        }

        try {
            if (Storage::exists($audioPath)) {
                $bytes = Storage::get($audioPath);

                if ($bytes === null || $bytes === '') {
                    throw new RuntimeException('File audio di storage kosong: '.$audioPath);
                }

                return $bytes;
            }
        } catch (RuntimeException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new RuntimeException('File audio tidak dapat diakses: '.$audioPath, 0, $e);
        }

        throw new RuntimeException('File audio tidak ditemukan: '.$audioPath);
    }

    private function isSupportedAudioMime(string $mimeType): bool
    {
        $mimeType = strtolower(trim(explode(';', $mimeType)[0]));

        return in_array($mimeType, self::SUPPORTED_AUDIO_MIME, true);
    }

    private function tempWavPath(): string
    {
        return sys_get_temp_dir().DIRECTORY_SEPARATOR.'vn_'.uniqid('', true).'.wav';
    }

    // -----------------------------------------------------------------
    // Parsing & validasi hasil
    // -----------------------------------------------------------------

    /**
     * @return array<string, mixed>
     */
    private function parseAndValidate(string $text): array
    {
        return $this->normalizeResult($this->decodeJson($text));
    }

    /**
     * Bersihkan pagar ```json lalu parse. Coba ambil objek JSON bila masih gagal.
     *
     * @return array<string, mixed>
     */
    private function decodeJson(string $text): array
    {
        $clean = trim($text);

        if (str_starts_with($clean, '```')) {
            $clean = (string) preg_replace('/^```(?:json)?\s*/i', '', $clean);
            $clean = (string) preg_replace('/\s*```$/', '', $clean);
            $clean = trim($clean);
        }

        $data = json_decode($clean, true);

        if (is_array($data)) {
            return $data;
        }

        $start = strpos($clean, '{');
        $end = strrpos($clean, '}');

        if ($start !== false && $end !== false && $end > $start) {
            $data = json_decode(substr($clean, $start, $end - $start + 1), true);

            if (is_array($data)) {
                return $data;
            }
        }

        throw new RuntimeException(
            'Respons AI bukan JSON valid sehingga tidak dapat diproses. Coba lagi.'
        );
    }

    /**
     * Pastikan semua key ada, tipe benar, dan nilai enum sesuai.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizeResult(array $data): array
    {
        $urgensi = strtolower(trim($this->stringOr($data['urgensi'] ?? null, '')));
        if (! in_array($urgensi, self::ALLOWED_URGENSI, true)) {
            $urgensi = 'sedang';
        }

        $status = strtolower(trim($this->stringOr($data['status_audio'] ?? null, '')));
        if (! in_array($status, self::ALLOWED_STATUS_AUDIO, true)) {
            $status = 'kurang_jelas';
        }

        $confidence = $this->clampInt($data['confidence'] ?? null, 0);

        if ($confidence < self::CONFIDENCE_PERLU_ULANG) {
            $status = 'perlu_ulang';
        }

        return [
            'transkrip' => $this->stringOr($data['transkrip'] ?? null, ''),
            'ringkasan' => $this->stringOr($data['ringkasan'] ?? null, ''),
            'penjelasan_ai' => $this->stringOr($data['penjelasan_ai'] ?? null, ''),
            'kategori' => $this->stringOr($data['kategori'] ?? null, 'lainnya'),
            'urgensi' => $urgensi,
            'confidence' => $confidence,
            'alasan_confidence' => $this->stringOr($data['alasan_confidence'] ?? null, ''),
            'bagian_tidak_jelas' => $this->stringList($data['bagian_tidak_jelas'] ?? null),
            'pertanyaan_klarifikasi' => $this->stringList($data['pertanyaan_klarifikasi'] ?? null),
            'status_audio' => $status,
        ];
    }

    /**
     * Penggabungan dasar tanpa AI: cukup menambahkan jawaban ke transkrip.
     *
     * @param  array<string, mixed>  $hasil
     * @return array<string, mixed>
     */
    private function mergeClarificationFallback(array $hasil, string $jawaban): array
    {
        $jawaban = trim($jawaban);

        if ($jawaban !== '') {
            $hasil['transkrip'] = trim($hasil['transkrip']."\n\n[Klarifikasi pengguna]: ".$jawaban);
            $hasil['penjelasan_ai'] = trim(
                $hasil['penjelasan_ai']."\nCatatan: pengguna menambahkan klarifikasi: ".$jawaban
            );
        }

        return $this->normalizeResult($hasil);
    }

    private function stringOr($value, string $default): string
    {
        if (is_string($value)) {
            return trim($value);
        }

        if (is_numeric($value) || is_bool($value)) {
            return trim((string) $value);
        }

        return $default;
    }

    private function clampInt($value, int $default): int
    {
        if (! is_numeric($value)) {
            return max(0, min(100, $default));
        }

        return max(0, min(100, (int) round((float) $value)));
    }

    /**
     * @return array<int, string>
     */
    private function stringList($value): array
    {
        if (is_string($value)) {
            $value = trim($value) === '' ? [] : [$value];
        }

        if (! is_array($value)) {
            return [];
        }

        $result = [];

        foreach ($value as $item) {
            if (is_string($item) && trim($item) !== '') {
                $result[] = trim($item);
            }
        }

        return array_values($result);
    }
}
