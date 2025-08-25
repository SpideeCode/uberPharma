<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Pharmacy;
use App\Models\Product;
use App\Models\Order;
use App\Models\Review;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // --- Stats principales
        $stats = [
            'users' => [
                'total' => User::count(),
                'clients' => User::where('role', 'client')->count(),
                'pharmacies' => User::where('role', 'pharmacy')->count(),
                'admins' => User::where('role', 'admin')->count(),
                'new_this_month' => User::where('created_at', '>=', now()->startOfMonth())->count(),
                'growth' => $this->calculateGrowth(User::class),
            ],
            'pharmacies' => [
                'total' => Pharmacy::count(),
                'new_this_month' => Pharmacy::where('created_at', '>=', now()->startOfMonth())->count(),
                'growth' => $this->calculateGrowth(Pharmacy::class),
            ],
            'products' => [
                'total' => Product::count(),
                'out_of_stock' => Product::where('stock', '<=', 0)->count(),
                'new_this_month' => Product::where('created_at', '>=', now()->startOfMonth())->count(),
                'growth' => $this->calculateGrowth(Product::class),
            ],
            'orders' => [
                'total' => Order::count(),
                'pending' => Order::where('status', 'pending')->count(),
                'completed' => Order::where('status', 'completed')->count(),
                'cancelled' => Order::where('status', 'cancelled')->count(),
                'revenue' => Order::where('status', 'completed')->sum('total_price'),
                'revenue_this_month' => Order::where('status', 'completed')
                    ->where('created_at', '>=', now()->startOfMonth())
                    ->sum('total_price'),
                'growth' => $this->calculateGrowth(Order::class),
            ],
        ];

        // --- Commandes récentes
        $recentOrders = Order::with(['client', 'pharmacy'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'user' => $order->client ? $order->client->name : 'Utilisateur inconnu',
                    'pharmacy' => $order->pharmacy ? $order->pharmacy->name : 'Pharmacie inconnue',
                    'total' => $order->total_price,
                    'status' => $order->status,
                    'date' => $order->created_at->format('M d, Y H:i'),
                ];
            });

        // --- Meilleures pharmacies
        $topPharmacies = Pharmacy::withCount('orders')
            ->withSum('orders', 'total_price')
            ->orderBy('orders_count', 'desc')
            ->take(5)
            ->get()
            ->map(function ($pharmacy) {
                return [
                    'id' => $pharmacy->id,
                    'name' => $pharmacy->name,
                    'orders_count' => $pharmacy->orders_count,
                    'total_revenue' => $pharmacy->orders_sum_total_price,
                ];
            });

        // --- Graphique des commandes
        $orderStats = $this->getOrderStats();
        
        // --- Graphique des revenus
        $revenueStats = $this->getRevenueStats();

        // --- Produits les plus vendus
        $topProducts = $this->getTopProducts();

        return Inertia::render('AdminDashboard', [
            'stats' => $stats,
            'recentOrders' => $recentOrders,
            'topPharmacies' => $topPharmacies,
            'orderStats' => $orderStats,
            'revenueStats' => $revenueStats,
            'topProducts' => $topProducts,
        ]);
    }

    /**
     * Calcule la croissance mensuelle pour un modèle donné
     */
    private function calculateGrowth($model, $field = 'created_at')
    {
        $currentMonthCount = $model::where($field, '>=', now()->startOfMonth())->count();
        $lastMonthCount = $model::whereBetween($field, [
            now()->subMonth()->startOfMonth(),
            now()->subMonth()->endOfMonth()
        ])->count();

        if ($lastMonthCount === 0) {
            return $currentMonthCount > 0 ? 100 : 0;
        }

        return round((($currentMonthCount - $lastMonthCount) / $lastMonthCount) * 100, 2);
    }

    /**
     * Récupère les statistiques des commandes pour les 6 derniers mois
     */
    private function getOrderStats()
    {
        $months = collect();
        $now = now();

        for ($i = 5; $i >= 0; $i--) {
            $date = $now->copy()->subMonths($i);
            $months->push([
                'month' => $date->format('M Y'),
                'orders' => Order::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
            ]);
        }

        return $months;
    }

    /**
     * Récupère les statistiques de revenus pour les 6 derniers mois
     */
    private function getRevenueStats()
    {
        $months = collect();
        $now = now();

        for ($i = 5; $i >= 0; $i--) {
            $date = $now->copy()->subMonths($i);
            $months->push([
                'month' => $date->format('M Y'),
                'revenue' => Order::where('status', 'completed')
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->sum('total_price'),
            ]);
        }

        return $months;
    }

    /**
     * Récupère les produits les plus vendus
     */
    private function getTopProducts($limit = 5)
    {
        return Product::with('pharmacy')
            ->withCount('orderItems')
            ->orderBy('order_items_count', 'desc')
            ->take($limit)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'pharmacy' => $product->pharmacy->name,
                    'sales' => $product->order_items_count,
                    'revenue' => $product->orderItems()->sum('price') * $product->orderItems()->sum('quantity'),
                ];
            });
    }

    /**
     * Récupère les statistiques pour une pharmacie spécifique
     */
    public function getPharmacyStats(Pharmacy $pharmacy)
    {
        $stats = [
            'total_orders' => $pharmacy->orders()->count(),
            'completed_orders' => $pharmacy->orders()->where('status', 'completed')->count(),
            'pending_orders' => $pharmacy->orders()->where('status', 'pending')->count(),
            'total_revenue' => $pharmacy->orders()->where('status', 'completed')->sum('total_price'),
            'products_count' => $pharmacy->products()->count(),
            'out_of_stock' => $pharmacy->products()->where('stock', '<=', 0)->count(),
        ];

        // Graphique des commandes des 6 derniers mois
        $orderStats = collect();
        $now = now();

        for ($i = 5; $i >= 0; $i--) {
            $date = $now->copy()->subMonths($i);
            $orderStats->push([
                'month' => $date->format('M Y'),
                'orders' => $pharmacy->orders()
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
            ]);
        }

        // Produits les plus vendus
        $topProducts = $pharmacy->products()
            ->withCount('orderItems')
            ->orderBy('order_items_count', 'desc')
            ->take(5)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sales' => $product->order_items_count,
                    'revenue' => $product->orderItems()->sum('price') * $product->orderItems()->sum('quantity'),
                ];
            });

        return response()->json([
            'stats' => $stats,
            'orderStats' => $orderStats,
            'topProducts' => $topProducts,
        ]);
    }
}
