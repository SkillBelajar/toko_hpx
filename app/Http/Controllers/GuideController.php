<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class GuideController extends Controller
{
    public function index(): View
    {
        $user = request()->user();

        return view('user-guide', [
            'isSuperadmin' => $user?->isSuperadmin() ?? false,
            'waAdmin' => config('services.whatsapp.admin'),
        ]);
    }
}
