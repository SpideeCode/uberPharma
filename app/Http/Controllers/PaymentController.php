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
     * Affiche la page de paiement pour un panier spécifique
     */
    public function index(Request $request)
    {
        $request->validate([
            'cart_id' => 'required|exists:carts,id',
        ]);

        $cart = Cart::with('items.product', 'pharmacy')
            ->where('user_id', auth()->id())
            ->where('id', $request->cart_id)
            ->first();

        if (!$cart) {
            return redirect()->back()->with('error', 'Panier introuvable.');
        }

        return Inertia::render('Payment', [
            'cart' => $cart,
        ]);
    }

    /**
     * Simule le paiement et crée la commande à partir du panier
     */
    public function store(Request $request)
    {
        $request->validate([
            'cart_id' => 'required|exists:carts,id',
            'delivery_address' => 'nullable|string',
            'delivery_latitude' => 'nullable|numeric',
            'delivery_longitude' => 'nullable|numeric',
        ]);

        $cart = Cart::with('items.product', 'pharmacy')
            ->where('user_id', auth()->id())
            ->where('id', $request->cart_id)
            ->first();

        if (!$cart || $cart->items->isEmpty()) {
            return redirect()->back()->with('error', 'Votre panier est vide.');
        }

        $estimatedMinutes = 30;

        $order = DB::transaction(function () use ($cart, $request) {
            $cartItems = $cart->items;

            $order = Order::create([
                'client_id' => auth()->id(),
                'pharmacy_id' => $cart->pharmacy->id, // <- assure-toi de prendre la pharmacy du cart
                'courier_id' => null,
                'status' => 'pending',
                'payment_status' => 'paid',
                'total_price' => $cartItems->sum(fn($item) => $item->price_at_addition * $item->quantity),
                'delivery_address' => $request->input('delivery_address', 'Adresse du client'),
                'delivery_latitude' => $request->input('delivery_latitude', 0),
                'delivery_longitude' => $request->input('delivery_longitude', 0),
            ]);

            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->price_at_addition,
                ]);
            }

            $cart->items()->delete();

            return $order;
        });

        $order->load('items.product', 'pharmacy'); // <- important

        return redirect()->route('order.confirmation', ['order' => $order->id])
            ->with(['estimatedMinutes' => $estimatedMinutes]);
    }

    public function confirmation(Order $order, Request $request)
    {
        $order->load('items.product', 'pharmacy'); // <- important

        return Inertia::render('OrderConfirmation', [
            'order' => $order,
            'estimatedMinutes' => $request->session()->get('estimatedMinutes', 30),
        ]);
    }
}
