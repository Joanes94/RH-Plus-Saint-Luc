<?php

namespace App\Http\Controllers;

use App\Models\Personnel;
use App\Models\ConfigRh;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RapportController extends Controller
{
    /**
     * Page de sélection des filtres pour le rapport.
     */
    public function personnel(Request $request)
    {
        // Stats de prévisualisation selon les filtres
        $query = $this->buildQuery($request);

        $stats = [
            'total'     => (clone $query)->count(),
            'hommes'    => (clone $query)->where('sexe', 'M')->count(),
            'femmes'    => (clone $query)->where('sexe', 'F')->count(),
            'cdi'       => (clone $query)->where('type_contrat', 'CDI')->count(),
            'cdd'       => (clone $query)->where('type_contrat', 'CDD')->count(),
            'actifs'    => (clone $query)->where('statut', 'actif')->count(),
            'inactifs'  => (clone $query)->where('statut', 'inactif')->count(),
            'en_conge'  => (clone $query)->where('statut', 'en_conge')->count(),
            'retraites' => (clone $query)->where('statut', 'retraite')->count(),
            'stagiaires' => (clone $query)->where('type_contrat', 'Stagiaire')->count(),
            'prestataires' => (clone $query)->where('type_contrat', 'Prestataire')->count(),
        ];

        // Aperçu des 5 premiers résultats
        $apercu = (clone $query)->orderBy('nom')->take(5)->get();

        return view('rapports.personnel', [
            'services'     => Personnel::services(),
            'corporations' => Personnel::corporations(),
            'contrats'     => Personnel::typesContrat(),
            'filters'      => $request->only('service', 'sexe', 'statut', 'type_contrat', 'corporation'),
            'stats'        => $stats,
            'apercu'       => $apercu,
        ]);
    }

    /**
     * Génération du PDF / page imprimable.
     */
    public function personnelPdf(Request $request)
    {
        $query      = $this->buildQuery($request);
        $personnels = $query->orderBy('nom')->orderBy('prenoms')->get();

        // Stats globales filtrées
        $stats = [
            'total'        => $personnels->count(),
            'hommes'       => $personnels->where('sexe', 'M')->count(),
            'femmes'       => $personnels->where('sexe', 'F')->count(),
            'cdi'          => $personnels->where('type_contrat', 'CDI')->count(),
            'cdd'          => $personnels->where('type_contrat', 'CDD')->count(),
            'stagiaires'   => $personnels->where('type_contrat', 'Stagiaire')->count(),
            'prestataires' => $personnels->where('type_contrat', 'Prestataire')->count(),
            'actifs'       => $personnels->where('statut', 'actif')->count(),
            'inactifs'     => $personnels->where('statut', 'inactif')->count(),
            'en_conge'     => $personnels->where('statut', 'en_conge')->count(),
            'retraites'    => $personnels->where('statut', 'retraite')->count(),
        ];

        // Répartition par service (filtré)
        $parService = $personnels->groupBy('service')->map(fn($g) => [
            'total'  => $g->count(),
            'hommes' => $g->where('sexe', 'M')->count(),
            'femmes' => $g->where('sexe', 'F')->count(),
        ])->sortByDesc('total');

        // Répartition par corporation (filtré)
        $parCorp = $personnels->groupBy('corporation')->map(fn($g) => [
            'total'  => $g->count(),
            'hommes' => $g->where('sexe', 'M')->count(),
            'femmes' => $g->where('sexe', 'F')->count(),
        ])->sortByDesc('total');

        // Titre du rapport selon les filtres
        $titreFiltre = $this->buildTitreFiltre($request);

        // Logos en base64
        $logoPath  = public_path('images/letterhead/logo_archidiocese.jpeg');
        $evePath   = public_path('images/letterhead/photo_eveque.jpeg');
        $logoB64   = file_exists($logoPath) ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($logoPath)) : null;
        $eveB64    = file_exists($evePath)  ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($evePath))  : null;

        return view('rapports.personnel_pdf', [
            'personnels'   => $personnels,
            'stats'        => $stats,
            'parService'   => $parService,
            'parCorp'      => $parCorp,
            'filters'      => $request->only('service', 'sexe', 'statut', 'type_contrat', 'corporation'),
            'titreFiltre'  => $titreFiltre,
            'date_rapport' => now()->isoFormat('D MMMM YYYY'),
            'organisation' => ConfigRh::get('organisation', 'Institutions Sanitaires Diocésaines'),
            'ville'        => ConfigRh::get('ville', 'Cotonou'),
            'drh_nom'      => ConfigRh::get('drh_nom', ''),
            'logo_b64'     => $logoB64,
            'eveque_b64'   => $eveB64,
        ]);
    }

    // ── Helpers privés ────────────────────────────────────────────────────────

    private function buildQuery(Request $request)
    {
        // Exclure les stagiaires, inclure les prestataires
        $query = Personnel::personnelPrincipal()->whereNull('deleted_at');

        // Application des filtres
        if ($request->filled('service'))      $query->where('service', $request->service);
        if ($request->filled('corporation'))  $query->where('corporation', $request->corporation);
        if ($request->filled('sexe'))         $query->where('sexe', $request->sexe);
        if ($request->filled('statut'))       $query->where('statut', $request->statut);
        if ($request->filled('type_contrat')) $query->where('type_contrat', $request->type_contrat);

        return $query;
    }

    private function buildTitreFiltre(Request $request): string
    {
        $parts = [];
        if ($request->filled('service'))      $parts[] = 'Service : ' . $request->service;
        if ($request->filled('corporation'))  $parts[] = 'Corporation : ' . $request->corporation;
        if ($request->filled('sexe'))         $parts[] = ($request->sexe === 'F' ? '👩 Femmes' : '👨 Hommes');
        if ($request->filled('statut')) {
            $labels = ['actif' => 'Actifs', 'inactif' => 'Inactifs', 'en_conge' => 'En congé', 'retraite' => 'Retraités'];
            $parts[] = $labels[$request->statut] ?? $request->statut;
        }
        if ($request->filled('type_contrat')) $parts[] = 'Contrat : ' . $request->type_contrat;

        return $parts ? implode(' · ', $parts) : 'Tout le personnel';
    }
}