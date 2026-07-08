<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Personnel extends Model
{
    use SoftDeletes;

    protected $table = 'personnels';

    protected $fillable = [
        'nom', 'prenoms', 'email', 'photo_path',
        'date_naissance', 'lieu_naissance',
        'sexe', 'telephone', 'situation_matrimoniale', 'diplome',
        'autorisation_clientele_privee',
        'corporation', 'service', 'type_contrat', 'categorie_echelon',
        'date_embauche_centre', 'date_embauche_isd',
        'date_debauchage', 'motif_debauchage',
        'numero_cnss', 'date_depart_retraite', 'date_fin_contrat',
        'conge_annee', 'conge_jours',
        'contact_urgence_nom', 'contact_urgence_telephone',
        'affectation', 'date_affectation',
        'statut', 'created_by',
    ];

    protected $casts = [
        'date_naissance'         => 'date',
        'date_embauche_centre'   => 'date',
        'date_embauche_isd'      => 'date',
        'date_debauchage'        => 'date',
        'date_depart_retraite'   => 'date',
        'date_fin_contrat'       => 'date',
        'date_affectation'       => 'date',
        'autorisation_clientele_privee' => 'boolean',
    ];

    // ── Scope : exclure uniquement les Stagiaires de la liste principale ──────
    // Les Prestataires restent dans Personnel
    public function scopePersonnelPrincipal($query)
    {
        return $query->where(function($q) {
            $q->where('type_contrat', '!=', 'Stagiaire')
              ->orWhereNull('type_contrat');
        });
    }

    // ── Listes de référence ───────────────────────────────────────────────────

    public static function corporations(): array
    {
        return [
            'AGENT ADMINISTRATIF',
            'AGENT DE PHARMACIE',
            "AGENT D'ENTRETIEN",
            'AIDE-SOIGNANT',
            'AIDE SOIGNANT PROMU',
            'ANESTHESISTE',
            'ASSISTANT DENTAIRE',
            'ASSISTANT LABORATOIRE',
            'ASSISTANT RH',
            'BIOLOGISTE MEDICALE',
            'CAISSIER',
            'CARDIOLOGUE',
            'CHAUFFEUR',
            'CHEF SERVICE FINANCIER ET COMPTABLE',
            'CHIRURGIEN MAXILO FACIALE',
            'CHIRURGIEN',
            'COMPTABLE',
            'DENTISTE',
            'DERMATOLOGUE',
            'DIRECTEUR',
            'DIRECTEUR DES RESSOURCES HUMAINES',
            'ENDOCRINOLOGUE',
            'GASTROLOGUE',
            'INFIRMIER',
            'KINESITHERAPEUTE',
            'MAGASINIER',
            'MEDECIN ANESTHESISTE REANIMATEUR',
            'MEDECIN',
            'MEDECIN CHEF',
            'NEPHROLOGUE',
            'NEURO CHIRURGIEN',
            'NEUROLOGUE',
            'SURVEILLANT',
            'OPHTAMOLOGUE',
            'ORL',
            'PSYCHIATRE',
            'RADIOLOGUE',
            'SECRETAIRE ADMINISTRATIVE',
            'SECRETAIRE DE LABORATOIRE',
            'SECRETAIRE MEDICAL',
            'STATISTICIEN',
            'TECHNICIEN DE LABORATOIRE',
            'TECHNICIEN EN IMAGERIE MEDICALE',
            'TECHNICIEN SUPERIEUR EN ODONTO',
            'TRAUMATOLOGUE',
            'UROLOGUE',
        ];
    }

    public static function services(): array
    {
        return [
            'MEDECINE',
            'COMPTABILITE',
            'CAISSE',
            'PHARMACIE',
            'FACTURATION',
            'MAGASIN',
            'CHIRURGIE',
            'KINESITHERAPIE',
            'STOMATOLOGIE',
            'MATERNITE',
            'PEDIATRIE',
            'SERVICE PLURIDISCIPLINAIRE',
            'URGENCES',
            'RADIOLOGIE',
            'LABORATOIRE',
            'DIRECTION DES RESSOURCES HUMAINES',
            'DIRECTION',
            'SECRETARIAT',
            'GASTRO-ENTEROLOGIE',
            'SERVICE DES SOINS INFIRMIERS',
            'SERVICE GENERAL',
        ];
    }

    public static function typesContrat(): array
    {
        return ['CDI', 'CDD', 'Stagiaire', 'Prestataire', 'Vacataire', 'Autre'];
    }

    public static function situationsMatrimoniales(): array
    {
        return ['Célibataire', 'Marié(e)', 'Divorcé(e)', 'Veuf/Veuve'];
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    public function getNomCompletAttribute(): string
    {
        return $this->prenoms . ' ' . strtoupper($this->nom);
    }

    public function getInitialesAttribute(): string
    {
        return strtoupper(substr($this->prenoms, 0, 1) . substr($this->nom, 0, 1));
    }

    public function getStatutLabelAttribute(): string
    {
        return match($this->statut) {
            'actif'    => 'Actif',
            'inactif'  => 'Inactif',
            'en_conge' => 'En congé',
            'retraite' => 'Retraité',
            default    => $this->statut,
        };
    }

    public function getPhotoUrlAttribute(): ?string
{
    if ($this->photo_path && Storage::disk('public')->exists($this->photo_path)) {
        return Storage::url($this->photo_path);
    }
    return null;
}

    public function isActif(): bool { return $this->statut === 'actif'; }

    // ── Relations ─────────────────────────────────────────────────────────────

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
