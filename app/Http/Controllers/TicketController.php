<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TicketController extends Controller
{
    public function index()
    {
        return view('ticket');
    }

    public function lookup(Request $request)
    {
        $validated = $request->validate([
            'ticket_code' => [
                'required',
                'regex:/^PPKS-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$/',
            ],
        ]);

        try {
            $response = Http::acceptJson()
                ->timeout(15)
                ->get(
                    rtrim(config('services.campuscare.url'), '/').
                    '/tickets/'.rawurlencode($validated['ticket_code'])
                );

            if ($response->status() === 404) {
                return back()->withErrors([
                    'ticket_code' => 'Kode tiket tidak ditemukan.',
                ]);
            }

            if (! $response->successful()) {
                return back()->withErrors([
                    'api' => 'Status tiket belum dapat diperiksa.',
                ]);
            }

            $ticket = $response->json('data') ?? $response->json();

            // Jangan meneruskan teks asli laporan ke halaman pelapor.
            unset($ticket['description'], $ticket['original_text']);

            return view('ticket', compact('ticket'));

        } catch (\Throwable $e) {
            report($e);

            return back()->withErrors([
                'api' => 'Backend tidak dapat dihubungi.',
            ]);
        }
    }

    public function reply(Request $request)
    {
        $validated = $request->validate([
            'ticket_code' => [
                'required',
                'regex:/^PPKS-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$/',
            ],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        try {
            $response = Http::acceptJson()
                ->timeout(15)
                ->post(
                    rtrim(config('services.campuscare.url'), '/').
                    '/tickets/'.rawurlencode($validated['ticket_code']).'/messages',
                    ['message' => $validated['message']]
                );

            if (! $response->successful()) {
                return back()->withErrors([
                    'reply' => 'Balasan belum berhasil dikirim.',
                ]);
            }

            return redirect()
                ->route('tiket')
                ->with('ticket_code', $validated['ticket_code'])
                ->with('success', 'Balasan berhasil dikirim.');

        } catch (\Throwable $e) {
            report($e);

            return back()->withErrors([
                'reply' => 'Backend tidak dapat dihubungi.',
            ]);
        }
    }
}
