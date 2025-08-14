<?php

use App\Models\Pharmacy;
use App\Models\Product;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Page d'accueil : toutes les pharmacies et produits
Route::get('/', function () {
    return Inertia::render('welcome', [
        'pharmacies' => Pharmacy::all(),
        'products' => Product::all(),
        'auth' => auth()->check() ? [
            'id' => auth()->id(),
            'name' => auth()->user()->name,
            'email' => auth()->user()->email,
        ] : null,
    ]);
})->name('home');

// Dashboard (auth)
Route::middleware(['auth', 'verified'])->get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->name('dashboard');

// Produits
Route::get('/products', [\App\Http\Controllers\ProductController::class, 'index']);
Route::get('/products/create', [\App\Http\Controllers\ProductController::class, 'create']);
Route::post('/products', [\App\Http\Controllers\ProductController::class, 'store']);

// Pharmacies
Route::get('/pharmacies', [\App\Http\Controllers\PharmacyController::class, 'index']);
Route::get('/pharmacies/create', [\App\Http\Controllers\PharmacyController::class, 'create']);
Route::post('/pharmacies', [\App\Http\Controllers\PharmacyController::class, 'store']);

// Commandes, Paiements, Livraisons, Avis
Route::get('/orders', [\App\Http\Controllers\OrderController::class, 'index']);
Route::get('/orders/create', [\App\Http\Controllers\OrderController::class, 'create']);
Route::post('/orders', [\App\Http\Controllers\OrderController::class, 'store']);
Route::post('/payments', [\App\Http\Controllers\PaymentController::class, 'store']);
Route::post('/deliveries/update-location', [\App\Http\Controllers\DeliveryController::class, 'updateLocation']);
Route::post('/reviews', [\App\Http\Controllers\ReviewController::class, 'store']);

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
