<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class ReportController extends Controller
{
    public function create()
    {
        return view('lapor');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'description' => ['required', 'string', 'min:20', 'max:20000'],
            'victim_wish' => ['required', 'string', 'in:KONSULTASI,PENDAMPINGAN,PROSES_FORMAL,BELUM_TAHU'],
            'location' => ['nullable', 'string', 'max:500'],
            'contact' => ['nullable', 'string', 'max:500'],
            'contact_consent' => ['nullable', 'boolean'],
            'consent' => ['required', 'accepted'],
            'attachments' => ['nullable', 'array', 'max:3'],
            'attachments.*' => [
                'file',
                'max:5120',
                'mimes:jpg,jpeg,png,pdf',
            ],
        ]);

        if (
            $request->filled('contact') &&
            ! $request->boolean('contact_consent')
        ) {
            throw ValidationException::withMessages([
                'contact_consent' => 'Setujui penggunaan kontak terlebih dahulu atau kosongkan kontak.',
            ]);
        }

        $api = rtrim(config('services.campuscare.url'), '/');

        try {
            $http = Http::acceptJson()->timeout(30)->asForm();

            foreach ($request->file('attachments', []) as $file) {
                $http->attach(
                    'files',
                    file_get_contents($file->getRealPath()),
                    $file->getClientOriginalName()
                );
            }

            $response = $http->post($api.'/reports', [
                'text' => $validated['description'],
                'victim_wish' => $validated['victim_wish'],
                'location' => $validated['location'] ?? null,
                'contact' => $request->boolean('contact_consent')
                    ? ($validated['contact'] ?? null)
                    : null,
                'consent' => true,
            ]);

            if (! $response->successful()) {
                return back()
                    ->withInput($request->except(['contact']))
                    ->withErrors([
                        'api' => 'Laporan belum dapat dikirim. Silakan coba kembali.',
                    ]);
            }

            $data = $response->json('data') ?? $response->json();

            if (empty($data['ticket_code'])) {
                return back()->withErrors([
                    'api' => 'Laporan diterima, tetapi kode tiket tidak tersedia. Hubungi pengelola sistem.',
                ]);
            }

            return redirect()
                ->route('lapor')
                ->with('ticket_code', $data['ticket_code'])
                ->with('report_success', true);

        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withInput($request->except(['contact']))
                ->withErrors([
                    'api' => 'Backend belum dapat dihubungi. Coba lagi nanti.',
                ]);
        }
    }
}
