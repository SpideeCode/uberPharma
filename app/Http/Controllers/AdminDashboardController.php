<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\DB;
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
        // Récupération des données de base
        $currentMonthStart = now()->startOfMonth();
        $lastMonthStart = now()->subMonth()->startOfMonth();
        $lastMonthEnd = now()->subMonth()->endOfMonth();

        // --- Stats principales
        $stats = [
            'users' => [
                'total' => User::count(),
                'clients' => User::where('role', 'client')->count(),
                'pharmacies' => User::where('role', 'pharmacy')->count(),
                'admins' => User::where('role', 'admin')->count(),
                'new_this_month' => User::where('created_at', '>=', $currentMonthStart)->count(),
                'growth' => $this->calculateGrowth(User::class),
            ],
            'pharmacies' => [
                'total' => Pharmacy::count(),
                'new_this_month' => Pharmacy::where('created_at', '>=', $currentMonthStart)->count(),
                'growth' => $this->calculateGrowth(Pharmacy::class),
            ],
            'products' => [
                'total' => Product::count(),
                'out_of_stock' => Product::where('stock', '<=', 0)->count(),
                'new_this_month' => Product::where('created_at', '>=', $currentMonthStart)->count(),
                'growth' => $this->calculateGrowth(Product::class),
            ],
            'orders' => [
                'total' => Order::count(),
                'pending' => Order::where('status', 'pending')->count(),
                'accepted' => Order::where('status', 'accepted')->count(),
                'in_delivery' => Order::where('status', 'in_delivery')->count(),
                'delivered' => Order::where('status', 'delivered')->count(),
                'cancelled' => Order::where('status', 'cancelled')->count(),
                'revenue' => Order::where('status', 'delivered')->sum('total_price'),
                'revenue_this_month' => Order::where('status', 'delivered')
                    ->where('created_at', '>=', $currentMonthStart)
                    ->sum('total_price'),
                'revenue_last_month' => Order::where('status', 'delivered')
                    ->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
                    ->sum('total_price'),
                'growth' => $this->calculateGrowth(Order::class),
            ],
        ];

        // --- Récupération des commandes récentes
        $recentOrders = $this->getRecentOrders(5);
        
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
        $topProducts = $this->getTopProducts(5);

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
     * Récupère les statistiques détaillées des commandes des 30 derniers jours
     */
    private function getOrderStats()
    {
        // Récupération des données des 30 derniers jours
        $endDate = now();
        $startDate = now()->subDays(29); // 30 jours au total
        
        // Création d'un tableau avec toutes les dates de la période
        $dates = collect();
        $currentDate = $startDate->copy();
        
        while ($currentDate <= $endDate) {
            $dates->push($currentDate->format('Y-m-d'));
            $currentDate->addDay();
        }
        
        // Récupération des commandes groupées par statut et par jour
        $ordersByStatus = Order::select(
                DB::raw('DATE(created_at) as date'),
                'status',
                DB::raw('count(*) as total')
            )
            ->where('created_at', '>=', $startDate)
            ->groupBy('date', 'status')
            ->orderBy('date')
            ->get()
            ->groupBy('status');
        
        // Initialisation des données pour chaque statut
        $statuses = [
            'pending' => ['label' => 'En attente', 'color' => '#F59E0B', 'data' => []],
            'accepted' => ['label' => 'Acceptées', 'color' => '#3B82F6', 'data' => []],
            'in_delivery' => ['label' => 'En livraison', 'color' => '#8B5CF6', 'data' => []],
            'delivered' => ['label' => 'Livrées', 'color' => '#10B981', 'data' => []],
            'cancelled' => ['label' => 'Annulées', 'color' => '#EF4444', 'data' => []],
        ];
        
        // Remplissage des données pour chaque jour et chaque statut
        foreach ($dates as $date) {
            foreach ($statuses as $status => &$statusData) {
                $statusOrders = $ordersByStatus->get($status, collect());
                $count = $statusOrders->firstWhere('date', $date)->total ?? 0;
                $statusData['data'][] = $count;
            }
        }
        
        // Calcul des totaux pour les indicateurs
        $previousPeriodStart = $startDate->copy()->subDays(30);
        $currentTotal = Order::where('created_at', '>=', $startDate)->count();
        $previousTotal = Order::whereBetween('created_at', [$previousPeriodStart, $startDate])->count();
        $percentageChange = $previousTotal > 0 
            ? round((($currentTotal - $previousTotal) / $previousTotal) * 100, 1)
            : 100;
        
        // Format des données pour Recharts
        $formattedData = [];
        
        // Pour chaque jour, on crée un objet avec les données de chaque statut
        foreach ($dates as $index => $date) {
            $dayData = [
                'date' => \Carbon\Carbon::parse($date)->format('d M'),
                'name' => \Carbon\Carbon::parse($date)->format('d M'),
            ];
            
            foreach ($statuses as $status => $statusData) {
                $dayData[$statusData['label']] = $statusData['data'][$index] ?? 0;
            }
            
            $formattedData[] = $dayData;
        }
        
        return $formattedData;
    }

    /**
     * Récupère les statistiques de revenus détaillées des 30 derniers jours
     */
    private function getRevenueStats()
    {
        // Période actuelle (30 derniers jours)
        $endDate = now();
        $startDate = now()->subDays(29);
        
        // Période précédente (30 jours avant la période actuelle)
        $previousPeriodStart = $startDate->copy()->subDays(30);
        
        // Récupération des revenus par jour pour la période actuelle
        $dailyRevenues = Order::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_price) as revenue')
            )
            ->where('status', 'delivered')
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');
        
        // Création d'un jeu de données complet pour tous les jours (même ceux sans commandes)
        $dates = collect();
        $currentDate = $startDate->copy();
        
        while ($currentDate <= $endDate) {
            $dateStr = $currentDate->format('Y-m-d');
            $revenue = $dailyRevenues->get($dateStr, (object)['revenue' => 0])->revenue;
            
            $dates->push([
                'date' => $dateStr,
                'revenue' => (float)$revenue,
                'formatted_date' => $currentDate->format('d M')
            ]);
            
            $currentDate->addDay();
        }
        
        // Calcul des totaux et des indicateurs
        $currentPeriodRevenue = Order::where('status', 'delivered')
            ->where('created_at', '>=', $startDate)
            ->sum('total_price');
            
        $previousPeriodRevenue = Order::where('status', 'delivered')
            ->whereBetween('created_at', [$previousPeriodStart, $startDate])
            ->sum('total_price');
            
        $revenueChange = $previousPeriodRevenue > 0 
            ? round((($currentPeriodRevenue - $previousPeriodRevenue) / $previousPeriodRevenue) * 100, 1)
            : 100;
        
        // Calcul des revenus par statut pour les indicateurs
        $revenueByStatus = [
            'delivered' => [
                'label' => 'Livrées',
                'value' => Order::where('status', 'delivered')
                    ->where('created_at', '>=', $startDate)
                    ->sum('total_price'),
                'color' => '#10B981'
            ],
            'in_delivery' => [
                'label' => 'En cours',
                'value' => Order::where('status', 'in_delivery')
                    ->where('created_at', '>=', $startDate)
                    ->sum('total_price'),
                'color' => '#8B5CF6'
            ],
            'pending' => [
                'label' => 'En attente',
                'value' => Order::where('status', 'pending')
                    ->where('created_at', '>=', $startDate)
                    ->sum('total_price'),
                'color' => '#F59E0B'
            ]
        ];
        
        // Format des données pour Recharts
        $formattedData = [];
        
        foreach ($dates as $day) {
            $formattedData[] = [
                'date' => $day['formatted_date'],
                'name' => $day['formatted_date'],
                'Revenu' => $day['revenue'],
            ];
        }
        
        return $formattedData;
    }

    /**
     * Récupère les statistiques d'une pharmacie spécifique
     */
    public function getPharmacyStats(Pharmacy $pharmacy)
    {
        $stats = [
            'total_orders' => $pharmacy->orders()->count(),
            'completed_orders' => $pharmacy->orders()->where('status', 'completed')->count(),
            'pending_orders' => $pharmacy->orders()->where('status', 'pending')->count(),
            'total_revenue' => $pharmacy->orders()->where('status', 'completed')->sum('total_price'),
        ];
        
        return $stats;
    }
    
    /**
     * Récupère les produits les plus vendus
     */
    private function getTopProducts($limit = 5)
    {
        return Product::select('products.*')
            ->withCount(['orderItems as sales_count' => function($query) {
                $query->select(DB::raw('COALESCE(SUM(quantity), 0)'));
            }])
            ->withSum(['orderItems as revenue' => function($query) {
                $query->select(DB::raw('COALESCE(SUM(quantity * price), 0)'));
            }], 'quantity * price')
            ->with(['pharmacy' => function($query) {
                $query->select('id', 'name');
            }])
            ->whereHas('orderItems')
            ->orderBy('sales_count', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'pharmacy_name' => $product->pharmacy ? $product->pharmacy->name : 'N/A',
                    'pharmacy_id' => $product->pharmacy_id,
                    'image' => $product->image_url,
                    'sales' => (int)$product->sales_count,
                    'revenue' => (float)$product->revenue,
                    'revenue_formatted' => number_format($product->revenue, 2, ',', ' ') . ' €',
                    'stock' => $product->stock,
                    'stock_status' => $product->stock > 10 ? 'En stock' : ($product->stock > 0 ? 'Stock faible' : 'Rupture'),
                    'stock_class' => $product->stock > 10 ? 'text-green-600' : ($product->stock > 0 ? 'text-yellow-600' : 'text-red-600'),
                ];
            });
    }
    
    /**
     * Récupère les commandes récentes
     */
    private function getRecentOrders($limit = 5)
    {
        return Order::with(['client', 'pharmacy', 'items.product'])
            ->latest()
            ->limit($limit)
            ->get()
            ->map(function ($order) {
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
                    'total' => (float)$order->total_price,
                    'total_formatted' => number_format($order->total_price, 2, ',', ' ') . ' €',
                    'status_value' => $order->status,
                    'status_label' => $statusConfig['label'],
                    'status_color' => $statusConfig['color'],
                    'items_count' => $order->items->count(),
                    'created_at' => [
                        'formatted' => $order->created_at->format('d/m/Y H:i'),
                        'diff' => $order->created_at->diffForHumans(),
                        'raw' => $order->created_at->toDateTimeString(),
                    ],
                    'delivery_address' => $order->delivery_address,
                ];
            });
    }
}
