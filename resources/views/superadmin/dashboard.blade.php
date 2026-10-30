@extends('layouts.dashboard')

@section('title', 'Dashboard Superadmin')
@section('badge', 'Superadmin')

@section('content')
<div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
    <h1 class="text-xl font-bold text-slate-900">Selamat datang di Dashboard Superadmin</h1>
    <p class="mt-2 text-sm text-slate-600">Halo, {{ auth()->user()->name }}. Kelola tenant, paket, dan langganan dari sini.</p>
</div>
@endsection
