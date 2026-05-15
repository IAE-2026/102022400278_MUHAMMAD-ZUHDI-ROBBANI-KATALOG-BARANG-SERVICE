<?php

use App\Http\Controllers\Admin\ItemController as AdminItemController;
use App\Http\Controllers\ItemController;
use Illuminate\Support\Facades\Route;

Route::get('/items', [ItemController::class, 'index']);
Route::get('/items/{item}', [ItemController::class, 'show'])->whereNumber('item');

Route::middleware('api.key:admin')->prefix('admin')->group(function (): void {
    Route::post('/items', [AdminItemController::class, 'store']);
    Route::put('/items/{item}', [AdminItemController::class, 'update'])->whereNumber('item');
    Route::delete('/items/{item}', [AdminItemController::class, 'destroy'])->whereNumber('item');
});
