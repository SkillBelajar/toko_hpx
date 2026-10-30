<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantController extends Controller
{
    public function index(): View
    {
        return view('superadmin.tenants.index', [
            'tenants' => Tenant::with(['package', 'users'])
                ->withCount('users')
                ->latest()
                ->get(),
        ]);
    }

    public function edit(Tenant $tenant): View
    {
        return view('superadmin.tenants.edit', [
            'tenant' => $tenant,
            'packages' => Package::orderBy('price')->get(),
        ]);
    }

    public function update(Request $request, Tenant $tenant): RedirectResponse
    {
        $data = $request->validate([
            'package_id' => ['nullable', 'exists:packages,id'],
            'expired_date' => ['nullable', 'date'],
        ]);

        $tenant->update($data);

        return redirect()->route('superadmin.tenants.index')->with('status', 'Data toko diperbarui.');
    }
}
