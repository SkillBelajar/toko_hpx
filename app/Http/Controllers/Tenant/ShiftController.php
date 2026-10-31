<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\CashShift;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShiftController extends Controller
{
    public function open(Request $request): View|RedirectResponse
    {
        if ($request->user()->activeShift()) {
            return redirect()->route('tenant.pos.index');
        }

        return view('tenant.shift.open', [
            'lastShift' => $request->user()->cashShifts()->latest('opened_at')->first(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'opening_amount' => ['required', 'integer', 'min:0'],
        ], [
            'opening_amount.required' => 'Modal laci awal wajib diisi.',
        ]);

        if ($request->user()->activeShift()) {
            return redirect()->route('tenant.pos.index');
        }

        $tenant = $request->user()->tenant;
        abort_unless($tenant, 403, 'Akun Anda belum terhubung ke toko.');

        CashShift::create([
            'tenant_id' => $tenant->id,
            'user_id' => $request->user()->id,
            'opening_amount' => $data['opening_amount'],
            'opened_at' => now(),
        ]);

        return redirect()->route('tenant.pos.index')
            ->with('status', 'Shift dibuka. Modal laci awal Rp '.number_format($data['opening_amount'], 0, ',', '.').'.');
    }

    public function close(Request $request): View|RedirectResponse
    {
        $shift = $request->user()->activeShift();

        if (! $shift) {
            return redirect()->route('tenant.shift.open');
        }

        return view('tenant.shift.close', ['shift' => $shift]);
    }

    public function update(Request $request): RedirectResponse
    {
        $shift = $request->user()->activeShift();

        if (! $shift) {
            return redirect()->route('tenant.shift.open');
        }

        $data = $request->validate([
            'closing_amount' => ['required', 'integer', 'min:0'],
        ], [
            'closing_amount.required' => 'Uang fisik laci akhir wajib diisi.',
        ]);

        // expected_amount & variance disempurnakan di Slice 6/7 setelah transaksi berjalan.
        $shift->update([
            'closing_amount' => $data['closing_amount'],
            'expected_amount' => $shift->opening_amount,
            'variance' => (float) $data['closing_amount'] - (float) $shift->opening_amount,
            'closed_at' => now(),
        ]);

        return redirect()->route('tenant.shift.open')
            ->with('status', 'Shift ditutup. Uang fisik laci Rp '.number_format($data['closing_amount'], 0, ',', '.').'.');
    }
}
