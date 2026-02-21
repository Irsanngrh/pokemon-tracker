<?php
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\AdminController;

Route::get('/', function () {
    return Inertia::render('Index');
});

Route::get('/admin/cards', [AdminController::class, 'index']);
Route::post('/admin/cards/{id}', [AdminController::class, 'update']);