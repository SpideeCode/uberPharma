<?php

namespace App\Http\Controllers;

use App\Models\Pharmacy;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PharmacyController extends Controller
{
    public function index()
    {
        $pharmacies = Pharmacy::with('user')->get();

        return Inertia::render('Index', [
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
        return Inertia::render('Pharmacies/Show', [
            'pharmacy' => $pharmacy->load('user')
        ]);
    }

    public function edit(Pharmacy $pharmacy)
    {
        return Inertia::render('Pharmacies/Edit', [
            'pharmacy' => $pharmacy,
        ]);
    }

    public function update(Request $request, Pharmacy $pharmacy)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
        ]);

        $pharmacy->update($data);

        return redirect()->route('pharmacies.index')
                         ->with('success', 'Pharmacie mise à jour.');
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
