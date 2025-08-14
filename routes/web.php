<?php

use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PharmacyController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReviewController;
use App\Models\Pharmacy;
use App\Models\Product;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'pharmacies' => Pharmacy::all(),
        'products' => Product::all(),
    ]);
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');
});


// Produits
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/create', [ProductController::class, 'create']);
Route::post('/products', [ProductController::class, 'store']);

// Pharmacies
Route::get('/pharmacies', [PharmacyController::class, 'index']);
Route::get('/pharmacies/create', [PharmacyController::class, 'create']);
Route::post('/pharmacies', [PharmacyController::class, 'store']);

// Commandes
Route::get('/orders', [OrderController::class, 'index']);
Route::get('/orders/create', [OrderController::class, 'create']);
Route::post('/orders', [OrderController::class, 'store']);

// Paiements
Route::post('/payments', [PaymentController::class, 'store']);

// Livraisons
Route::post('/deliveries/update-location', [DeliveryController::class, 'updateLocation']);

// Avis
Route::post('/reviews', [ReviewController::class, 'store']);

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
