@extends('layouts.app')
@section('title', $personnel->nom_complet)
@section('page-title', 'Fiche personnel')

@section('content')

<div class="page-header-bar">
    <a href="{{ route('personnel.index') }}" class="btn-back">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
        Retour à la liste
    </a>
    <div class="header-actions">
        <a href="{{ route('personnel.edit', $personnel) }}" class="btn-ghost btn-sm">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            Modifier
        </a>
        <button class="btn-primary btn-sm" onclick="document.getElementById('modal-affecter').style.display='flex'">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/></svg>
            Affecter
        </button>
    </div>
</div>

{{-- ── En-tête fiche ──────────────────────────────────────────────────────── --}}
<div class="fiche-hero">
    <div class="fiche-avatar">{{ $personnel->initiales }}</div>
    <div class="fiche-hero-info">
        <h2>{{ $personnel->nom_complet }}</h2>
        <div class="fiche-tags">
            <span class="statut-pill statut-{{ $personnel->statut }}">{{ $personnel->statut_label }}</span>
            @if($personnel->corporation)
                <span class="fiche-tag tag-blue">{{ $personnel->corporation }}</span>
            @endif
            @if($personnel->service)
                <span class="fiche-tag tag-green">{{ $personnel->service }}</span>
            @endif
            @if($personnel->type_contrat)
                <span class="contrat-tag contrat-{{ strtolower($personnel->type_contrat) }}">{{ $personnel->type_contrat }}</span>
            @endif
        </div>
    </div>
    <div class="fiche-hero-meta">
        <div class="fiche-meta-item">
            <span class="meta-label">Enregistré le</span>
            <span class="meta-val">{{ $personnel->created_at->format('d/m/Y') }}</span>
        </div>
        @if($personnel->createdBy)
        <div class="fiche-meta-item">
            <span class="meta-label">Par</span>
            <span class="meta-val">{{ $personnel->createdBy->prenoms }}</span>
        </div>
        @endif
    </div>
</div>

{{-- ── Corps fiche (2 colonnes) ───────────────────────────────────────────── --}}
<div class="fiche-grid">

    {{-- Colonne gauche --}}
    <div class="fiche-col">

        <div class="dash-card">
            <div class="card-header"><h3>Identité</h3></div>
            <dl class="detail-list">
                <div class="dl-row"><dt>Date de naissance</dt><dd>{{ $personnel->date_naissance ? $personnel->date_naissance->format('d/m/Y') : '—' }}</dd></div>
                <div class="dl-row"><dt>Lieu de naissance</dt><dd>{{ $personnel->lieu_naissance ?: '—' }}</dd></div>
                <div class="dl-row"><dt>Sexe</dt><dd>{{ $personnel->sexe === 'M' ? 'Masculin' : 'Féminin' }}</dd></div>
                <div class="dl-row"><dt>Situation matrimoniale</dt><dd>{{ $personnel->situation_matrimoniale ?: '—' }}</dd></div>
                <div class="dl-row"><dt>Téléphone</dt><dd>{{ $personnel->telephone ?: '—' }}</dd></div>
                <div class="dl-row"><dt>Diplôme</dt><dd>{{ $personnel->diplome ?: '—' }}</dd></div>
                <div class="dl-row">
                    <dt>Clientèle privée</dt>
                    <dd>
                        @if($personnel->autorisation_clientele_privee)
                            <span class="badge badge-green">Autorisé</span>
                        @else
                            <span class="badge badge-warn">Non autorisé</span>
                        @endif
                    </dd>
                </div>
            </dl>
        </div>

        <div class="dash-card">
            <div class="card-header"><h3>Contact d'urgence</h3></div>
            @if($personnel->contact_urgence_nom || $personnel->contact_urgence_telephone)
            <dl class="detail-list">
                <div class="dl-row"><dt>Nom</dt><dd>{{ $personnel->contact_urgence_nom ?: '—' }}</dd></div>
                <div class="dl-row"><dt>Téléphone</dt><dd>{{ $personnel->contact_urgence_telephone ?: '—' }}</dd></div>
            </dl>
            @else
                <p class="empty-inline">Aucun contact renseigné.</p>
            @endif
        </div>

    </div>

    {{-- Colonne droite --}}
    <div class="fiche-col">

        <div class="dash-card">
            <div class="card-header"><h3>Contrat & Carrière</h3></div>
            <dl class="detail-list">
                <div class="dl-row"><dt>Catégorie / Échelon</dt><dd>{{ $personnel->categorie_echelon ?: '—' }}</dd></div>
                <div class="dl-row"><dt>N° CNSS</dt><dd class="mono-text">{{ $personnel->numero_cnss ?: '—' }}</dd></div>
                <div class="dl-row"><dt>Embauche dans le Centre</dt><dd>{{ $personnel->date_embauche_centre ? $personnel->date_embauche_centre->format('d/m/Y') : '—' }}</dd></div>
                <div class="dl-row"><dt>Embauche dans les ISD</dt><dd>{{ $personnel->date_embauche_isd ? $personnel->date_embauche_isd->format('d/m/Y') : '—' }}</dd></div>
                <div class="dl-row"><dt>Fin de contrat</dt><dd>{{ $personnel->date_fin_contrat ? $personnel->date_fin_contrat->format('d/m/Y') : '—' }}</dd></div>
                <div class="dl-row"><dt>Départ à la retraite</dt><dd>{{ $personnel->date_depart_retraite ? $personnel->date_depart_retraite->format('d/m/Y') : '—' }}</dd></div>
                @if($personnel->date_debauchage)
                <div class="dl-row"><dt>Date de débauchage</dt><dd>{{ $personnel->date_debauchage->format('d/m/Y') }}</dd></div>
                <div class="dl-row"><dt>Motif de débauchage</dt><dd>{{ $personnel->motif_debauchage ?: '—' }}</dd></div>
                @endif
            </dl>
        </div>

        @if($personnel->conge_annee || $personnel->conge_jours)
        <div class="dash-card">
            <div class="card-header"><h3>Congé</h3></div>
            <dl class="detail-list">
                <div class="dl-row"><dt>Année</dt><dd>{{ $personnel->conge_annee ?: '—' }}</dd></div>
                <div class="dl-row"><dt>Nombre de jours</dt><dd>{{ $personnel->conge_jours ?? '—' }} j</dd></div>
            </dl>
        </div>
        @endif

        @if($personnel->affectation)
        <div class="dash-card">
            <div class="card-header"><h3>Affectation</h3></div>
            <dl class="detail-list">
                <div class="dl-row"><dt>Structure</dt><dd>{{ $personnel->affectation }}</dd></div>
                @if($personnel->date_affectation)
                <div class="dl-row"><dt>Date</dt><dd>{{ $personnel->date_affectation->format('d/m/Y') }}</dd></div>
                @endif
            </dl>
        </div>
        @endif

    </div>
