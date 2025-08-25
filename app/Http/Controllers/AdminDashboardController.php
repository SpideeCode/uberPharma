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

        // --- Commandes récentes avec statuts détaillés
        $recentOrders = Order::with(['client', 'pharmacy', 'courier'])
            ->withCount('items')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($order) {
                // Définition des statuts avec couleurs et libellés
                $statusConfig = [
                    'pending' => ['label' => 'En attente', 'color' => 'bg-yellow-100 text-yellow-800'],
                    'accepted' => ['label' => 'Acceptée', 'color' => 'bg-blue-100 text-blue-800'],
                    'in_delivery' => ['label' => 'En livraison', 'color' => 'bg-indigo-100 text-indigo-800'],
                    'delivered' => ['label' => 'Livrée', 'color' => 'bg-green-100 text-green-800'],
                    'cancelled' => ['label' => 'Annulée', 'color' => 'bg-red-100 text-red-800'],
                ][$order->status] ?? ['label' => ucfirst($order->status), 'color' => 'bg-gray-100 text-gray-800'];

                return [
                    'id' => $order->id,
                    'user' => [
                        'name' => $order->client ? $order->client->name : 'Utilisateur inconnu',
                        'email' => $order->client ? $order->client->email : null,
                    ],
                    'pharmacy' => $order->pharmacy ? [
                        'name' => $order->pharmacy->name,
                        'id' => $order->pharmacy->id,
                    ] : null,
                    'courier' => $order->courier ? [
                        'name' => $order->courier->name,
                        'id' => $order->courier->id,
                    ] : null,
                    'total' => number_format($order->total_price, 2, ',', ' ') . ' €',
                    'status' => [
                        'value' => $order->status,
                        'label' => $statusConfig['label'],
                        'color' => $statusConfig['color'],
                    ],
                    'items_count' => $order->items_count,
                    'created_at' => [
                        'formatted' => $order->created_at->format('d/m/Y H:i'),
                        'diff' => $order->created_at->diffForHumans(),
                        'raw' => $order->created_at,
                    ],
                    'delivery_address' => $order->delivery_address,
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
        
        return [
            'labels' => $dates->map(fn($date) => \Carbon\Carbon::parse($date)->format('d M')),
            'datasets' => collect($statuses)->map(function($data, $status) {
                return [
                    'label' => $data['label'],
                    'data' => $data['data'],
                    'borderColor' => $data['color'],
                    'backgroundColor' => $data['color'].'20', // 20% d'opacité
                    'borderWidth' => 2,
                    'tension' => 0.3,
                    'fill' => true,
                ];
            })->values()->toArray(),
            'summary' => [
                'total' => $currentTotal,
                'change' => [
                    'value' => abs($currentTotal - $previousTotal),
                    'percentage' => abs($percentageChange),
                    'trend' => $currentTotal >= $previousTotal ? 'up' : 'down',
                ],
            ],
        ];
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
        
        return [
            'daily' => $dates,
            'summary' => [
                'current_period' => $currentPeriodRevenue,
                'previous_period' => $previousPeriodRevenue,
                'change' => [
                    'value' => abs($currentPeriodRevenue - $previousPeriodRevenue),
                    'percentage' => abs($revenueChange),
                    'trend' => $currentPeriodRevenue >= $previousPeriodRevenue ? 'up' : 'down',
                ],
            ],
            'by_status' => array_values($revenueByStatus)
        ];
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
