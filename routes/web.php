<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LandingController;


Route::get('/', [LandingController::class, 'index'])->name('landing');

// Redirect jika user mencoba akses /login secara manual (opsional)
Route::get('/login', function () {
    return redirect()->route('filament.app.auth.login');
})->name('login');

// Route::get('/', function () {
//     return redirect()->route('filament.admin.auth.login');
// });

