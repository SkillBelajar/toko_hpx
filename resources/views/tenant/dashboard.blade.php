@extends('tenant.layout')

@section('title', 'Dashboard Tenant')
@section('heading', 'Dashboard')

@section('content')
@php
    $tenant = auth()->user()->tenant;
    $available = \App\Models\Inventory::where('tenant_id', $tenant?->id)->available()->count();
    $aging = \App\Models\Inventory::where('tenant_id', $tenant?->id)
        ->available()
        ->whereDate('entry_date', '<', now()->subDays(\App\Models\Inventory::AGING_DAYS))
        ->count();
@endphp

<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    @foreach ([
        ['label' => 'Stok Tersedia', 'value' => $available],
        ['label' => 'Stok Mengendap', 'value' => $aging, 'alert' => $aging > 0],
        ['label' => 'IMEI Terpakai', 'value' => $tenant?->imeiUsed() ?? 0],
        ['label' => 'Sisa Kuota', 'value' => $tenant?->imeiLimit() === null ? '∞' : max(0, $tenant->imeiLimit() - $tenant->imeiUsed())],
    ] as $card)
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $card['label'] }}</p>
            <p class="mt-2 text-3xl font-extrabold tracking-tight {{ !empty($card['alert']) ? 'text-red-600' : 'text-slate-900' }}">{{ $card['value'] }}</p>
        </div>
    @endforeach
</div>

@if ($aging > 0)
    <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 p-5">
        <p class="text-sm font-bold text-red-700">Peringatan Stok Mengendap</p>
        <p class="mt-1 text-sm text-red-600">{{ $aging }} unit sudah lebih dari 30 hari tidak laku. Pertimbangkan turunkan harga atau bundling.</p>
        <a href="{{ route('tenant.inventory.index') }}" class="mt-3 inline-block rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">Lihat Inventori</a>
    </div>
@endif

<div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
    <h2 class="text-lg font-bold text-slate-900">Selamat datang di Dashboard Tenant</h2>
    <p class="mt-2 text-sm text-slate-600">
        Halo, {{ auth()->user()->name }} &middot; {{ $tenant?->nama_toko ?? '—' }}
    </p>
    <div class="mt-4 flex flex-wrap gap-2">
        <a href="{{ route('tenant.inventory.index') }}" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Kelola Inventori</a>
    </div>
</div>
@endsection
