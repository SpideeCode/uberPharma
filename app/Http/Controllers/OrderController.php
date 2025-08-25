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
