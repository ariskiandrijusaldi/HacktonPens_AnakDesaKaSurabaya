<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\Response as ClientResponse;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class OfficerController extends Controller
{
    private function api(Request $request)
    {
        return Http::withToken(session('campuscare_token'))
            ->acceptJson()
            ->timeout(20);
    }

    private function base(): string
    {
        return rtrim(config('services.campuscare.url'), '/');
    }

    private function get(Request $request, string $path, array $query = [])
    {
        return $this->api($request)->get($this->base().$path, $query);
    }

    private function post(Request $request, string $path, array $data = [])
    {
        return $this->api($request)->post($this->base().$path, $data);
    }

    private function patch(Request $request, string $path, array $data = [])
    {
        return $this->api($request)->patch($this->base().$path, $data);
    }

    private function result(ClientResponse $response)
    {
        if ($response->status() === 401) {
            // Token backend kedaluwarsa/tidak valid: bersihkan sesi petugas
            // dan kembalikan ke halaman masuk agar tidak tampil error 502.
            session()->forget(['campuscare_token', 'campuscare_expires_at', 'campuscare_user']);

            throw new HttpResponseException(
                redirect()->route('masuk')->withErrors([
                    'login' => 'Sesi petugas berakhir. Silakan masuk kembali.',
                ])
            );
        }

        if (! $response->successful()) {
            abort($response->status() === 404 ? 404 : 502,
                'Permintaan ke backend gagal.');
        }

        return $response->json('data') ?? $response->json();
    }

    public function dashboard(Request $request)
    {
        return view('officer.landing');
    }

    public function index(Request $request)
    {
        $allowedStatus = ['MENUNGGU_TIM', 'SEDANG_DIDAMPINGI', 'SELESAI'];
        $allowedUrgency = ['MERAH', 'KUNING', 'HIJAU'];

        $filters = $request->only(['page', 'q', 'category']);

        if (in_array($request->query('status'), $allowedStatus, true)) {
            $filters['status'] = $request->query('status');
        }

        if (in_array($request->query('urgency'), $allowedUrgency, true)) {
            $filters['urgency'] = $request->query('urgency');
        }

        $reports = $this->result(
            $this->get($request, '/officer/reports', $filters)
        );

        // Statistik bersifat suplemen; bila gagal, daftar tetap tampil.
        try {
            $stats = $this->result($this->get($request, '/officer/stats'));
        } catch (\Throwable $e) {
            $stats = [];
        }

        return view('officer.laporan', compact('reports', 'stats'));
    }

    public function detail(Request $request, string $id)
    {
        $report = $this->result(
            $this->get($request, '/officer/reports/'.rawurlencode($id))
        );

        // Jangan mengambil kontak atau teks asli dalam request detail biasa.
        unset($report['contact'], $report['original_text']);

        return view('officer.detail', compact('report'));
    }

    public function updateStatus(Request $request, string $id)
    {
        $data = $request->validate([
            'status' => ['required', 'in:MENUNGGU_TIM,SEDANG_DIDAMPINGI,SELESAI'],
        ]);

        $this->result(
            $this->patch(
                $request,
                '/officer/reports/'.rawurlencode($id).'/status',
                $data
            )
        );

        return back()->with('success', 'Status laporan diperbarui.');
    }

    public function updateUrgency(Request $request, string $id)
    {
        $data = $request->validate([
            'urgency' => ['required', 'in:MERAH,KUNING,HIJAU'],
            'reason' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        $this->result(
            $this->patch(
                $request,
                '/officer/reports/'.rawurlencode($id).'/urgency',
                $data
            )
        );

        return back()->with('success', 'Urgensi diperbarui beserta alasannya.');
    }

    public function assign(Request $request, string $id)
    {
        $data = $request->validate([
            'assigned_to' => ['nullable', 'string', 'max:100'],
        ]);

        $officerId = null;

        if (! empty($data['assigned_to'])) {
            $officerId = $this->resolveOfficerId($request, $data['assigned_to']);

            if ($officerId === null) {
                return back()->withErrors([
                    'assigned_to' => 'Petugas dengan nama tersebut tidak ditemukan.',
                ]);
            }
        }

        $this->result(
            $this->patch(
                $request,
                '/officer/reports/'.rawurlencode($id).'/assign',
                ['officer_id' => $officerId]
            )
        );

        return back()->with('success', 'Petugas berhasil ditugaskan.');
    }

    /**
     * Ubah nama/username petugas menjadi officer_id yang dipakai backend.
     */
    private function resolveOfficerId(Request $request, string $value): ?string
    {
        $officers = $this->result($this->get($request, '/officer/officers'));
        $officers = is_array($officers) ? $officers : [];

        foreach ($officers as $officer) {
            if (! is_array($officer)) {
                continue;
            }

            if (($officer['id'] ?? null) === $value) {
                return $value;
            }

            if (strcasecmp((string) ($officer['username'] ?? ''), $value) === 0) {
                return $officer['id'];
            }
        }

        return null;
    }

    public function addNote(Request $request, string $id)
    {
        $data = $request->validate([
            'note' => ['required', 'string', 'max:5000'],
        ]);

        $this->result(
            $this->post(
                $request,
                '/officer/reports/'.rawurlencode($id).'/notes',
                $data
            )
        );

        return back()->with('success', 'Catatan internal disimpan.');
    }

    public function sendMessage(Request $request, string $id)
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $this->result(
            $this->post(
                $request,
                '/officer/reports/'.rawurlencode($id).'/messages',
                ['body' => $data['message']]
            )
        );

        return back()->with('success', 'Pesan berhasil dikirim.');
    }

    public function statistics(Request $request)
    {
        $stats = $this->result(
            $this->get($request, '/officer/stats')
        );

        return view('officer.statistik', compact('stats'));
    }

    public function patterns(Request $request)
    {
        $patterns = $this->result(
            $this->get($request, '/officer/patterns', $request->only(['page', 'q']))
        );

        return view('officer.pola', compact('patterns'));
    }

    public function audit(Request $request)
    {
        $filters = $request->only([
            'actor', 'officer', 'from', 'to', 'q', 'page',
        ]);

        $audit = $this->result(
            $this->get($request, '/officer/audit-logs', $filters)
        );

        return view('officer.audit', compact('audit', 'filters'));
    }

    public function overrideIdentity(Request $request, string $id)
    {
        abort_unless(
            strtoupper(session('campuscare_user.role', '')) === 'ADMIN'
                || strtoupper(data_get(session('campuscare_user', []), 'role', '')) === 'ADMIN',
            403,
            'Fitur ini hanya untuk ADMIN.'
        );

        $data = $request->validate([
            'pin' => ['required', 'string', 'max:100'],
            'pin_confirmation' => ['required', 'same:pin'],
            'reason' => ['required', 'string', 'min:20', 'max:2000'],
        ]);

        $response = $this->api($request)->post(
            $this->base().'/officer/reports/'.
            rawurlencode($id).'/override',
            [
                'pin' => $data['pin'],
                'confirm_pin' => $data['pin_confirmation'],
                'reason' => $data['reason'],
            ]
        );

        $identity = $this->result($response);

        // Hasil sensitif hanya untuk permintaan ini, tidak disimpan di session.
        return response()->json([
            'contact' => data_get($identity, 'contact'),
            'original_text' => data_get($identity, 'original_text'),
        ]);
    }

    public function attachment(Request $request, string $id, string $fileId)
    {
        $response = $this->api($request)->get(
            $this->base().'/officer/reports/'.
            rawurlencode($id).'/attachments/'.rawurlencode($fileId)
        );

        if (! $response->successful()) {
            abort($response->status() === 404 ? 404 : 502,
                'Lampiran tidak dapat diunduh.');
        }

        return response($response->body(), 200, [
            'Content-Type' => $response->header('Content-Type', 'application/octet-stream'),
            'Content-Disposition' => 'attachment; filename="lampiran"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'no-store, private',
        ]);
    }
}
