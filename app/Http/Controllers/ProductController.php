<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Pharmacy;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('pharmacy')->get();
        $pharmacies = Pharmacy::all();

        return Inertia::render('AdminProducts', [
            'products' => $products,
            'pharmacies' => $pharmacies,
        ]);
    }
    // Formulaire création produit
 public function create(Request $request)
{
    $pharmacyId = $request->query('pharmacy_id'); // Récupération depuis l'URL
    $pharmacy = Pharmacy::findOrFail($pharmacyId);

    return Inertia::render('CreateProduct', [
        'pharmacy' => $pharmacy,
    ]);
}

    // Stocker le produit
    // Stocker le produit
    public function store(Request $request)
    {
        // Validation des données
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'price'       => 'required|numeric|min:0',
            'stock'       => 'required|integer|min:0',
            'pharmacy_id' => 'required|exists:pharmacies,id', // On s'assure que la pharmacie existe
        ]);

        // Création du produit avec le pharmacy_id fourni
        Product::create($validated);

        // Redirection vers la page de la pharmacie avec message de succès
        return redirect()->route('pharmacies.show', $validated['pharmacy_id'])
            ->with('success', 'Produit ajouté avec succès.');
    }


    // Formulaire édition produit
    public function edit(Product $product)
    {
        return Inertia::render('EditProduct', [
            'product' => $product,
            'pharmacy' => $product->pharmacy,
        ]);
    }

    // Mettre à jour le produit
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        $product->update($validated);

        return redirect()->route('pharmacies.show', $product->pharmacy_id)
            ->with('success', 'Produit mis à jour.');
    }

    // Supprimer le produit
    public function destroy(Product $product)
    {
        $pharmacyId = $product->pharmacy_id;
        $product->delete();

        return redirect()->route('pharmacies.show', $pharmacyId)
            ->with('success', 'Produit supprimé.');
    }
}
