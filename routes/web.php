<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\PharmacyController;
use App\Http\Controllers\ProductController;
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

Route::get('/pharmacies/{pharmacy}/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
Route::post('/products', [ProductController::class, 'store'])->name('products.store');
Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

// Pharmacies

Route::get('/pharmacies', [PharmacyController::class, 'index'])->name('pharmacies.index');
Route::get('/pharmacies/create', [PharmacyController::class, 'create'])->name('pharmacies.create');
Route::post('/pharmacies', [PharmacyController::class, 'store'])->name('pharmacies.store');
Route::get('/pharmacies/{pharmacy}', [PharmacyController::class, 'show'])->name('pharmacies.show');
Route::get('/pharmacies/{pharmacy}/edit', [PharmacyController::class, 'edit'])->name('pharmacies.edit');
Route::put('/pharmacies/{pharmacy}', [PharmacyController::class, 'update'])->name('pharmacies.update');
Route::delete('/pharmacies/{pharmacy}', [PharmacyController::class, 'destroy'])->name('pharmacies.destroy');

// Optionnel : voir uniquement les pharmacies de l’utilisateur connecté
Route::get('/mes-pharmacies', [PharmacyController::class, 'myPharmacies'])->name('pharmacies.my');


// Commandes, Paiements, Livraisons, Avis
Route::get('/orders', [\App\Http\Controllers\OrderController::class, 'index']);
Route::get('/orders/create', [\App\Http\Controllers\OrderController::class, 'create']);
Route::post('/orders', [\App\Http\Controllers\OrderController::class, 'store']);
Route::post('/payments', [\App\Http\Controllers\PaymentController::class, 'store']);
Route::post('/deliveries/update-location', [\App\Http\Controllers\DeliveryController::class, 'updateLocation']);
Route::post('/reviews', [\App\Http\Controllers\ReviewController::class, 'store']);




Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->name('logout')
    ->middleware('auth');

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
