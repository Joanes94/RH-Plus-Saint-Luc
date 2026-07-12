<?php

namespace App\Http\Controllers;

use App\Models\Contrat;
use App\Models\ConfigRh;
use App\Models\GrilleSalariale;
use App\Models\Personnel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContratController extends Controller
{
    // ── Création ──────────────────────────────────────────────────────────────
    public function create(Personnel $personnel)
    {
        return view('contrats.create', [
            'personnel'  => $personnel,
            'types'      => Contrat::typesContrat(),
            'categories' => GrilleSalariale::categories(),
            'grille'     => $this->grillePourJs(),
        ]);
    }

    public function store(Request $request, Personnel $personnel)
    {
        $data = $this->validateContrat($request);
        $data['personnel_id'] = $personnel->id;
        $data['created_by']   = Auth::id();

        Contrat::create($data);

        return redirect()->route('personnel.show', $personnel)
            ->with('success', 'Contrat ajouté avec succès.');
    }

    // ── Modification ─────────────────────────────────────────────────────────
    public function edit(Personnel $personnel, Contrat $contrat)
    {
        return view('contrats.edit', [
            'personnel'  => $personnel,
            'contrat'    => $contrat,
            'types'      => Contrat::typesContrat(),
            'categories' => GrilleSalariale::categories(),
            'grille'     => $this->grillePourJs(),
        ]);
    }

    public function update(Request $request, Personnel $personnel, Contrat $contrat)
    {
        $data = $this->validateContrat($request);
        $contrat->update($data);

        return redirect()->route('personnel.show', $personnel)
            ->with('success', 'Contrat mis à jour.');
    }

    // ── Suppression ───────────────────────────────────────────────────────────
    public function destroy(Personnel $personnel, Contrat $contrat)
    {
        $contrat->delete();

        return redirect()->route('personnel.show', $personnel)
            ->with('success', 'Contrat supprimé.');
    }

    // ── Document imprimable (CDD / CDI / Prestation) ─────────────────────────
    public function document(Personnel $personnel, Contrat $contrat)
    {
        abort_unless(in_array($contrat->type_contrat, ['CDD', 'CDI', 'Prestataire']), 422,
            "La génération du document n'est disponible que pour les contrats CDD, CDI ou Prestation.");

        $logoPath = public_path('images/letterhead/logo_archidiocese.jpeg');
        $evePath  = public_path('images/letterhead/photo_eveque.jpeg');

        $data = [
            'personnel'     => $personnel,
            'contrat'       => $contrat,
            'organisation'  => ConfigRh::get('organisation', "L'Archidiocèse de Cotonou"),
            'ville'         => ConfigRh::get('ville', 'Cotonou'),
            'employeur_nom'       => ConfigRh::get('contrat_employeur_nom', "L'ARCHIDIOCÈSE DE COTONOU"),
            'employeur_adresse'   => ConfigRh::get('contrat_employeur_adresse',
                "sis au lot 624-B, lieudit Awhouanlèko, (Cadjèhoun), 12ème Arrondissement, Commune de Cotonou, IFU: 6201000203005, 01 BP: 491; Tél: 21 30 01 45"),
            'representant_nom'    => ConfigRh::get('contrat_representant_nom', 'Roger HOUNGBEDJI'),
            'representant_titre'  => ConfigRh::get('contrat_representant_titre', 'Archevêque, op.'),
            'delegataire_nom'     => ConfigRh::get('contrat_delegataire_nom', 'Abbé Théophile AKOHA'),
            'delegataire_titre'   => ConfigRh::get('contrat_delegataire_titre', 'Vicaire Général'),
            'directrice_travail'  => ConfigRh::get('contrat_directrice_travail', 'Mireille C. LEGBA ADANKON'),
            'logo_b64'      => file_exists($logoPath) ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($logoPath)) : null,
            'eveque_b64'    => file_exists($evePath)  ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($evePath))  : null,
        ];

        if ($contrat->type_contrat === 'Prestataire') {
            return view('contrats.document_prestation', $data);
        }

        return view('contrats.document', $data);
    }

    // ── Validation ────────────────────────────────────────────────────────────
    private function validateContrat(Request $request): array
    {
        $data = $request->validate([
            'type_contrat'       => 'required|string|max:100',
            'fonction'           => 'nullable|string|max:150',
            'service'            => 'nullable|string|max:150',
            'categorie'          => 'nullable|string|max:10',
            'echelon'            => 'nullable|integer|min:1|max:11',
            'date_effet_echelon' => 'nullable|date',
            'centre'             => 'nullable|string|max:200',
            'lieu_conge'         => 'nullable|string|max:150',
            'salaire_base'       => 'nullable|numeric|min:0',
            'honoraire_garde'       => 'nullable|numeric|min:0',
            'honoraire_permanence'  => 'nullable|numeric|min:0',
            'date_debut'         => 'required|date',
            'duree_mois'         => 'nullable|integer|min:1|max:60',
            'date_fin'           => 'nullable|date|after_or_equal:date_debut',
            'date_debauchage'    => 'nullable|date',
            'motif_debauchage'   => 'nullable|string|max:255',
            'lieu_signature'     => 'nullable|string|max:150',
            'date_signature'     => 'nullable|date',
            'numero_visa'        => 'nullable|string|max:100',
            'date_visa'          => 'nullable|date',
            'statut'             => 'nullable|in:actif,termine,rompu',
        ]);

        // Un CDD doit avoir une durée pour pouvoir calculer sa date de fin.
        if (in_array($data['type_contrat'], ['CDD', 'Prestataire']) && empty($data['duree_mois']) && empty($data['date_fin'])) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'duree_mois' => "Merci d'indiquer la durée (en mois) ou une date de fin pour ce contrat à durée déterminée.",
            ]);
        }

        return $data;
    }

    /** Grille salariale formatée pour être injectée en JS côté formulaire. */
    private function grillePourJs(): array
    {
        $grille = [];
        foreach (GrilleSalariale::all() as $case) {
            $grille[$case->categorie][$case->echelon] = [
                'salaire'     => $case->salaire,
                'coefficient' => (float) $case->coefficient,
            ];
        }
        return $grille;
    }
}