<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Avancement extends Model
{
    protected $table = 'avancements';

    protected $fillable = [
        'personnel_id', 'contrat_id', 'type', 'date_effet',
        'ancienne_categorie', 'ancien_echelon', 'nouvelle_categorie', 'nouvel_echelon',
        'ancien_salaire', 'nouveau_salaire', 'coefficient_applique',
        'numero_reference', 'created_by',
    ];

    protected $casts = [
        'date_effet'   => 'date',
        'ancien_salaire'   => 'integer',
        'nouveau_salaire'  => 'integer',
        'coefficient_applique' => 'decimal:3',
    ];

    public function personnel()
    {
        return $this->belongsTo(Personnel::class);
    }

    public function contrat()
    {
        return $this->belongsTo(Contrat::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getTypeLabelAttribute(): string
    {
        return $this->type === 'bonification' ? 'Bonification (58 ans)' : 'Avancement d\'échelon';
    }
}
