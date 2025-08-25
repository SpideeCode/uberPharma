<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\PharmacyController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\PharmacyDashboardController;
use App\Http\Controllers\PharmacyProductController;
use App\Models\Pharmacy;
use App\Models\Product;
use Inertia\Inertia;
use Illuminate\Support\Facades\Route;

// Middleware
use App\Http\Middleware\AdminPass;
use App\Http\Middleware\PharmacyPass;
use App\Http\Middleware\ClientPass;

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\UserController;
use App\Models\Order;
use App\Models\Review;
use App\Models\User;

// -----------------------------------------------------------------------------
// Admin Dashboard & Stats
// -----------------------------------------------------------------------------
Route::middleware(['auth', AdminPass::class])->group(function () {
    // Tableau de bord admin
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    
    // Statistiques d'une pharmacie spécifique
    Route::get('/admin/pharmacies/{pharmacy}/stats', [AdminDashboardController::class, 'getPharmacyStats'])
        ->name('admin.pharmacies.stats');

    // Gestion des utilisateurs
    Route::get('/admin/users', [UserController::class, 'index'])->name('admin.users');
    
    // Gestion des pharmacies
    Route::get('/admin/pharmacies', [PharmacyController::class, 'index'])->name('admin.pharmacies');
    
    // Gestion des produits
    Route::get('/admin/products', [ProductController::class, 'index'])->name('admin.products');
    
    // Gestion des commandes
    Route::get('/admin/orders', [OrderController::class, 'adminIndex'])->name('admin.orders');
});

// -----------------------------------------------------------------------------
// Homepage
// -----------------------------------------------------------------------------
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'pharmacies' => Pharmacy::all(),
        'products' => Product::all(),
        'auth' => auth()->check() ? [
            'id' => auth()->id(),
            'name' => auth()->user()->name,
            'email' => auth()->user()->email,
            'role' => auth()->user()->role,
        ] : null,
    ]);
})->name('home');

// -----------------------------------------------------------------------------
// Dashboard accessible à tous les utilisateurs authentifiés
// -----------------------------------------------------------------------------
Route::middleware(['auth', 'verified'])->get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->name('dashboard');

// -----------------------------------------------------------------------------
// Routes clients
// -----------------------------------------------------------------------------

Route::middleware(['auth', ClientPass::class])->group(function () {
    // Historique des commandes (ClientOrders.tsx)
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');

    // Gestion des commandes (PharmacyOrders.tsx)
    Route::get('/pharmacy/orders', [OrderController::class, 'index'])->name('pharmacy.orders');

    // Détail d'une commande (OrderDetail.tsx)
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');

    // Création d'une commande (si besoin)
    Route::get('/orders/create', [OrderController::class, 'create'])->name('orders.create');
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');

    // Paiement (Payment.tsx)
    Route::get('/payment', [PaymentController::class, 'index'])->name('payment.index');
    Route::post('/payment', [PaymentController::class, 'store'])->name('payment.store');

    // Confirmation commande (OrderConfirmation.tsx)
    Route::get('/order/confirmation/{order}', [PaymentController::class, 'confirmation'])
        ->name('order.confirmation');

    // Livraison
    Route::post('/deliveries/update-location', [DeliveryController::class, 'updateLocation'])->name('deliveries.update');

    // Avis
    Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');

    // Pharmacies (PharmacyDetail.tsx)
    Route::get('/pharmacies/{pharmacy}', [PharmacyController::class, 'show'])->name('pharmacies.show');

    // Panier (CartSidebar.tsx / Cart.tsx)
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
    Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');


    Route::post('/orders/{order}/send-to-pharmacy', [OrderController::class, 'sendToPharmacy'])
        ->name('orders.send');
});


// -----------------------------------------------------------------------------
// Routes pharmacies
// -----------------------------------------------------------------------------
Route::middleware(['auth', PharmacyPass::class])->group(function () {
    // Tableau de bord pharmacie
    Route::get('/pharmacy/dashboard', [PharmacyDashboardController::class, 'index'])->name('pharmacy.dashboard');
    // Gestion des pharmacies
    Route::get('/pharmacies', [PharmacyController::class, 'index'])->name('pharmacies.index');
    Route::get('/pharmacies/create', [PharmacyController::class, 'create'])->name('pharmacies.create');
    Route::post('/pharmacies', [PharmacyController::class, 'store'])->name('pharmacies.store');
    Route::get('/pharmacies/{pharmacy}/edit', [PharmacyController::class, 'edit'])->name('pharmacies.edit');
    Route::put('/pharmacies/{pharmacy}', [PharmacyController::class, 'update'])->name('pharmacies.update');
    Route::delete('/pharmacies/{pharmacy}', [PharmacyController::class, 'destroy'])->name('pharmacies.destroy');
    Route::get('/mes-pharmacies', [PharmacyController::class, 'myPharmacies'])->name('pharmacies.my');

    // Gestion produits
    Route::get('/pharmacies/{pharmacy}/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    
    // Mise à jour du statut d'une commande
    Route::patch('/api/orders/{order}/status', [OrderController::class, 'updateStatus'])
        ->name('api.orders.updateStatus');
});

// -----------------------------------------------------------------------------
// Routes admin
// -----------------------------------------------------------------------------
Route::middleware(['auth', AdminPass::class])->group(function () {
    Route::get('/users', function () {
        return Inertia::render('Users', [
            'users' => \App\Models\User::all(),
        ]);
    })->name('users.index');

    Route::post('/users', function (\Illuminate\Http\Request $request) {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|in:client,pharmacy,admin',
        ]);

        \App\Models\User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        return redirect()->back();
    });

    Route::post('/users/{user}/update-role', function (\Illuminate\Http\Request $request, \App\Models\User $user) {
        $request->validate([
            'role' => 'required|in:client,pharmacy,admin',
        ]);
        $user->role = $request->role;
        $user->save();
        return redirect()->back();
    });

    Route::delete('/users/{user}', function (\App\Models\User $user) {
        $user->delete();
        return redirect()->back();
    });
});

// -----------------------------------------------------------------------------
// Dashboard pharmacie
// -----------------------------------------------------------------------------
Route::middleware(['auth', PharmacyPass::class])
    ->prefix('pharmacy')
    ->name('pharmacy.')
    ->group(function () {
        Route::get('/dashboard', [PharmacyDashboardController::class, 'index'])->name('dashboard');

        Route::get('/products', [PharmacyProductController::class, 'index'])->name('products.index');
        Route::post('/products', [PharmacyProductController::class, 'store'])->name('products.store');
        Route::get('/products/{product}/edit', [PharmacyProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{product}', [PharmacyProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{product}', [PharmacyProductController::class, 'destroy'])->name('products.destroy');
    });

// -----------------------------------------------------------------------------
// Logout
// -----------------------------------------------------------------------------
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->name('logout')
    ->middleware('auth');

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
