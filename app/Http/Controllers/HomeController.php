<?php

namespace App\Http\Controllers;

use App\Models\Package;
use Illuminate\Http\Response;

class HomeController extends Controller
{
    /**
     * Landing page: kirim semua paket (public, tanpa info sensitif).
     */
    public function __invoke(): Response
    {
        $packages = Package::orderBy('price')
            ->orderBy('name')
            ->get()
            ->map(fn (Package $p) => [
                'name'        => $p->name,
                'price'       => $p->price,
                'trx_limit'   => $p->total_transaction_limit,
                'imei_limit'   => $p->total_imei_limit,
                'is_default'  => (bool) $p->is_default,
            ]);

        return response()->view('welcome', ['landingPackages' => $packages]);
    }
}