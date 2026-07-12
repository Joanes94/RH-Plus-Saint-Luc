<?php

namespace App\Services;

use App\Models\Avancement;
use App\Models\ConfigRh;
use App\Models\Contrat;
use App\Models\GrilleSalariale;
use App\Models\Personnel;
use Carbon\Carbon;

class AvancementService
{
    /** Âge à partir duquel la bonification (article 88) s'applique. */
    public const AGE_BONIFICATION = 58;

    /** Ancienneté (en mois) requise entre deux avancements d'échelon. */
    public const MOIS_ENTRE_ECHELONS = 24;

    /**
     * Parcourt tout le personnel actif et applique automatiquement les
     * avancements d'échelon et bonifications qui sont dus.
     *
     * @return array{echelons:int, bonifications:int, avancements: \Illuminate\Support\Collection}
     */
    public function traiterTout(): array
    {
        $resultats = collect();

        $personnels = Personnel::whereIn('statut', ['actif', 'en_conge'])
            ->with(['contrats' => fn ($q) => $q->orderByDesc('date_debut'), 'avancements'])
            ->get();

        foreach ($personnels as $personnel) {
            $contrat = $personnel->contrat_actif;
            if (!$contrat || !$contrat->categorie || !$contrat->echelon) {
                continue; // rien à évaluer sans catégorie/échelon renseignés
            }

            $bonification = $this->traiterBonification($personnel, $contrat);
            if ($bonification) {
                $resultats->push($bonification);
                continue; // une fois bonifié, on ne recalcule plus l'échelon ce passage-ci
            }

            $echelon = $this->traiterEchelon($personnel, $contrat);
            if ($echelon) {
                $resultats->push($echelon);
            }
        }

        return [
            'echelons'      => $resultats->where('type', 'echelon')->count(),
            'bonifications' => $resultats->where('type', 'bonification')->count(),
            'avancements'   => $resultats,
        ];
    }

    /**
     * Applique la bonification des 58 ans (article 88) si l'agent vient
     * d'atteindre l'âge requis et n'en a pas déjà bénéficié.
     */
    public function traiterBonification(Personnel $personnel, Contrat $contrat): ?Avancement
    {
        if (!$personnel->date_naissance || $personnel->age < self::AGE_BONIFICATION) {
            return null;
        }

        $dejaBonifie = $personnel->relationLoaded('avancements')
            ? $personnel->avancements->contains('type', 'bonification')
            : $personnel->avancements()->where('type', 'bonification')->exists();

        if ($dejaBonifie) {
            return null;
        }

        $caseBase = GrilleSalariale::caseGrille($contrat->categorie, 1);
        if (!$caseBase) return null;

        $coefficient   = (float) ConfigRh::get('coefficient_bonification', 2.3);
        $ancienSalaire = (int) $contrat->salaire_base;
        $nouveauSalaire = (int) round($caseBase->salaire * $coefficient);

        $avancement = Avancement::create([
            'personnel_id'          => $personnel->id,
            'contrat_id'            => $contrat->id,
            'type'                  => 'bonification',
            'date_effet'            => now()->toDateString(),
            'ancienne_categorie'    => $contrat->categorie,
            'ancien_echelon'        => $contrat->echelon,
            'nouvelle_categorie'    => $contrat->categorie,
            'nouvel_echelon'        => $contrat->echelon,
            'ancien_salaire'        => $ancienSalaire,
            'nouveau_salaire'       => $nouveauSalaire,
            'coefficient_applique'  => $coefficient,
            'numero_reference'      => $this->genererReference('bonification'),
        ]);

        $contrat->update(['salaire_base' => $nouveauSalaire]);

        return $avancement;
    }

    /**
     * Fait avancer l'agent d'un échelon si l'ancienneté depuis le dernier
     * avancement (ou depuis l'embauche) a atteint 24 mois, et qu'il n'est
     * pas déjà au dernier échelon de la grille.
     */
    public function traiterEchelon(Personnel $personnel, Contrat $contrat): ?Avancement
    {
        if ((int) $contrat->echelon >= GrilleSalariale::ECHELON_MAX) {
            return null;
        }

        $dateReference = $this->dateDernierAvancementEchelon($personnel)
            ?? $contrat->date_effet_echelon
            ?? $personnel->date_embauche_centre;
        if (!$dateReference) {
            return null; // pas de point de départ fiable pour calculer l'ancienneté
        }

        if ($dateReference->diffInMonths(now()) < self::MOIS_ENTRE_ECHELONS) {
            return null;
        }

        $nouvelEchelon = $contrat->echelon + 1;
        $caseGrille    = GrilleSalariale::caseGrille($contrat->categorie, $nouvelEchelon);
        if (!$caseGrille) return null;

        $ancienSalaire = (int) $contrat->salaire_base;

        $avancement = Avancement::create([
            'personnel_id'          => $personnel->id,
            'contrat_id'            => $contrat->id,
            'type'                  => 'echelon',
            'date_effet'            => now()->toDateString(),
            'ancienne_categorie'    => $contrat->categorie,
            'ancien_echelon'        => $contrat->echelon,
            'nouvelle_categorie'    => $contrat->categorie,
            'nouvel_echelon'        => $nouvelEchelon,
            'ancien_salaire'        => $ancienSalaire,
            'nouveau_salaire'       => $caseGrille->salaire,
            'coefficient_applique'  => $caseGrille->coefficient,
            'numero_reference'      => $this->genererReference('echelon'),
        ]);

        $contrat->update(['echelon' => $nouvelEchelon, 'salaire_base' => $caseGrille->salaire]);

        return $avancement;
    }

    private function dateDernierAvancementEchelon(Personnel $personnel): ?Carbon
    {
        $dernier = $personnel->relationLoaded('avancements')
            ? $personnel->avancements->where('type', 'echelon')->sortByDesc('date_effet')->first()
            : $personnel->avancements()->where('type', 'echelon')->orderByDesc('date_effet')->first();

        return $dernier?->date_effet;
    }

    /**
     * N° de référence auto-incrémenté par type et par année, au format
     * personnalisable (config_rh: reference_echelon / reference_bonification).
     */
    public function genererReference(string $type): string
    {
        $annee = now()->year;
        $sequence = Avancement::where('type', $type)->whereYear('date_effet', $annee)->count() + 1;
        $sequenceFormatee = str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);

        $modele = $type === 'bonification'
            ? ConfigRh::get('reference_bonification', '{seq}.{mois}.{annee}/AC/VECI/DDIS/CRH-DDIS/SA')
            : ConfigRh::get('reference_echelon', '{seq}/{mois}-{anneeCourte}/AC/DDIS/CRH-DDIS/DIR/DRH/ARH');

        return str_replace(
            ['{seq}', '{mois}', '{annee}', '{anneeCourte}'],
            [$sequenceFormatee, now()->format('m'), $annee, now()->format('y')],
            $modele
        );
    }
}
