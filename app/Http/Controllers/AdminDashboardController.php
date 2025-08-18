<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Pharmacy;
use App\Models\Product;
use App\Models\Order;
use App\Models\Review;
use Inertia\Inertia;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // --- Stats
        $stats = [
            'users' => [
                'total' => User::count(),
                'clients' => User::where('role', 'client')->count(),
                'pharmacies' => User::where('role', 'pharmacy')->count(),
                'admins' => User::where('role', 'admin')->count(),
            ],
            'pharmacies' => Pharmacy::count(),
            'products' => Product::count(),
            'orders' => Order::count(),
            'revenues' => Order::sum('total_price'),
            'reviews' => Review::count(),
        ];

        // --- Data CRUD
        $users = User::select('id','name','email','role','created_at')->get();
        $pharmacies = Pharmacy::withCount('products')->get();
        $products = Product::with('pharmacy:id,name')->get();

        return Inertia::render('AdminDashboard', [
            'stats' => $stats,
            'users' => $users,
            'pharmacies' => $pharmacies,
            'products' => $products,
        ]);
    }
}
