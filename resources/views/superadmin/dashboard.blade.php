@extends('superadmin.layout')

@section('title', 'Dashboard Superadmin')
@section('heading', 'Dashboard')

@section('content')
@php
    $totalTenant = \App\Models\Tenant::count();
    $totalPaket = \App\Models\Package::count();
    $expired = \App\Models\Tenant::whereNotNull('expired_date')->whereDate('expired_date', '<', now())->count();
    $tanpaPaket = \App\Models\Tenant::whereNull('package_id')->count();
@endphp

<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    @foreach ([
        ['label' => 'Total Toko', 'value' => $totalTenant],
        ['label' => 'Paket Aktif', 'value' => $totalPaket],
        ['label' => 'Toko Expired', 'value' => $expired],
        ['label' => 'Toko Tanpa Paket', 'value' => $tanpaPaket],
    ] as $card)
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $card['label'] }}</p>
            <p class="mt-2 text-3xl font-extrabold tracking-tight text-slate-900">{{ $card['value'] }}</p>
        </div>
    @endforeach
</div>

<div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
    <h2 class="text-lg font-bold text-slate-900">Selamat datang di Dashboard Superadmin</h2>
    <p class="mt-2 text-sm text-slate-600">Halo, {{ auth()->user()->name }}. Kelola paket berlangganan dan data toko dari menu di samping.</p>
    <div class="mt-4 flex flex-wrap gap-2">
        <a href="{{ route('superadmin.packages.index') }}" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Kelola Paket</a>
        <a href="{{ route('superadmin.tenants.index') }}" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Kelola Toko</a>
    </div>
</div>
@endsection
