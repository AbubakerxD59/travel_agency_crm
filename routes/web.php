<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\InvoicePreviewController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

Route::get('/agent-notification-sw.js', function (): BinaryFileResponse {
    $path = public_path('agent-notification-sw.js');

    abort_unless(is_file($path), 404);

    $response = response()->file($path, [
        'Content-Type' => 'application/javascript; charset=UTF-8',
        'Cache-Control' => 'public, max-age=0, must-revalidate',
    ]);

    $response->headers->set('Service-Worker-Allowed', '/');

    return $response;
})->name('agent.notification.sw');

/*
| Fallback when public/storage symlink is missing (common on first deploy).
| If the symlink exists, the web server serves files directly and this route is not hit.
*/
Route::get('/storage/{path}', function (string $path) {
    $path = str_replace('\\', '/', $path);

    if ($path === '' || str_contains($path, '..')) {
        abort(404);
    }

    if (! Storage::disk('public')->exists($path)) {
        abort(404);
    }

    return Storage::disk('public')->response($path);
})->where('path', '.*');

Route::get('/test/invoice', [InvoicePreviewController::class, 'test'])
    ->name('test.invoice');

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route(auth()->user()->defaultRedirectRoute())
        : redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    require __DIR__.'/admin.php';
    require __DIR__.'/manager.php';
    require __DIR__.'/agent.php';
});
