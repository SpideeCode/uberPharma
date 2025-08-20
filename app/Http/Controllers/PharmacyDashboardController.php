<?php

namespace App\Http\Controllers;

use App\Models\Pharmacy;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PharmacyDashboardController extends Controller
{
    public function index()
    {
        // Récupérer uniquement les pharmacies de l'utilisateur connecté
        $pharmacies = Pharmacy::where('user_id', Auth::id())->get();

        return Inertia::render('PharmacyDashboard', [
            'pharmacies' => $pharmacies,
        ]);
    }
}
