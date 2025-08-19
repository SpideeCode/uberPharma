<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Pharmacy;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PharmacyController extends Controller
{
   public function index()
{
    $pharmacies = Pharmacy::with('user')->get();

    
    return Inertia::render('AdminPharmacies', [
        'pharmacies' => $pharmacies
    ]);
}


    public function create()
    {
        return Inertia::render('CreatePharmacy');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
        ]);

        Pharmacy::create([
            'name' => $validated['name'],
            'address' => $validated['address'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('home')
            ->with('success', 'Pharmacie créée avec succès.');
    }

public function show(Pharmacy $pharmacy)
{
    // Charger les relations essentielles
    $pharmacy->load('products', 'user');

    // Récupérer le panier de l'utilisateur connecté pour cette pharmacie
    $cart = null;
    if (auth()->check() && auth()->user()->role === 'client') {
        $cart = Cart::with('items.product')
            ->where('user_id', auth()->id())
            ->where('pharmacy_id', $pharmacy->id)
            ->first();
    }

    return Inertia::render('ShowPharmacy', [
        'pharmacy' => $pharmacy,
        'cart'     => $cart,
        'auth'     => auth()->user(), // pour le frontend
    ]);
}





    public function edit(Pharmacy $pharmacy)
    {

        return Inertia::render('EditPharmacy', [
            'pharmacy' => $pharmacy
        ]);
    }


    public function update(Request $request, Pharmacy $pharmacy)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
        ]);

        $pharmacy->update($validated);

        return redirect()->route('pharmacies.show', $pharmacy->id)
            ->with('success', 'Pharmacie mise à jour avec succès.');
    }


    public function destroy(Pharmacy $pharmacy)
    {
        $pharmacy->delete();

        return redirect()->back()->with('success', 'Pharmacie supprimée.');
    }

    // public function myPharmacies()
    // {
    //     $pharmacies = auth()->user()->pharmacies()->get();

    //     return Inertia::render('Pharmacies/MyPharmacies', [
    //         'pharmacies' => $pharmacies,
    //         'auth' => auth()->user(),
    //     ]);
    // }
}
