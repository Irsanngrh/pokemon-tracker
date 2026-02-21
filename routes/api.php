<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CardController;

Route::get('/expansions', [CardController::class, 'getExpansions']);
Route::get('/cards', [CardController::class, 'getCards']);