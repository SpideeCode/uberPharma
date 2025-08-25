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

        // Statistiques globales
        $stats = [
            'total_pharmacies' => $pharmacies->count(),
            'total_orders' => array_sum($ordersByStatus),
            'total_products' => $pharmacies->sum('products_count'),
            'total_revenue' => $revenueByStatus['delivered'] ?? 0,
            'pending_orders' => $ordersByStatus['pending'] ?? 0,
            'in_delivery_orders' => $ordersByStatus['in_delivery'] ?? 0,
            'delivered_orders' => $ordersByStatus['delivered'] ?? 0,
            'cancelled_orders' => $ordersByStatus['cancelled'] ?? 0,
            'revenue_by_status' => $revenueByStatus,
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

        // Produits les plus vendus (uniquement les commandes complétées)
        $topProducts = Product::whereIn('pharmacy_id', $pharmacies->pluck('id'))
            ->whereHas('orderItems.order', function($query) {
                $query->where('status', 'completed');
            })
            ->withCount(['orderItems as completed_order_items_count' => function($query) {
                $query->whereHas('order', function($q) {
                    $q->where('status', 'completed');
                });
            }])
            ->withSum(['orderItems as total_sold' => function($query) {
                $query->select(DB::raw('SUM(quantity)'))
                    ->whereHas('order', function($q) {
                        $q->where('status', 'completed');
                    });
            }], 'quantity')
            ->orderBy('completed_order_items_count', 'desc')
            ->take(5)
            ->get()
            ->map(function ($product) {
                $revenue = $product->orderItems
                    ->filter(fn($item) => $item->order->status === 'completed')
                    ->sum(fn($item) => $item->price * $item->quantity);

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sales' => $product->total_sold ?? 0,
                    'revenue' => $revenue,
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
