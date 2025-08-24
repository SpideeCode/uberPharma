<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    /**
     * Affiche tous les paniers du client avec les items
     */
    public function index()
    {
        $carts = Cart::with('items.product', 'pharmacy')
                     ->where('user_id', Auth::id())
                     ->get()
                     ->map(function ($cart) {
                         $cart->total_price = $cart->items->sum(fn($item) => $item->price_at_addition * $item->quantity);
                         return $cart;
                     });

        return inertia('Cart', ['carts' => $carts]);
    }

    /**
     * Ajoute un produit au panier d'une pharmacie
     */
    public function add(Request $request)
    {
        $request->validate([
            'product_id'  => 'required|exists:products,id',
            'quantity'    => 'required|integer|min:1',
            'pharmacy_id' => 'required|exists:pharmacies,id',
        ]);

        $product = Product::findOrFail($request->product_id);

        // Récupère ou crée le panier pour ce client et cette pharmacie
        $cart = Cart::firstOrCreate(
            ['user_id' => Auth::id(), 'pharmacy_id' => $request->pharmacy_id]
        );

        // Vérifie si le produit est déjà dans le panier
        $cartItem = CartItem::firstOrNew([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
        ]);

        $cartItem->quantity = ($cartItem->quantity ?? 0) + (int)$request->quantity;
        $cartItem->price_at_addition = $product->price;
        $cartItem->save();

        return back()->with('success', 'Produit ajouté au panier');
    }

    /**
     * Retire un produit du panier
     */
    public function remove(Request $request)
    {
        $request->validate([
            'cart_item_id' => 'required|exists:cart_items,id',
        ]);

        $cartItem = CartItem::findOrFail($request->cart_item_id);
        $cartItem->delete();

        return back()->with('success', 'Produit retiré du panier');
    }

    /**
     * Vide complètement un panier par pharmacie
     */
    public function clear(Request $request)
    {
        $request->validate([
            'cart_id' => 'required|exists:carts,id',
        ]);

        $cart = Cart::findOrFail($request->cart_id);
        $cart->items()->delete();

        return back()->with('success', 'Panier vidé');
    }
}
