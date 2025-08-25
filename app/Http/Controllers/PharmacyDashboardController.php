<?php

namespace App\Http\Controllers;

use App\Models\Pharmacy;
use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
                // Statistiques par statut de commande
                $statusCounts = $pharmacy->orders()
                    ->select('status', DB::raw('count(*) as count'))
                    ->groupBy('status')
                    ->pluck('count', 'status')
                    ->toArray();
                
                // Revenus par statut
                $revenueByStatus = $pharmacy->orders()
                    ->select('status', DB::raw('sum(total_price) as revenue'))
                    ->groupBy('status')
                    ->pluck('revenue', 'status')
                    ->toArray();
                
                $pharmacy->status_counts = $statusCounts;
                $pharmacy->revenue = $revenueByStatus['delivered'] ?? 0;
                $pharmacy->pending_orders = $statusCounts['pending'] ?? 0;
                $pharmacy->in_delivery_orders = $statusCounts['in_delivery'] ?? 0;
                $pharmacy->delivered_orders = $statusCounts['delivered'] ?? 0;
                $pharmacy->cancelled_orders = $statusCounts['cancelled'] ?? 0;
                
                return $pharmacy;
            });

        // Calcul des statistiques globales
        $pharmacyIds = $pharmacies->pluck('id');
        
        // Récupération des totaux par statut
        $ordersByStatus = Order::whereIn('pharmacy_id', $pharmacyIds)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
            
        // Revenus par statut
        $revenueByStatus = Order::whereIn('pharmacy_id', $pharmacyIds)
            ->select('status', DB::raw('sum(total_price) as revenue'))
            ->groupBy('status')
            ->pluck('revenue', 'status')
            ->toArray();

        // Statistiques sur 30 jours
        $thirtyDaysAgo = Carbon::now()->subDays(30);
        
        // Évolution des commandes sur 30 jours
        $ordersTrend = Order::whereIn('pharmacy_id', $pharmacyIds)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('count(*) as count'),
                DB::raw('sum(total_price) as revenue')
            )
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->groupBy('date')
            ->orderBy('date')
            ->get();
            
        // Préparer les données pour le graphique d'évolution
        $salesTrend = [];
        $revenueTrend = [];
        
        foreach ($ordersTrend as $day) {
            $date = Carbon::parse($day->date)->format('d M');
            $salesTrend[] = [
                'date' => $date,
                'commandes' => $day->count
            ];
            $revenueTrend[] = [
                'date' => $date,
                'revenu' => (float)$day->revenue
            ];
        }
        
        // Produits les plus vendus
        $topProducts = Product::whereIn('id', function($query) use ($pharmacyIds) {
                $query->select('product_id')
                    ->from('order_items')
                    ->join('orders', 'order_items.order_id', '=', 'orders.id')
                    ->whereIn('orders.pharmacy_id', $pharmacyIds);
            })
            ->withCount(['orderItems as total_quantity' => function($query) use ($pharmacyIds) {
                $query->select(DB::raw('sum(quantity)'))
                    ->join('orders', 'order_items.order_id', '=', 'orders.id')
                    ->whereIn('orders.pharmacy_id', $pharmacyIds);
            }])
            ->withSum(['orderItems' => function($query) use ($pharmacyIds) {
                $query->select(DB::raw('sum(quantity * order_items.price)'))
                    ->join('orders', 'order_items.order_id', '=', 'orders.id')
                    ->whereIn('orders.pharmacy_id', $pharmacyIds);
            }], 'quantity')
            ->orderBy('total_quantity', 'desc')
            ->take(5)
            ->get()
            ->map(function($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'quantity' => $product->total_quantity,
                    'revenue' => $product->total_revenue ?? 0,
                    'image' => $product->image_url
                ];
            });

        // Calcul des statistiques du mois précédent
        $previousMonthStart = Carbon::now()->subMonth()->startOfMonth();
        $previousMonthEnd = Carbon::now()->subMonth()->endOfMonth();
        
        // Commandes du mois précédent
        $previousMonthOrders = Order::whereIn('pharmacy_id', $pharmacyIds)
            ->whereBetween('created_at', [$previousMonthStart, $previousMonthEnd])
            ->count();
            
        // Revenus du mois précédent (uniquement les commandes livrées)
        $previousMonthRevenue = Order::whereIn('pharmacy_id', $pharmacyIds)
            ->where('status', 'delivered')
            ->whereBetween('created_at', [$previousMonthStart, $previousMonthEnd])
            ->sum('total_price');
            
        // Produits en stock le mois dernier (approximation basée sur la variation)
        $currentMonthProducts = $pharmacies->sum('products_count');
        $previousMonthProducts = $currentMonthProducts * 0.9; // Estimation à 90% du mois actuel
        
        // Commandes en attente le mois dernier
        $previousMonthPending = Order::whereIn('pharmacy_id', $pharmacyIds)
            ->where('status', 'pending')
            ->whereBetween('created_at', [$previousMonthStart, $previousMonthEnd])
            ->count();
        
        // Calcul des évolutions en pourcentage
        $totalOrders = array_sum($ordersByStatus);
        $totalRevenue = $revenueByStatus['delivered'] ?? 0;
        $pendingOrders = $ordersByStatus['pending'] ?? 0;
        
        $ordersChange = $previousMonthOrders > 0 
            ? round((($totalOrders - $previousMonthOrders) / $previousMonthOrders) * 100) 
            : 0;
            
        $revenueChange = $previousMonthRevenue > 0 
            ? round((($totalRevenue - $previousMonthRevenue) / $previousMonthRevenue) * 100) 
            : 0;
            
        $productsChange = $previousMonthProducts > 0 
            ? round((($currentMonthProducts - $previousMonthProducts) / $previousMonthProducts) * 100) 
            : 0;
            
        $pendingChange = $previousMonthPending > 0 
            ? round((($pendingOrders - $previousMonthPending) / $previousMonthPending) * 100) 
            : 0;

        // Statistiques globales
        $stats = [
            'total_pharmacies' => $pharmacies->count(),
            'total_orders' => $totalOrders,
            'total_products' => $currentMonthProducts,
            'total_revenue' => $totalRevenue,
            'pending_orders' => $pendingOrders,
            'in_delivery_orders' => $ordersByStatus['in_delivery'] ?? 0,
            'delivered_orders' => $ordersByStatus['delivered'] ?? 0,
            'cancelled_orders' => $ordersByStatus['cancelled'] ?? 0,
            'revenue_by_status' => $revenueByStatus,
            'sales_trend' => $salesTrend,
            'revenue_trend' => $revenueTrend,
            'status_distribution' => [
                ['name' => 'En attente', 'value' => $ordersByStatus['pending'] ?? 0, 'color' => '#F59E0B'],
                ['name' => 'En livraison', 'value' => $ordersByStatus['in_delivery'] ?? 0, 'color' => '#8B5CF6'],
                ['name' => 'Livrées', 'value' => $ordersByStatus['delivered'] ?? 0, 'color' => '#10B981'],
                ['name' => 'Annulées', 'value' => $ordersByStatus['cancelled'] ?? 0, 'color' => '#EF4444'],
            ]
        ];

        // Commandes récentes (toutes pharmacies confondues)
        $recentOrders = Order::whereIn('pharmacy_id', $pharmacyIds)
            ->with(['client', 'pharmacy', 'items.product'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($order) {
                // Configuration des statuts avec couleurs
                $statusConfig = [
                    'pending' => ['label' => 'En attente', 'color' => 'bg-yellow-100 text-yellow-800'],
                    'accepted' => ['label' => 'Acceptée', 'color' => 'bg-blue-100 text-blue-800'],
                    'in_delivery' => ['label' => 'En livraison', 'color' => 'bg-indigo-100 text-indigo-800'],
                    'delivered' => ['label' => 'Livrée', 'color' => 'bg-green-100 text-green-800'],
                    'cancelled' => ['label' => 'Annulée', 'color' => 'bg-red-100 text-red-800'],
                ][$order->status] ?? ['label' => ucfirst($order->status), 'color' => 'bg-gray-100 text-gray-800'];

                return [
                    'id' => $order->id,
                    'client_name' => $order->client ? $order->client->name : 'Client inconnu',
                    'client_email' => $order->client ? $order->client->email : null,
                    'pharmacy_name' => $order->pharmacy ? $order->pharmacy->name : 'Pharmacie inconnue',
                    'pharmacy_id' => $order->pharmacy ? $order->pharmacy->id : null,
                    'total' => $order->total_price,
                    'total_formatted' => number_format($order->total_price, 2, ',', ' ') . ' €',
                    'status_value' => $order->status,
                    'status_label' => $statusConfig['label'],
                    'status_color' => $statusConfig['color'],
                    'items_count' => $order->items->count(),
                    'created_at_formatted' => $order->created_at->format('d/m/Y H:i'),
                    'created_at_diff' => $order->created_at->diffForHumans(),
                    'created_at_raw' => $order->created_at->toDateTimeString(),
                    'delivery_address' => $order->delivery_address,
                ];
            });

        // Produits les plus vendus (uniquement les commandes livrées)
        $topProducts = Product::whereIn('pharmacy_id', $pharmacyIds)
            ->whereHas('orderItems.order', function($query) {
                $query->where('status', 'delivered');
            })
            ->with(['pharmacy:id,name'])
            ->select([
                'products.*',
                DB::raw('(SELECT SUM(oi.quantity) FROM order_items oi 
                         JOIN orders o ON oi.order_id = o.id 
                         WHERE oi.product_id = products.id AND o.status = "delivered") as total_sold'),
                DB::raw('(SELECT SUM(oi.quantity * oi.price) FROM order_items oi 
                         JOIN orders o ON oi.order_id = o.id 
                         WHERE oi.product_id = products.id AND o.status = "delivered") as total_revenue'),
                DB::raw('(SELECT COUNT(DISTINCT oi.order_id) FROM order_items oi 
                         JOIN orders o ON oi.order_id = o.id 
                         WHERE oi.product_id = products.id AND o.status = "delivered") as order_count'),
                DB::raw('(SELECT AVG(oi.price) FROM order_items oi 
                         JOIN orders o ON oi.order_id = o.id 
                         WHERE oi.product_id = products.id AND o.status = "delivered") as avg_price')
            ])
            ->orderBy('total_sold', 'desc')
            ->take(10)
            ->get()
            ->map(function ($product) {
                // Calcul du taux de croissance
                $growthRate = 0;
                $previousPeriodSold = DB::table('order_items')
                    ->join('orders', 'order_items.order_id', '=', 'orders.id')
                    ->where('order_items.product_id', $product->id)
                    ->where('orders.status', 'delivered')
                    ->where('orders.created_at', '<', now()->subDays(30))
                    ->sum('order_items.quantity');
                
                $currentPeriodSold = (int)$product->total_sold;
                
                if ($previousPeriodSold > 0) {
                    $growthRate = (($currentPeriodSold - $previousPeriodSold) / $previousPeriodSold) * 100;
                } elseif ($currentPeriodSold > 0) {
                    $growthRate = 100; // Nouveau produit avec ventes
                }

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'pharmacy' => $product->pharmacy->name ?? 'N/A',
                    'quantity' => $currentPeriodSold,
                    'revenue' => (float)$product->total_revenue,
                    'avg_price' => (float)$product->avg_price,
                    'orders_count' => (int)$product->order_count,
                    'growth_rate' => round($growthRate, 1),
                    'image' => $product->image ? asset('storage/' . $product->image) : '/images/placeholder-product.png',
                    'formatted_revenue' => number_format((float)$product->total_revenue, 2, ',', ' ') . ' €',
                    'formatted_avg_price' => number_format((float)$product->avg_price, 2, ',', ' ') . ' €',
                    'growth_icon' => $growthRate >= 0 ? '▲' : '▼',
                    'growth_class' => $growthRate >= 0 ? 'text-green-600' : 'text-red-600'
                ];
            });

        return Inertia::render('PharmacyDashboard', [
            'pharmacies' => $pharmacies,
            'stats' => $stats,
            'recentOrders' => $recentOrders,
            'topProducts' => $topProducts,
            'salesTrend' => $salesTrend,
            'revenueTrend' => $revenueTrend,
            'statusDistribution' => $stats['status_distribution']
        ]);
    }
}
