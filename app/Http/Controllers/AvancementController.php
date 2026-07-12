<?php

namespace App\Http\Controllers;

use App\Models\Avancement;
use App\Models\ConfigRh;
use App\Services\AvancementService;
use Illuminate\Http\Request;

class AvancementController extends Controller
{
    /** Déclenche manuellement la vérification des avancements dus. */
    public function verifier(AvancementService $service)
    {
        $resultats = $service->traiterTout();
        $total = $resultats['echelons'] + $resultats['bonifications'];

        $message = $total === 0
            ? "Vérification effectuée : aucun avancement n'était dû aujourd'hui."
            : "Vérification effectuée : {$resultats['echelons']} avancement(s) d'échelon et {$resultats['bonifications']} bonification(s) appliqué(s).";

        return back()->with('success', $message);
    }

    /**
     * Document imprimable pour un avancement.
     * - type "echelon"      : une seule lettre (DRH → agent)
     * - type "bonification" : deux lettres, sélectionnées via ?doc=employe|directeur
     */
    public function document(Request $request, Avancement $avancement)
    {
        $avancement->load('personnel.contrats');
        $contrat = $avancement->contrat ?? $avancement->personnel->contrat_actif;

        $data = [
            'avancement'   => $avancement,
            'personnel'    => $avancement->personnel,
            'contrat'      => $contrat,
            'organisation' => ConfigRh::get('organisation', "Direction Diocésaine de la Santé"),
            'ville'        => ConfigRh::get('ville', 'Cotonou'),
            'drh_nom'      => ConfigRh::get('avancement_drh_nom', 'Abbé Wilfried KOUTOUKLOUI'),
            'directeur_diocesain_nom' => ConfigRh::get('avancement_directeur_diocesain_nom', 'Abbé Paul HESSOU'),
        ];

        if ($avancement->type === 'bonification') {
            $vue = $request->query('doc') === 'directeur' ? 'avancements.document_bonification_directeur' : 'avancements.document_bonification_employe';
            return view($vue, $data);
        }

        return view('avancements.document_echelon', $data);
    }
}
