@extends('tenant.layout')

@section('title', 'Inventori')
@section('heading', 'Inventori')

@section('content')

{{-- Kuota IMEI --}}
<div class="mb-4 grid gap-4 sm:grid-cols-3">
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">IMEI Terpakai</p>
        <p class="mt-2 text-2xl font-extrabold tracking-tight text-slate-900">
            {{ $imeiUsed }}
            <span class="text-sm font-semibold text-slate-400">/ {{ $imeiLimit ?? '∞' }}</span>
        </p>
        @if ($imeiLimit)
            @php $pct = min(100, (int) round($imeiUsed / max(1, $imeiLimit) * 100)); @endphp
            <div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-slate-100">
                <div class="h-full rounded-full {{ $pct >= 100 ? 'bg-red-500' : ($pct >= 80 ? 'bg-amber-500' : 'bg-indigo-600') }}" style="width: {{ $pct }}%"></div>
            </div>
        @else
            <p class="mt-3 text-xs font-semibold text-emerald-600">Paket unlimited</p>
        @endif
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Stok Mengendap</p>
        <p class="mt-2 text-2xl font-extrabold tracking-tight {{ $agingCount > 0 ? 'text-red-600' : 'text-slate-900' }}">{{ $agingCount }}</p>
        <p class="mt-3 text-xs text-slate-500">Available lebih dari 30 hari</p>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Paket Aktif</p>
        <p class="mt-2 text-2xl font-extrabold tracking-tight text-slate-900">{{ $tenant?->package?->name ?? '—' }}</p>
        <p class="mt-3 text-xs text-slate-500">
            @if ($tenant?->expired_date)
                Aktif s/d {{ $tenant->expired_date->format('d M Y') }}
            @else
                Tanpa masa aktif
            @endif
        </p>
    </div>
</div>

{{-- Form tambah stok --}}
@php $errors = $errors ?? new Illuminate\Support\ViewErrorBag; @endphp
<div class="mb-4 rounded-2xl border border-slate-200 bg-white shadow-sm" x-data="{ open: {{ $errors->any() ? 'true' : 'false' }} }">
    <button type="button" @click="open = !open"
        class="flex w-full items-center justify-between gap-3 px-5 py-4 text-left">
        <span class="text-sm font-bold text-slate-900">+ Tambah Stok / IMEI</span>
        <span class="text-xs font-semibold text-indigo-600" x-text="open ? 'Tutup' : 'Buka'">Buka</span>
    </button>

    <div x-cloak x-show="open" class="border-t border-slate-100 p-5">
        @if ($imeiLimitReached ?? false)
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">
                Limit Total Input IMEI Anda sudah habis. Silakan Upgrade Paket.
            </div>
        @endif

        <form method="POST" action="{{ route('tenant.inventory.store') }}" class="space-y-4">
            @csrf

            <div class="grid gap-4 sm:grid-cols-3">
                <div class="sm:col-span-2">
                    <label for="tipe_hp" class="block text-sm font-medium text-slate-700">Tipe HP</label>
                    <input id="tipe_hp" name="tipe_hp" type="text" required value="{{ old('tipe_hp') }}"
                        class="mt-1 block w-full rounded-xl border border-slate-300 px-4 py-2.5 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
                        placeholder="Mis. Samsung Galaxy A15 8/256">
                    @error('tipe_hp') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="brand" class="block text-sm font-medium text-slate-700">Brand</label>
                    <input id="brand" name="brand" type="text" value="{{ old('brand') }}"
                        class="mt-1 block w-full rounded-xl border border-slate-300 px-4 py-2.5 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
                        placeholder="Samsung">
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="price_sell" class="block text-sm font-medium text-slate-700">Harga Jual (Rp)</label>
                    <input id="price_sell" name="price_sell" type="number" min="0" step="1000" required value="{{ old('price_sell') }}"
                        class="mt-1 block w-full rounded-xl border border-slate-300 px-4 py-2.5 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
                        placeholder="2500000">
                    @error('price_sell') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="entry_date" class="block text-sm font-medium text-slate-700">Tanggal Masuk</label>
                    <input id="entry_date" name="entry_date" type="date" value="{{ old('entry_date', now()->toDateString()) }}"
                        class="mt-1 block w-full rounded-xl border border-slate-300 px-4 py-2.5 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                </div>
            </div>

            <div>
                <label for="imei" class="block text-sm font-medium text-slate-700">
                    Nomor IMEI
                    <span class="font-normal text-slate-400">&mdash; satu per baris, atau pisahkan dengan koma/spasi</span>
                </label>
                <textarea id="imei" name="imei" rows="4" required
                    class="mt-1 block w-full rounded-xl border border-slate-300 px-4 py-2.5 font-mono text-sm tracking-wide focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
                    placeholder="356789012345671&#10;356789012345672">{{ old('imei') }}</textarea>
                <p class="mt-1 text-xs text-slate-500">Mendukung scan barcode: hasil scan langsung terisi seperti diketik.</p>
                @error('imei') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <button type="submit"
                class="w-full rounded-xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white shadow-sm hover:bg-indigo-700 sm:w-auto">
                Simpan Stok
            </button>
        </form>
    </div>
