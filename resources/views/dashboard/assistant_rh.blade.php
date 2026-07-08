@extends('layouts.app')
@section('title', 'Tableau de bord')
@section('page-title', 'Bonjour, {{ $user->prenoms }} 👋')

@section('content')

{{-- KPIs --}}
<div class="kpi-grid mb-24">

    {{-- Agents actifs --}}
    <div class="kpi-card kpi-blue">
        <div class="kpi-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
        </div>

        <div class="kpi-body">
            <div class="kpi-value">{{ $stats['actifs'] }}</div>
            <div class="kpi-label">Agents actifs</div>

            <div class="kpi-hf">
                <span class="kpi-hf-h">
                    👨 {{ $stats['hommes'] }} H
                </span>

                <span class="kpi-hf-sep">•</span>

                <span class="kpi-hf-f">
                    👩 {{ $stats['femmes'] }} F
                </span>
            </div>
        </div>

        <div class="kpi-trend up">
            {{ $stats['total'] }} agents au total
        </div>
    </div>

    {{-- En attente --}}
    <div class="kpi-card kpi-amber">
        <div class="kpi-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <polyline points="12 6 12 12 16 14"/>
            </svg>
        </div>

        <div class="kpi-body">
            <div class="kpi-value">{{ $totalEnAttente }}</div>
            <div class="kpi-label">En attente du DRH</div>
        </div>

        @if($totalEnAttente > 0)
            <div class="kpi-trend warn">
                À traiter par le DRH
            </div>
        @else
            <div class="kpi-trend up">
                Aucune demande en attente
            </div>
        @endif
    </div>

    {{-- Congés --}}
    <div class="kpi-card kpi-purple">
        <div class="kpi-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="4" width="18" height="18" rx="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/>
                <line x1="8" y1="2" x2="8" y2="6"/>
                <line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
        </div>

        <div class="kpi-body">
            <div class="kpi-value">{{ $stats['conges'] }}</div>
            <div class="kpi-label">Agents en congé</div>
        </div>

        <div class="kpi-trend">
            sur {{ $stats['actifs'] }} actifs
        </div>
    </div>

    {{-- Stagiaires --}}
    <div class="kpi-card kpi-green">
        <div class="kpi-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
                <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
            </svg>
        </div>

        <div class="kpi-body">
            <div class="kpi-value">{{ $stats['totalStagiaires'] }}</div>
            <div class="kpi-label">Stagiaires en cours</div>
        </div>

        <div class="kpi-trend up">
            En activité
        </div>
    </div>

</div>

<div class="dash-two-col">

    {{-- Congés soumis --}}
    <div class="dash-card">
        <div class="card-header">
            <h3>Congés soumis au DRH</h3>
            <span class="badge badge-warn">{{ $congesPendants->count() }}</span>
        </div>
        @if($congesPendants->isEmpty())
            <p class="empty-inline">Aucun congé en attente.</p>
        @else
        <div class="attente-list">
            @foreach($congesPendants as $c)
            <a href="{{ route('conges.show', $c) }}" class="attente-item">
                <div class="agent-avatar av-sm">{{ $c->personnel->initiales }}</div>
                <div class="attente-info">
                    <span class="attente-name">{{ $c->personnel->nom_complet }}</span>
                    <span class="attente-meta">{{ $c->getTypeCongeLabel() }} · {{ $c->nb_jours_demandes }}j · du {{ $c->date_debut->format('d/m/Y') }}</span>
                </div>
                <span class="status-badge status-warn">En attente</span>
            </a>
            @endforeach
        </div>
        @endif
        <div style="margin-top:12px">
            <a href="{{ route('conges.index') }}" class="btn-ghost btn-sm">Voir tous les congés</a>
        </div>
    </div>

    <div class="dash-col-right">

        {{-- Absences soumises --}}
        <div class="dash-card">
            <div class="card-header">
                <h3>Absences soumises au DRH</h3>
                <span class="badge badge-warn">{{ $absencesPendants->count() }}</span>
            </div>
            @if($absencesPendants->isEmpty())
                <p class="empty-inline">Aucune absence en attente.</p>
            @else
            <div class="attente-list">
                @foreach($absencesPendants as $a)
                <a href="{{ route('absences.show', $a) }}" class="attente-item">
                    <div class="agent-avatar av-sm">{{ $a->personnel->initiales }}</div>
                    <div class="attente-info">
                        <span class="attente-name">{{ $a->personnel->nom_complet }}</span>
                        <span class="attente-meta">{{ $a->getTypeLabel() }} · {{ $a->nb_jours }}j</span>
                    </div>
                    <span class="status-badge status-warn">En attente</span>
                </a>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Demandes soumises --}}
        <div class="dash-card">
            <div class="card-header">
                <h3>Demandes soumises au DRH</h3>
                <span class="badge badge-warn">{{ $demandesPendants->count() }}</span>
            </div>
            @if($demandesPendants->isEmpty())
                <p class="empty-inline">Aucune demande en attente.</p>
            @else
            <div class="attente-list">
                @foreach($demandesPendants as $d)
                <a href="{{ route('demandes.show', $d) }}" class="attente-item">
                    <div class="agent-avatar av-sm">{{ $d->personnel->initiales }}</div>
                    <div class="attente-info">
                        <span class="attente-name">{{ $d->personnel->nom_complet }}</span>
                        <span class="attente-meta">{{ $d->type_label }}</span>
                    </div>
                    <span class="status-badge status-warn">En attente</span>
                </a>
                @endforeach
            </div>
            @endif
        </div>

    </div>
</div>

{{-- Accès rapides --}}
<div class="dash-card" style="margin-top:0">
    <div class="card-header"><h3>Accès rapides</h3></div>
    <div class="quick-links">
        <a href="{{ route('personnel.index') }}" class="ql-item">
            <div class="ql-icon ql-blue">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
            </div>
            <span>Personnel</span>
        </a>
        <a href="{{ route('stagiaires.index') }}" class="ql-item">
            <div class="ql-icon ql-green">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
            </div>
            <span>Stagiaires</span>
        </a>
        <a href="{{ route('conges.index') }}" class="ql-item">
            <div class="ql-icon ql-amber">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </div>
            <span>Congés</span>
        </a>
        <a href="{{ route('absences.index') }}" class="ql-item">
            <div class="ql-icon ql-purple">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <span>Absences</span>
        </a>
        <a href="{{ route('demandes.create') }}" class="ql-item">
            <div class="ql-icon ql-teal">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            </div>
            <span>Nouvelle demande</span>
        </a>
        <a href="{{ route('rapports.personnel') }}" class="ql-item">
            <div class="ql-icon" style="background:var(--col-red-lt);color:var(--col-red)">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
            </div>
            <span>Rapports PDF</span>
        </a>
    </div>
</div>

@endsection
