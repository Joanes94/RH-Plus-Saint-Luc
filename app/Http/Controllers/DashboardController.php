<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Conge;
use App\Models\Absence;
use App\Models\Demande;
use App\Models\Personnel;
use App\Models\Stagiaire;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->isDRH()) {
            return redirect()->route('drh.dashboard');
        }

        $base = Personnel::personnelPrincipal();

        $stats = [
            'total'    => (clone $base)->count(),
            'actifs'   => (clone $base)->where('statut', 'actif')->count(),
            'conges'   => Conge::where('statut', 'approuve')
                                ->whereDate('date_debut', '<=', now())
                                ->whereDate('date_fin', '>=', now())
                                ->distinct('personnel_id')
                                ->count('personnel_id'),
            'inactifs' => (clone $base)->where('statut', 'inactif')->count(),
            'hommes'   => (clone $base)->where('statut', 'actif')->where('sexe', 'M')->count(),
            'femmes'   => (clone $base)->where('statut', 'actif')->where('sexe', 'F')->count(),
            'totalStagiaires' => Stagiaire::count(),
            'anciensTravailleurs' => (clone $base)->anciensTravailleurs()->count(),
        ];

        $anciensRecents = (clone $base)->anciensTravailleurs()->orderByDesc('date_depart')->take(5)->get();

        $congesPendants   = Conge::with('personnel')->where('statut', 'soumis')->latest()->take(5)->get();
        $absencesPendants = Absence::with('personnel')->where('statut', 'soumis')->latest()->take(5)->get();
        $demandesPendants = Demande::with('personnel')->where('statut', 'soumis')->latest()->take(5)->get();

        $totalEnAttente = Conge::where('statut', 'soumis')->count()
            + Absence::where('statut', 'soumis')->count()
            + Demande::where('statut', 'soumis')->count();

        return view('dashboard.assistant_rh', compact(
            'user', 'stats',
            'congesPendants', 'absencesPendants', 'demandesPendants',
            'totalEnAttente', 'anciensRecents'
        ));
    }
}