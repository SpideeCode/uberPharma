<?php

namespace App\Http\Controllers;

use App\Models\Pharmacy;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class PharmacyController extends Controller
{
    // Liste toutes les pharmacies
    public function index()
    {
        $pharmacies = Pharmacy::with('user')->get();

        return Inertia::render('Pharmacies/Index', [
            'pharmacies' => $pharmacies,
            'auth' => Auth::user(),
        ]);
    }

    // Page création
    public function create()
    {
        return Inertia::render('Pharmacies/Create');
    }

    // Enregistrement d'une nouvelle pharmacie
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'status' => 'required|in:open,closed',
        ]);

        Pharmacy::create([
            'user_id' => Auth::id(),
            'name' => $data['name'],
            'address' => $data['address'],
            'phone' => $data['phone'] ?? null,
            'status' => $data['status'],
        ]);

        return redirect()->route('pharmacies.index')->with('success', 'Pharmacie créée avec succès.');
    }

    // Page édition
    public function edit(Pharmacy $pharmacy)
    {
        // Vérifie si l'utilisateur connecté est le propriétaire
        if ($pharmacy->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        return Inertia::render('Pharmacies/Edit', [
            'pharmacy' => $pharmacy,
        ]);
    }

    // Mise à jour
    public function update(Request $request, Pharmacy $pharmacy)
    {
        if ($pharmacy->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'status' => 'required|in:open,closed',
        ]);

        $pharmacy->update($data);

        return redirect()->route('pharmacies.index')->with('success', 'Pharmacie mise à jour.');
    }

    // Suppression
    public function destroy(Pharmacy $pharmacy)
    {
        if ($pharmacy->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $pharmacy->delete();

        return redirect()->route('pharmacies.index')->with('success', 'Pharmacie supprimée.');
    }
}
