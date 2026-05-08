<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        $domains = $user->domains()->orderByDesc('created_at')->get();

        $stats = [
            'total'   => $domains->count(),
            'up'      => $domains->where('is_up', true)->count(),
            'down'    => $domains->where('is_up', false)->count(),
            'unknown' => $domains->whereNull('is_up')->count(),
        ];

        return view('dashboard', compact('domains', 'stats'));
    }
}
