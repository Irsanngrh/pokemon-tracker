<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\CardController;
use App\Http\Controllers\CollectionItemController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\AdminController;
use App\Http\Middleware\AdminAuth;
use Inertia\Inertia;

Route::get('/', function () { return Inertia::render('Index'); });
Route::get('/my-collection', function () { return Inertia::render('MyCollection'); });

Route::post('/collection/init', [CollectionController::class, 'init']);
Route::post('/collection/verify', [CollectionController::class, 'verify']);

Route::get('/api/expansions', [CardController::class, 'getExpansions']);
Route::get('/api/cards', [CardController::class, 'getCards']);

Route::get('/api/collection/items', [CollectionItemController::class, 'getItems']);
Route::post('/api/collection/items', [CollectionItemController::class, 'updateItem']);
Route::get('/api/collection/summary', [CollectionItemController::class, 'getSummary']);

Route::get('/c/{slug}', [CollectionController::class, 'showPublic']);

Route::get('/api/export/excel', [ExportController::class, 'exportExcel']);
Route::get('/api/export/pdf', [ExportController::class, 'exportPdf']);

Route::middleware([AdminAuth::class])->group(function () {
    Route::get('/admin', [AdminController::class, 'index']);
    Route::post('/admin/cards/{id}', [AdminController::class, 'update']);
});