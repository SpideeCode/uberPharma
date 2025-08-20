<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Pharmacy;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PharmacyProductController extends Controller
{
    // Afficher les produits et pharmacies du pharmacien
    public function index()
{
    $user = auth()->user();

    // Récupérer les pharmacies du pharmacien
    $pharmacies = $user->pharmacies()->get();

    // Récupérer tous les produits des pharmacies du pharmacien
    $products = Product::whereIn('pharmacy_id', $pharmacies->pluck('id'))->get();

    return Inertia::render('PharmacyProducts', [
        'pharmacies' => $pharmacies,
        'products' => $products,
        'auth' => [
            'id' => $user->id,
            'name' => $user->name,
            'role' => $user->role,
        ],
    ]);
}

    // Ajouter un nouveau produit
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'pharmacy_id' => 'required|exists:pharmacies,id',
        ]);

        // Vérifier que la pharmacie appartient bien au pharmacien
        $pharmacy = Pharmacy::where('id', $request->pharmacy_id)
                            ->where('user_id', Auth::id())
                            ->firstOrFail();

        Product::create([
            'name' => $request->name,
            'price' => $request->price,
            'stock' => $request->stock,
            'pharmacy_id' => $pharmacy->id,
        ]);

        return redirect()->back();
    }

    // Modifier un produit
    public function edit(Product $product)
    {
        $this->authorizeProduct($product);
        $pharmacies = Pharmacy::where('user_id', Auth::id())->get();

        return Inertia::render('EditProduct', [
            'product' => $product,
            'pharmacies' => $pharmacies,
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $this->authorizeProduct($product);

        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'pharmacy_id' => 'required|exists:pharmacies,id',
        ]);

        // Vérifier que la nouvelle pharmacie appartient bien au pharmacien
        $pharmacy = Pharmacy::where('id', $request->pharmacy_id)
                            ->where('user_id', Auth::id())
                            ->firstOrFail();

        $product->update([
            'name' => $request->name,
            'price' => $request->price,
            'stock' => $request->stock,
            'pharmacy_id' => $pharmacy->id,
        ]);

        return redirect()->back();
    }

    // Supprimer un produit
    public function destroy(Product $product)
    {
        $this->authorizeProduct($product);
        $product->delete();

        return redirect()->back();
    }

    // Vérifie que le produit appartient bien à l'utilisateur
    private function authorizeProduct(Product $product)
    {
        if ($product->pharmacy->user_id !== Auth::id()) {
            abort(403, "Vous n'êtes pas autorisé à modifier ce produit.");
        }
    }
}
