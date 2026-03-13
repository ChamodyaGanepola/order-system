<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;

Route::post('/orders/{order}/update-status', [OrderController::class, 'updateStatus']);
