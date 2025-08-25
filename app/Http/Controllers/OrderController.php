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

        // Vérification d'accès
        if ($user->role === 'client' && $order->user_id !== $user->id) {
            abort(403);
        }

        if ($user->role === 'pharmacy' && !$user->pharmacies->contains($order->pharmacy_id)) {
            abort(403);
        }

        $order->load('items.product', 'client', 'pharmacy');

        return Inertia::render('OrderDetail', [
            'order' => $order,
        ]);
    }
}
