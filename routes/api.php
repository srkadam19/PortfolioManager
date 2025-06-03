<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PortfolioController;

Route::get('/', [PortfolioController::class, 'store']);
Route::post('/portfolio', [PortfolioController::class, 'store']);
Route::get('/portfolio/{username}', [PortfolioController::class, 'show']);
Route::put('/portfolio/{username}', [PortfolioController::class, 'update']);
Route::delete('/portfolio/{username}', [PortfolioController::class, 'destroy']);