</div>

{{-- Zone danger --}}
<div class="dash-card danger-zone" style="max-width:600px; margin-top:8px;">
    <div class="card-header"><h3 class="danger-title">Zone de danger</h3></div>
    <p class="danger-text">Archiver cet agent le masquera de toutes les listes. L'action est réversible.</p>
    <form method="POST" action="{{ route('personnel.destroy', $personnel) }}"
          onsubmit="return confirm('Archiver {{ $personnel->nom_complet }} ?')">
        @csrf @method('DELETE')
        <button type="submit" class="btn-danger">Archiver cet agent</button>
    </form>
</div>

{{-- ── Modal Affectation ──────────────────────────────────────────────────── --}}
<div id="modal-affecter" class="modal-backdrop" style="display:none" onclick="if(event.target===this)this.style.display='none'">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Affecter {{ $personnel->prenoms }} {{ strtoupper($personnel->nom) }}</h3>
            <button type="button" class="modal-close" onclick="document.getElementById('modal-affecter').style.display='none'">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <form method="POST" action="{{ route('personnel.affecter', $personnel) }}">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label>Structure / Lieu d'affectation <span class="req">*</span></label>
                    <div class="input-wrapper">
                        <input type="text" name="affectation"
                               value="{{ $personnel->affectation }}"
                               placeholder="Ex: Centre hospitalier de Porto-Novo" required>
                    </div>
                </div>
                <div class="form-row-2col">
                    <div class="form-group">
                        <label>Nouveau service</label>
                        <div class="input-wrapper select-wrapper">
                            <select name="service">
                                <option value="">Inchangé</option>
                                @foreach(\App\Models\Personnel::services() as $s)
                                    <option value="{{ $s }}" {{ $personnel->service === $s ? 'selected' : '' }}>{{ $s }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Date d'affectation</label>
                        <div class="input-wrapper">
                            <input type="date" name="date_affectation"
                                   value="{{ $personnel->date_affectation ? $personnel->date_affectation->format('Y-m-d') : date('Y-m-d') }}">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-ghost" onclick="document.getElementById('modal-affecter').style.display='none'">Annuler</button>
                <button type="submit" class="btn-primary">Enregistrer l'affectation</button>
            </div>
        </form>
    </div>
</div>

@endsection
