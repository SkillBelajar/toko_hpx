<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PosController extends Controller
{
    public function index(Request $request): View
    {
        $tenant = $request->user()->tenant;

        return view('tenant.pos.index', [
            'shift' => $request->user()->activeShift(),
            'stockCount' => Inventory::where('tenant_id', $tenant?->id)->available()->count(),
        ]);
    }

    /**
     * Endpoint ringan untuk cek IMEI dari scanner (Slice 6 akan lanjut ke checkout).
     */
    public function lookup(Request $request)
    {
        $imei = preg_replace('/\D/', '', (string) $request->query('imei'));
        $tenant = $request->user()->tenant;

        $item = Inventory::where('tenant_id', $tenant?->id)
            ->with('product')
            ->where('imei', $imei)
            ->first();

        if (! $item) {
            return response()->json(['found' => false, 'imei' => $imei], 404);
        }

        return response()->json([
            'found' => true,
            'imei' => $item->imei,
            'tipe_hp' => $item->product?->tipe_hp,
            'brand' => $item->product?->brand,
            'price_sell' => (int) ($item->product?->price_sell ?? 0),
            'status' => $item->status,
            'age_days' => $item->ageInDays(),
        ]);
    }
}
