<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;

class CampusCareController extends Controller
{
    public function home()
    {
        return view('layouts.home');
    }

    public function help()
    {
        $resources = [];

        try {
            $response = Http::acceptJson()
                ->timeout(10)
                ->get(config('services.campuscare.url').'/help-resources');

            if ($response->successful()) {
                $resources = $response->json('data')
                    ?? $response->json()
                    ?? [];
            }
        } catch (\Throwable $e) {
            report($e);
        }

        return view('bantuan', compact('resources'));
    }
}
