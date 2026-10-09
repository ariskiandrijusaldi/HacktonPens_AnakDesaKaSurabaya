<?php

namespace App\Http\Controllers;

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

    private function result(Request $response)
    {
        if (! $response->successful()) {
            abort($response->status() === 404 ? 404 : 502,
                'Permintaan ke backend gagal.');
        }

        return $response->json('data') ?? $response->json();
    }

    public function dashboard(Request $request)
    {
        $filters = $request->only([
            'status', 'urgency', 'category', 'location',
            'assigned_to', 'q', 'page',
        ]);

        $reports = $this->result(
            $this->get($request, '/officer/reports', $filters)
        );

        return view('officer.dasbor', compact('reports', 'filters'));
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
            'status' => ['required', 'string', 'max:50'],
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
            'assigned_to' => ['required', 'string', 'max:100'],
        ]);

        $this->result(
            $this->patch(
                $request,
                '/officer/reports/'.rawurlencode($id).'/assign',
                $data
            )
        );

        return back()->with('success', 'Petugas berhasil ditugaskan.');
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
                $data
            )
        );

        return back()->with('success', 'Pesan berhasil dikirim.');
    }

    public function statistics(Request $request)
    {
        $stats = $this->result(
            $this->get($request, '/officer/statistics')
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
            $this->get($request, '/officer/audit', $filters)
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
            rawurlencode($id).'/override-identity',
            [
                'pin' => $data['pin'],
                'pin_confirmation' => $data['pin_confirmation'],
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
