<?php
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use App\Helpers\TransexHelper;
Route::get('/run-migrations', function() {
    Artisan::call('migrate', ['--force' => true]);
    return 'Migrations ran!';
});
Route::resource('products', ProductController::class)->except(['show']);

Route::get('/test-token', function() {
    try {
        $token = \App\Helpers\TransexHelper::getToken();
        dd($token); // should show your token string
    } catch (\Exception $e) {
        dd($e->getMessage());
    }
});
// Customers
Route::get('/customers', [CustomerController::class, 'index']);
Route::get('/customers/create', [CustomerController::class, 'create']);
Route::post('/customers', [CustomerController::class, 'store']);
Route::post('/customers/import', [CustomerController::class, 'import'])->name('customers.import');
Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');
Route::delete('/customers-delete-all', [CustomerController::class, 'destroyAll'])
    ->name('customers.destroyAll');
Route::delete('/customers/imports/delete-date', [CustomerController::class, 'deleteImportsByDate'])
    ->name('customers.imports.deleteByDate');

// Orders
Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
// Step 1: Select customer
Route::get('/orders/create', [OrderController::class, 'selectCustomer'])->name('orders.selectCustomer');
// Step 2: Show create order page for a customer
Route::get('/orders/create/{customer}', [OrderController::class, 'create'])->name('orders.create');
Route::post('/orders', [OrderController::class, 'store']);
Route::get('/orders/pending', [OrderController::class, 'pending']);
Route::get('/orders/completed', [OrderController::class, 'completed']);
Route::get('/orders/shipping', [OrderController::class, 'shipping']);
Route::get('/orders/rejected', [OrderController::class, 'rejected']);
Route::get('/orders/outofstock', [OrderController::class,'outOfStock']);
Route::post('/orders/{order}/update-status', [OrderController::class, 'updateStatus'])
    ->name('orders.updateStatus');
// Edit & Update
Route::get('/orders/{order}/edit', [OrderController::class, 'edit'])->name('orders.edit');
Route::put('/orders/{order}', [OrderController::class, 'update'])->name('orders.update');

// Optional: show route if needed
// Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');


Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
