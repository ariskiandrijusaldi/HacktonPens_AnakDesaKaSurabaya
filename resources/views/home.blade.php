@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-800 mb-4">Sistem Pengaduan Masyarakat</h1>
        <p class="text-xl text-gray-600 max-w-2xl mx-auto">
            Laporkan masalah di lingkungan Anda dan ikuti perkembangan penyelesaian pengaduan Anda
        </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
        <div class="bg-white p-6 rounded-lg shadow text-center">
            <div class="text-4xl font-bold text-blue-600 mb-2">{{ $complaints->count() }}</div>
            <h3 class="text-lg font-semibold text-gray-800">Pengaduan Terbaru</h3>
        </div>
        
        <div class="bg-white p-6 rounded-lg shadow text-center">
            <div class="text-4xl font-bold text-green-600 mb-2">
                {{ Complaint::where('status', 'resolved')->count() }}
            </div>
            <h3 class="text-lg font-semibold text-gray-800">Pengaduan Selesai</h3>
        </div>
        
        <div class="bg-white p-6 rounded-lg shadow text-center">
            <div class="text-4xl font-bold text-yellow-600 mb-2">
                {{ Complaint::where('status', 'in_progress')->count() }}
            </div>
            <h3 class="text-lg font-semibold text-gray-800">Dalam Proses</h3>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg p-6 mb-8">
        <h2 class="text-2xl font-bold mb-6">Pengaduan Terbaru</h2>
        
        @if($complaints->count() > 0)
            <div class="space-y-4">
                @foreach($complaints as $complaint)
                    <div class="border-b pb-4 last:border-b-0">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="font-semibold text-lg">{{ $complaint->title }}</h3>
                                <p class="text-gray-600">{{ Str::limit($complaint->description, 100) }}</p>
                                <div class="flex items-center mt-2 space-x-2">
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded">
                                        {{ $complaint->category }}
                                    </span>
                                    <span class="px-2 py-1 bg-{{ $complaint->status === 'resolved' ? 'green' : ($complaint->status === 'in_progress' ? 'yellow' : 'gray') }}-100 text-{{ $complaint->status === 'resolved' ? 'green' : ($complaint->status === 'in_progress' ? 'yellow' : 'gray') }}-800 text-xs rounded">
                                        {{ ucfirst($complaint->status) }}
                                    </span>
                                </div>
                            </div>
                            <a href="{{ route('complaints.show', $complaint) }}" class="text-blue-500 hover:text-blue-700">
                                Lihat Detail
                            </a>
                        </div>
                        <div class="text-sm text-gray-500 mt-2">
                            {{ $complaint->created_at->diffForHumans() }} oleh {{ $complaint->user->name ?? 'Anonymous' }}
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-500 text-center py-4">Belum ada pengaduan yang tersedia</p>
        @endif
    </div>

    <div class="text-center">
        <a href="{{ route('complaints.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg text-lg">
            Buat Pengaduan Baru
        </a>
    </div>
</div>
@endsection