<?php

namespace App\Http\Controllers;

use App\Models\Pharmacy;
use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PharmacyDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Récupérer les pharmacies de l'utilisateur avec les statistiques
        $pharmacies = Pharmacy::where('user_id', $user->id)
            ->withCount(['orders', 'products'])
            ->get()
            ->map(function ($pharmacy) {
                $pharmacy->revenue = $pharmacy->orders()->where('status', 'completed')->sum('total_price');
                $pharmacy->pending_orders = $pharmacy->orders()->where('status', 'pending')->count();
                return $pharmacy;
            });

        // Statistiques globales
        $stats = [
            'total_pharmacies' => $pharmacies->count(),
            'total_orders' => $pharmacies->sum('orders_count'),
            'total_products' => $pharmacies->sum('products_count'),
            'total_revenue' => $pharmacies->sum('revenue'),
            'pending_orders' => $pharmacies->sum('pending_orders'),
        ];

        // Commandes récentes (toutes pharmacies confondues)
        $recentOrders = Order::whereIn('pharmacy_id', $pharmacies->pluck('id'))
            ->with(['client', 'pharmacy'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'client' => $order->client ? $order->client->name : 'Client inconnu',
                    'pharmacy' => $order->pharmacy ? $order->pharmacy->name : 'Pharmacie inconnue',
                    'total' => $order->total_price,
                    'status' => $order->status,
                    'date' => $order->created_at->format('M d, Y H:i'),
                ];
            });

        // Produits les plus vendus
        $topProducts = Product::whereIn('pharmacy_id', $pharmacies->pluck('id'))
            ->withCount('orderItems')
            ->withSum('orderItems as total_sold', 'quantity')
            ->orderBy('total_sold', 'desc')
            ->take(5)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sales' => $product->total_sold,
                    'revenue' => $product->orderItems->sum(function ($item) {
                        return $item->price * $item->quantity;
                    }),
                ];
            });

        return Inertia::render('PharmacyDashboard', [
            'pharmacies' => $pharmacies,
            'stats' => $stats,
            'recentOrders' => $recentOrders,
            'topProducts' => $topProducts,
        ]);
    }
}
