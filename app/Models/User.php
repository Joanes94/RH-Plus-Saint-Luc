<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'nom',
        'prenoms',
        'sexe',
        'email',
        'telephone',
        'role',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getNomCompletAttribute(): string
    {
        return $this->prenoms . ' ' . strtoupper($this->nom);
    }

    public function isDRH(): bool
    {
        return $this->role === 'drh';
    }

    public function isAssistantRH(): bool
    {
        return $this->role === 'assistant_rh';
    }
}
