<?php

namespace App\Http\Controllers;

use App\Models\Conge;
use App\Models\Personnel;
use App\Models\ConfigRh;
use App\Services\CalendrierService;
use App\Services\DocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CongeController extends Controller
{
    public function __construct(
        private CalendrierService $cal,
        private DocumentService   $doc,
    ) {}

    // ── Liste ─────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $query = Conge::with('personnel');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->whereHas('personnel', fn($q) =>
                $q->where('nom', 'like', "%$s%")
                  ->orWhere('prenoms', 'like', "%$s%")
            );
        }
        if ($request->filled('statut')) $query->where('statut', $request->statut);
        if ($request->filled('annee'))  $query->where('annee',  $request->annee);

        $conges = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        $annees = Conge::distinct()->orderByDesc('annee')->pluck('annee')->map(fn($a) => (string)$a)->toArray();
        if (empty($annees)) $annees = [(string)date('Y')];

        return view('conges.index', compact('conges', 'annees'));
    }

    // ── Création ──────────────────────────────────────────────────────────────
    public function create()
    {
        $personnels = Personnel::personnelPrincipal()->orderBy('nom')->get();

        $typesConge = [
            'administratif' => 'Congé Administratif',
            'technique'     => 'Congé Technique',
            'maternite'     => 'Congé de Maternité',
        ];

        return view('conges.create', compact('personnels', 'typesConge'));
    }

    public function store(Request $request)
    {
        $rules = [
            'personnel_id' => 'required|exists:personnels,id',
            'type_conge'   => 'required|in:administratif,technique,maternite',
            'date_debut'   => 'required|date',
            'annee'        => 'required|string|max:10',
        ];

        // Pour maternité : date de fin obligatoire (14 semaines)
        if ($request->type_conge === 'maternite') {
            $rules['date_fin'] = 'required|date|after:date_debut';
        } else {
            $rules['nb_jours_demandes'] = 'required|integer|min:1|max:365';
        }

        $data = $request->validate($rules);

        $personnel = Personnel::findOrFail($request->personnel_id);

        // Calcul solde
        $solde = $this->cal->soldeConges($personnel, $request->annee);

        if ($request->type_conge === 'maternite') {
            // Maternité : 14 semaines, dates fixes saisies par l'utilisateur
            $dateDebut = \Carbon\Carbon::parse($request->date_debut);
            $dateFin   = \Carbon\Carbon::parse($request->date_fin);
            $nbJours   = 98; // 14 semaines
            $nbRestants = $solde['restants'];
        } else {
            $nbJours   = (int)$request->nb_jours_demandes;
            $dateDebut = \Carbon\Carbon::parse($request->date_debut);
            $dateFin   = $this->cal->calculerDateFin($dateDebut, $nbJours);
            $nbRestants = $solde['restants'] - $nbJours;
        }

        Conge::create([
            'reference'          => null,
            'personnel_id'       => $request->personnel_id,
            'type_conge'         => $request->type_conge,
            'date_debut'         => $dateDebut,
            'date_fin'           => $dateFin,
            'nb_jours_demandes'  => $nbJours,
            'nb_jours_acquis'    => $solde['acquis'],
            'nb_jours_deja_pris' => $solde['total_pris'],
            'nb_jours_restants'  => $nbRestants,
            'annee'              => $request->annee,
            'observations'       => $request->observations,
            'statut'             => $request->action === 'soumettre' ? 'soumis' : 'brouillon',
            'created_by'         => Auth::id(),
        ]);

        return redirect()->route('conges.index')
            ->with('success', 'Demande de congé enregistrée.');
    }

    // ── Détail ────────────────────────────────────────────────────────────────
    public function show(Conge $conge)
{
    $conge->load('personnel');

    $solde = $this->cal->soldeConges(
        $conge->personnel,
        $conge->annee
    );

    return view('conges.show', compact('conge', 'solde'));
}

    // ── Modification ──────────────────────────────────────────────────────────
    public function edit(Conge $conge)
    {
        abort_if(!$conge->isEditable(), 403);
        $personnels = Personnel::personnelPrincipal()->orderBy('nom')->get();
        $typesConge = [
            'administratif' => 'Congé Administratif',
            'technique'     => 'Congé Technique',
            'maternite'     => 'Congé de Maternité',
        ];
        return view('conges.edit', compact('conge', 'personnels', 'typesConge'));
    }

    public function update(Request $request, Conge $conge)
    {
        abort_if(!$conge->isEditable(), 403);

        $conge->update([
            'type_conge'        => $request->type_conge ?? $conge->type_conge,
            'date_debut'        => $request->date_debut ?? $conge->date_debut,
            'date_fin'          => $request->date_fin   ?? $conge->date_fin,
            'nb_jours_demandes' => $request->nb_jours_demandes ?? $conge->nb_jours_demandes,
            'annee'             => $request->annee ?? $conge->annee,
            'observations'      => $request->observations,
            'statut'            => $request->action === 'soumettre' ? 'soumis' : $conge->statut,
        ]);

        return redirect()->route('conges.show', $conge)
            ->with('success', 'Congé mis à jour.');
    }

    // ── Suppression ───────────────────────────────────────────────────────────
    public function destroy(Conge $conge)
    {
        abort_if(!$conge->isEditable(), 403);
        $conge->delete();
        return redirect()->route('conges.index')->with('success', 'Congé supprimé.');
    }

    // ── Approbation (DRH) ─────────────────────────────────────────────────────
    public function approuver(Request $request, Conge $conge)
    {
        abort_if($conge->statut !== 'soumis', 422);

        $signPath = null;
        if ($request->hasFile('signature')) {
            $signPath = $this->doc->sauvegarderSignature($request->file('signature'));
        } else {
            $signPath = ConfigRh::get('drh_signature_path');
        }

        $conge->update([
            'statut'         => 'approuve',
            'approuve_par'   => Auth::id(),
            'approuve_le'    => now(),
            'signature_path' => $signPath,
            'reference'      => $request->filled('reference') ? $request->reference : $conge->reference,
        ]);

        return redirect()->route('conges.show', $conge)
            ->with('success', 'Congé approuvé. Document disponible.');
    }

    public function rejeter(Request $request, Conge $conge)
    {
        abort_if($conge->statut !== 'soumis', 422);

        $conge->update([
            'statut'      => 'rejete',
            'rejete_par'  => Auth::id(),
            'rejete_le'   => now(),
            'motif_rejet' => $request->motif_rejet,
        ]);

        return redirect()->route('conges.show', $conge)
            ->with('success', 'Congé rejeté.');
    }

    // ── Document officiel ──────────────────────────────────────────────────────
    public function document(Conge $conge)
    {
        abort_if($conge->statut !== 'approuve', 403);
        $conge->load('personnel');

        $data = $this->doc->buildCongeData($conge);

        // Surcharger la signature_url en base64
        $signPath = $conge->signature_path ?: ConfigRh::get('drh_signature_path');
        $signUrl  = null;
        if ($signPath) {
            $full = Storage::disk('public')->path($signPath);
            if (file_exists($full)) {
                $mime    = mime_content_type($full) ?: 'image/png';
                $signUrl = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($full));
            }
        }

        return view('conges.document', array_merge($data, [
            'document'      => $conge,
            'personnel'     => $conge->personnel,
            'signature_url' => $signUrl,
        ]));
    }

    // ── Calcul date de fin (AJAX) ──────────────────────────────────────────────
    public function calculerDateFin(Request $request)
    {
        $request->validate([
            'date_debut'        => 'required|date',
            'nb_jours_demandes' => 'required|integer|min:1',
        ]);

        $dateDebut = \Carbon\Carbon::parse($request->date_debut);
        $dateFin   = $this->cal->calculerDateFin($dateDebut, (int)$request->nb_jours_demandes);

        return response()->json([
            'date_fin'    => $dateFin->format('Y-m-d'),
            'date_fin_fr' => $dateFin->isoFormat('dddd D MMMM YYYY'),
        ]);
    }
}
