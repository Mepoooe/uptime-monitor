<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DomainController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('dashboard'));

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::resource('domains', DomainController::class);
    Route::post('domains/{domain}/check-now', [DomainController::class, 'checkNow'])
        ->name('domains.check-now');
    Route::get('domains/{domain}/logs', [DomainController::class, 'logs'])
        ->name('domains.logs');
});

$authRoutes = __DIR__ . '/auth.php';

if (file_exists($authRoutes)) {
    require $authRoutes;
}