</div>

{{-- Pencarian --}}
<form method="GET" action="{{ route('tenant.inventory.index') }}" class="mb-4 flex gap-2">
    <input name="q" type="search" value="{{ $search }}"
        class="block w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
        placeholder="Cari tipe HP atau nomor IMEI...">
    <button type="submit" class="shrink-0 rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">Cari</button>
    @if ($search !== '')
        <a href="{{ route('tenant.inventory.index') }}" class="shrink-0 rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Reset</a>
    @endif
    <a href="{{ route('tenant.export.inventory', request()->only('q')) }}"
        class="shrink-0 rounded-xl border border-emerald-300 bg-emerald-50 px-4 py-2.5 text-sm font-semibold text-emerald-700 hover:bg-emerald-100">Export .xls</a>
</form>

{{-- Tabel stok --}}
<div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[48rem] text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="whitespace-nowrap px-4 py-3 font-semibold">Tipe HP</th>
                    <th class="whitespace-nowrap px-4 py-3 font-semibold">IMEI</th>
                    <th class="whitespace-nowrap px-4 py-3 font-semibold">Harga</th>
                    <th class="whitespace-nowrap px-4 py-3 font-semibold">Tanggal Masuk</th>
                    <th class="whitespace-nowrap px-4 py-3 font-semibold">Umur Stok</th>
                    <th class="whitespace-nowrap px-4 py-3 font-semibold">Status</th>
                    <th class="whitespace-nowrap px-4 py-3 font-semibold text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($inventories as $item)
                    @php $isAging = $item->isAging(); @endphp
                    <tr class="{{ $isAging ? 'bg-red-100' : 'hover:bg-slate-50/70' }}">
                        <td class="whitespace-nowrap px-4 py-3">
                            <span class="font-semibold {{ $isAging ? 'text-red-700' : 'text-slate-900' }}">{{ $item->product?->tipe_hp ?? '—' }}</span>
                            @if ($item->product?->brand)
                                <span class="block text-xs text-slate-500">{{ $item->product->brand }}</span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 font-mono text-xs {{ $isAging ? 'text-red-700' : 'text-slate-700' }}">{{ $item->imei }}</td>
                        <td class="whitespace-nowrap px-4 py-3 {{ $isAging ? 'text-red-700' : 'text-slate-700' }}">
                            Rp {{ number_format($item->product?->price_sell ?? 0, 0, ',', '.') }}
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 {{ $isAging ? 'text-red-700' : 'text-slate-600' }}">
                            {{ $item->entry_date->format('d M Y') }}
                        </td>
                        <td class="whitespace-nowrap px-4 py-3">
                            <span class="font-semibold {{ $isAging ? 'text-red-700' : 'text-slate-700' }}">{{ $item->ageInDays() }} hari</span>
                            @if ($isAging)
                                <span class="ml-1 rounded-full bg-red-600 px-2 py-0.5 text-[11px] font-bold text-white">Mengendap</span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-4 py-3">
                            @if ($item->status === 'available')
                                <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700">Available</span>
                            @else
                                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-500">Sold</span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-center">
                            <a href="{{ route('tenant.inventory.edit', $item) }}"
                                class="inline-flex items-center gap-1 rounded-lg border border-indigo-200 bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700 hover:bg-indigo-100">
                                Edit
                            </a>
                            @if ($item->status === 'available')
                            {{-- Konfirmasi 2 tahap: cegah salah klik (standar #9 Safety) --}}
                            <form method="POST" action="{{ route('tenant.inventory.destroy', $item) }}" class="inline js-confirm-delete"
                                data-imei="{{ $item->imei }}" data-tipe="{{ $item->product?->tipe_hp }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="inline-flex items-center gap-1 rounded-lg border border-red-200 bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-600 hover:bg-red-100">
                                    Hapus
                                </button>
                            </form>
                            @else
                                <span class="text-[11px] text-slate-400" title="Unit terjual punya riwayat transaksi, tidak bisa dihapus.">terkunci</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center text-sm text-slate-500">
                            {{ $search !== '' ? 'Tidak ada stok yang cocok dengan pencarian.' : 'Belum ada stok. Tambahkan IMEI pertama Anda.' }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">{{ $inventories->links() }}</div>

{{-- Konfirmasi 2 tahap untuk hapus stok (standar #9: salah klik tidak merusak data) --}}
<script>
document.querySelectorAll('.js-confirm-delete').forEach(function (form) {
    form.addEventListener('submit', function (e) {
        if (form.dataset.confirmed === '1') return;
        e.preventDefault();
        var imei = form.dataset.imei, tipe = form.dataset.tipe || '';
        if (!confirm('Hapus ' + tipe + ' (IMEI ' + imei + ') dari stok?')) return;
        if (!confirm('Konfirmasi kedua: tindakan ini tidak bisa dibatalkan. Tetap hapus?')) return;
        form.dataset.confirmed = '1';
        form.submit();
    });
});
</script>

@endsection
