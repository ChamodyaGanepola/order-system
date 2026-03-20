<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;

Route::post('orders/{order}/update-status', [OrderController::class, 'updateStatus']);
Route::post('orders/{order}/transex-order', [OrderController::class, 'createTransexOrder']);
Route::post('orders/{order}/fde-order', [OrderController::class, 'createFDEOrder']);
Route::post('orders/bulk-details', [OrderController::class, 'bulkDetails']);
Route::post('orders/bulk-ship', [OrderController::class, 'bulkShip']);
Route::post('orders/bulk-ship-fde', [OrderController::class, 'bulkShipFDE']);
