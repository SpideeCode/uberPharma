<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    /**
     * Affiche la page de paiement avec le panier.
     */
    public function index()
    {
        $cart = Cart::with('items.product')
            ->where('user_id', auth()->id())
            ->first();

        return Inertia::render('Payment', [
            'cart' => $cart,
        ]);
    }

    /**
     * Simule le paiement, crée la commande et redirige vers confirmation.
     */

public function store(Request $request)
{
    $cart = Cart::with('items.product')->where('user_id', auth()->id())->first();

    if (!$cart || $cart->items->isEmpty()) {
        return redirect()->back()->with('error', 'Votre panier est vide.');
    }

    $estimatedMinutes = 30;

    $order = DB::transaction(function () use ($cart, $request) {
        $cartItems = $cart->items;

        $order = Order::create([
            'client_id' => auth()->id(),
            'pharmacy_id' => $cartItems->first()->product->pharmacy_id,
            'courier_id' => null,
            'status' => 'pending',
            'payment_status' => 'paid',
            'total_price' => $cartItems->sum(fn($item) => $item->product->price * $item->quantity),
            'delivery_address' => $request->input('delivery_address', 'Adresse du client'),
            'delivery_latitude' => $request->input('delivery_latitude', 0),
            'delivery_longitude' => $request->input('delivery_longitude', 0),
        ]);

        foreach ($cartItems as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'price' => $item->product->price,
            ]);
        }

        // Vider le panier
        $cart->items()->delete();

        return $order;
    });

    // 🔹 Ici on recharge la commande avec les items pour qu'ils soient disponibles
    $order->load('items.product');

   return redirect()->route('order.confirmation', ['order' => $order->id])
                 ->with(['estimatedMinutes' => $estimatedMinutes]);

}

public function confirmation(Order $order, Request $request)
{
    // Charger les items et les produits associés
    $order->load('items.product');

    return Inertia::render('OrderConfirmation', [
        'order' => $order,
        'estimatedMinutes' => $request->session()->get('estimatedMinutes', 30),
    ]);
}

}