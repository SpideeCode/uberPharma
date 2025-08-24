<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Toutes les commandes du client connecté
        $orders = Order::with('items.product', 'pharmacy')
            ->where('client_id', Auth::id()) // ← ici
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('ClientOrders', [
            'orders' => $orders,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOrderRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */


    public function show(Order $order)
    {
        // Vérifie que la commande appartient bien au client
        if ($order->client_id !== Auth::id()) { // ← ici
            abort(403);
        }

        $order->load('items.product', 'pharmacy');

        return Inertia::render('OrderDetail', [
            'order' => $order,
        ]);
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Order $order)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOrderRequest $request, Order $order)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        //
    }

    /**
     * Confirmation de commande après paiement.
     */
    public function confirmation($orderId)
    {
        $order = Order::with('items.product', 'pharmacy')->findOrFail($orderId);

        return Inertia::render('OrderConfirmation', [
            'order' => $order,
        ]);
    }
}
