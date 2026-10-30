<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PackageController extends Controller
{
    public function index(): View
    {
        return view('superadmin.packages.index', [
            'packages' => Package::withCount('tenants')->orderByDesc('is_default')->orderBy('price')->get(),
        ]);
    }

    public function create(): View
    {
        return view('superadmin.packages.form', ['package' => new Package(['price' => 0])]);
    }

    public function store(Request $request): RedirectResponse
    {
        Package::create($this->validated($request));

        return redirect()->route('superadmin.packages.index')->with('status', 'Paket berhasil dibuat.');
    }

    public function edit(Package $package): View
    {
        return view('superadmin.packages.form', ['package' => $package]);
    }

    public function update(Request $request, Package $package): RedirectResponse
    {
        $package->update($this->validated($request));

        return redirect()->route('superadmin.packages.index')->with('status', 'Paket berhasil diperbarui.');
    }

    public function destroy(Package $package): RedirectResponse
    {
        if ($package->tenants()->exists()) {
            return back()->with('error', 'Paket masih dipakai tenant. Pindahkan tenant dulu sebelum menghapus.');
        }

        $package->delete();

        return redirect()->route('superadmin.packages.index')->with('status', 'Paket dihapus.');
    }

    /**
     * Limit kosong / 0 dianggap unlimited (null) kecuali flag is_unlimited tidak dicentang.
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'price' => ['required', 'integer', 'min:0'],
            'total_transaction_limit' => ['nullable', 'integer', 'min:1'],
            'total_imei_limit' => ['nullable', 'integer', 'min:1'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $data['is_default'] = $request->boolean('is_default');

        // Hanya satu paket default: matikan flag default pada paket lain.
        if ($data['is_default']) {
            DB::transaction(function () use (&$data) {
                Package::where('is_default', true)->update(['is_default' => false]);
            });
        }

        return $data;
    }
}
