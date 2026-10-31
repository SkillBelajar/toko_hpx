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

        return view('tenant.shift.close', [
            'shift' => $shift,
            'expectedAmount' => $this->expectedAmount($shift),
            'totalSales' => (int) $shift->transactions()->sum('total_price'),
            'trxCount' => $shift->transactions()->count(),
        ]);
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

        // expected = modal awal + semua transaksi selama shift; variance = fisik - expected.
        $expected = $this->expectedAmount($shift);

        $shift->update([
            'closing_amount' => $data['closing_amount'],
            'expected_amount' => $expected,
            'variance' => (float) $data['closing_amount'] - (float) $expected,
            'closed_at' => now(),
        ]);

        return redirect()->route('tenant.reports.shifts')
            ->with('status', 'Shift ditutup. Uang fisik laci Rp '.number_format($data['closing_amount'], 0, ',', '.')
                .' · seharusnya Rp '.number_format($expected, 0, ',', '.')
                .' · selisih Rp '.number_format((float) $data['closing_amount'] - (float) $expected, 0, ',', '.').'.');
    }

    /** expected_amount = opening_amount + total transaksi shift ini. */
    private function expectedAmount(CashShift $shift): float
    {
        return (float) $shift->opening_amount + (float) $shift->transactions()->sum('total_price');
    }
}
