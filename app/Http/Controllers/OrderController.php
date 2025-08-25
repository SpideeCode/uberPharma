<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * Liste les commandes selon le rôle de l'utilisateur
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'client') {
            // Commandes du client
            $orders = Order::with('items.product', 'pharmacy')
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();

            $component = 'ClientOrders';
        } elseif ($user->role === 'pharmacy') {
            // Commandes pour les pharmacies de l'utilisateur
            $pharmacyIds = $user->pharmacies()->pluck('id');
            $orders = Order::with('items.product', 'client')
                ->whereIn('pharmacy_id', $pharmacyIds)
                ->orderBy('created_at', 'desc')
                ->get();

            $component = 'PharmacyOrders';
        } else {
            $orders = collect(); // vide pour les autres rôles
            $component = 'ClientOrders'; // par défaut
        }

        return Inertia::render($component, [
            'orders' => $orders,
        ]);
    }

    /**
     * Détail d'une commande
     */
    /**
     * Affiche la liste des commandes pour l'administration
     */
    public function adminShow(Order $order)
    {
        $order->load([
            'items.product',
            'client',
            'pharmacy',
            'courier',
            'delivery',
            'reviews'
        ]);

        // Formatage des données pour la vue
        $orderData = [
            'id' => $order->id,
            'reference' => $order->reference,
            'status' => $order->status,
            'status_label' => $order->status_label,
            'status_color' => $order->status_color,
            'total_price' => $order->total_price,
            'total_price_formatted' => number_format($order->total_price, 2, ',', ' ') . ' €',
            'created_at' => [
                'formatted' => $order->created_at->format('d/m/Y H:i'),
                'diff' => $order->created_at->diffForHumans(),
                'raw' => $order->created_at->toDateTimeString(),
            ],
            'updated_at' => $order->updated_at->format('d/m/Y H:i'),
            'client' => $order->client ? [
                'id' => $order->client->id,
                'name' => $order->client->name,
                'email' => $order->client->email,
                'phone' => $order->client->phone,
            ] : null,
            'pharmacy' => $order->pharmacy ? [
                'id' => $order->pharmacy->id,
                'name' => $order->pharmacy->name,
                'address' => $order->pharmacy->address,
                'phone' => $order->pharmacy->phone,
            ] : null,
            'delivery_address' => $order->delivery_address,
            'delivery' => $order->delivery ? [
                'status' => $order->delivery->status,
                'status_label' => $order->delivery->status_label,
                'status_color' => $order->delivery->status_color,
                'tracking_number' => $order->delivery->tracking_number,
                'shipped_at' => $order->delivery->shipped_at?->format('d/m/Y H:i'),
                'delivered_at' => $order->delivery->delivered_at?->format('d/m/Y H:i'),
                'courier' => $order->delivery->courier ? [
                    'id' => $order->delivery->courier->id,
                    'name' => $order->delivery->courier->name,
                    'phone' => $order->delivery->courier->phone,
                ] : null,
            ] : null,
            'items' => $order->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->price,
                    'total' => $item->price * $item->quantity,
                    'total_formatted' => number_format($item->price * $item->quantity, 2, ',', ' ') . ' €',
                    'image' => $item->product->image_url,
                ];
            }),
            'can_update' => true, // À adapter selon les permissions
        ];

        return Inertia::render('Admin/OrderDetail', [
            'order' => $orderData,
        ]);
    }

    /**
     * Affiche la liste des commandes pour l'administration
     */
    public function adminIndex()
    {
        $orders = Order::with(['client', 'pharmacy', 'items.product'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('Admin/Orders', [
            'orders' => $orders,
        ]);
    }

    /**
     * Détail d'une commande
     */
    public function show(Order $order)
    {
        $user = Auth::user();

        $order->load('items.product', 'pharmacy', 'client');

        return Inertia::render('OrderDetail', [
            'order' => $order,
        ]);
    }

    /**
     * Met à jour le statut d'une commande
     */
    public function updateStatus(Order $order, Request $request)
    {
        $user = auth()->user();
        
        // Vérifier que l'utilisateur a le droit de modifier cette commande
        if ($user->role === 'pharmacy') {
            // Vérifier que la commande appartient à une des pharmacies de l'utilisateur
            if (!$user->pharmacies->contains($order->pharmacy_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Non autorisé à modifier cette commande',
                ], 403);
            }
        } elseif ($user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé',
            ], 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,accepted,in_delivery,delivered,cancelled',
        ]);

        // Vérifier les transitions de statut autorisées
        $allowedTransitions = [
            'pending' => ['accepted', 'cancelled'],
            'accepted' => ['in_delivery', 'cancelled'],
            'in_delivery' => ['delivered'],
        ];

        if (isset($allowedTransitions[$order->status]) && 
            !in_array($validated['status'], $allowedTransitions[$order->status])) {
            return response()->json([
                'success' => false,
                'message' => 'Transition de statut non autorisée',
            ], 422);
        }

        $order->update(['status' => $validated['status']]);

        // Vous pourriez ajouter ici une notification au client
        // Exemple: event(new OrderStatusUpdated($order));

        return response()->json([
            'success' => true,
            'message' => 'Statut de la commande mis à jour avec succès',
            'order' => $order->fresh(['items.product', 'client'])
        ]);
    }
}
