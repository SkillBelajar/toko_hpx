<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function edit(Request $request): View
    {
        $tenant = $request->user()->tenant;
        $user = $request->user();

        return view('tenant.settings', [
            'tenant' => $tenant,
            'user' => $user,
        ]);
    }

    public function updateStore(Request $request): RedirectResponse
    {
        $tenant = $request->user()->tenant;
        abort_unless($tenant, 403, 'Akun Anda belum terhubung ke toko.');

        $validated = $request->validate([
            'nama_toko' => ['required', 'string', 'max:100'],
            'no_telp'   => ['nullable', 'string', 'max:32'],
            'alamat'    => ['nullable', 'string', 'max:255'],
        ], [
            'nama_toko.required' => 'Nama toko wajib diisi.',
            'nama_toko.max'      => 'Nama toko maksimal 100 karakter.',
            'no_telp.max'        => 'Nomor telepon/WhatsApp maksimal 32 karakter.',
            'alamat.max'         => 'Alamat toko maksimal 255 karakter.',
        ]);

        $tenant->update($validated);

        return back()->with('status_store', 'Informasi toko berhasil disimpan.');
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users')->ignore($user->id)],
        ], [
            'name.required'  => 'Nama pengguna wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email'    => 'Format email tidak valid.',
            'email.unique'   => 'Alamat email ini sudah digunakan oleh akun lain.',
        ]);

        $user->update($validated);

        return back()->with('status_profile', 'Profil pengguna berhasil diperbarui.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'confirmed', Password::min(6)],
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'current_password.current_password' => 'Password saat ini tidak sesuai.',
            'password.required' => 'Password baru wajib diisi.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
            'password.min' => 'Password baru minimal 6 karakter.',
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('status_password', 'Password berhasil diubah.');
    }
}
