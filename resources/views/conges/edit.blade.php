@extends('layouts.app')
@section('title', 'Modifier la demande de congé')
@section('page-title', 'Modifier la demande de congé')

@section('content')
<div class="page-header-bar">
    <a href="{{ route('conges.show', $conge) }}" class="btn-back">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
        Retour à la demande
    </a>
</div>

@if($errors->any())
<div class="alert alert-error">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/></svg>
    {{ $errors->count() }} erreur(s) à corriger.
</div>
@endif

<div class="conge-form-layout">
<div class="conge-form-main">
<form method="POST" action="{{ route('conges.update', $conge) }}" id="congeForm">
    @csrf @method('PUT')

    <div class="form-section">
        <div class="form-section-header">
            <div class="section-icon section-blue"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></div>
            <h3>Agent : {{ $conge->personnel->nom_complet }}</h3>
        </div>
        <p style="font-size:.83rem;color:var(--col-text-2)">
            {{ $conge->personnel->service ?: '—' }} · {{ $conge->personnel->corporation ?: '—' }}
        </p>
    </div>

    <div class="form-section">
        <div class="form-section-header">
            <div class="section-icon section-amber"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/></svg></div>
            <h3>Détails du congé</h3>
        </div>
        <div class="form-grid">
            <div class="form-group fg-3">
                <label>Type de congé <span class="req">*</span></label>
                <div class="input-wrapper select-wrapper">
                    <select name="type_conge" required>
                        <option value="administratif" {{ old('type_conge', $conge->type_conge) === 'administratif' ? 'selected' : '' }}>Congé administratif</option>
                        <option value="technique"     {{ old('type_conge', $conge->type_conge) === 'technique'     ? 'selected' : '' }}>Congé technique (Radiologie)</option>
                    </select>
                </div>
            </div>
            <div class="form-group fg-3">
                <label>Année de référence <span class="req">*</span></label>
                <div class="input-wrapper">
                    <input type="text" name="annee" id="anneeInput"
                           value="{{ old('annee', $conge->annee) }}" required maxlength="9">
                </div>
            </div>
            <div class="form-group fg-3">
                <label>Date de début <span class="req">*</span></label>
                <div class="input-wrapper">
                    <input type="date" name="date_debut" id="dateDebutInput"
                           value="{{ old('date_debut', $conge->date_debut->format('Y-m-d')) }}" required>
                </div>
            </div>
            <div class="form-group fg-3">
                <label>Nombre de jours (ouvrables) <span class="req">*</span></label>
                <div class="input-wrapper">
                    <input type="number" name="nb_jours_demandes" id="nbJoursInput"
                           value="{{ old('nb_jours_demandes', $conge->nb_jours_demandes) }}"
                           min="1" max="30" required>
                </div>
            </div>
        </div>

        <div id="calcResult" class="calc-result" style="margin-top:12px">
            <div class="cr-item">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/></svg>
                <span>Date de reprise : <strong id="crDateFin">{{ $conge->date_fin->isoFormat('dddd D MMMM YYYY') }}</strong></span>
            </div>
        </div>

        <div class="form-group" style="margin-top:14px">
            <label>Observations</label>
            <div class="input-wrapper">
                <textarea name="observations" rows="3"
                    style="width:100%;padding:10px 14px;background:var(--col-bg);border:1.5px solid var(--col-border-lg);border-radius:var(--radius);font-family:'DM Sans',sans-serif;font-size:.875rem;resize:vertical;outline:none;">{{ old('observations', $conge->observations) }}</textarea>
            </div>
        </div>
    </div>

    <div class="form-submit-bar">
        <a href="{{ route('conges.show', $conge) }}" class="btn-ghost">Annuler</a>
        <button type="submit" name="action" value="brouillon" class="btn-ghost">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v14a2 2 0 0 1-2 2z"/></svg>
            Sauvegarder
        </button>
        <button type="submit" name="action" value="soumettre" class="btn-primary">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
            Soumettre au DRH
        </button>
    </div>
</form>
</div>

<div class="conge-form-aside">
    <div class="aside-card">
        <h4>Règles de calcul</h4>
        <div class="rule-list">
            <div class="rule-row"><div class="rule-bullet"></div><p><strong>Base :</strong> 24 jours ouvrables</p></div>
            <div class="rule-row"><div class="rule-bullet rule-amber"></div><p>+2j après 20 ans, +4j après 25 ans, +6j après 30 ans (max 30j)</p></div>
            <div class="rule-row"><div class="rule-bullet rule-blue"></div><p>Jours ouvrables : lundi → vendredi, hors jours fériés</p></div>
        </div>
    </div>
</div>
</div>
@endsection

@push('scripts')
<script>
const dateDebutInp = document.getElementById('dateDebutInput');
const nbJoursInp   = document.getElementById('nbJoursInput');
const anneeInp     = document.getElementById('anneeInput');

function calculerDateFin() {
    const debut = dateDebutInp.value;
    const jours = nbJoursInp.value;
    if (!debut || !jours) return;
    const pid   = '{{ $conge->personnel_id }}';
    const annee = anneeInp.value;
    const url   = `{{ route('conges.calcul-date-fin') }}?date_debut=${debut}&nb_jours_demandes=${jours}&personnel_id=${pid}&annee=${annee}`;
    fetch(url).then(r => r.json()).then(d => {
        document.getElementById('crDateFin').textContent = d.date_fin_format;
        document.getElementById('calcResult').style.display = 'flex';
    }).catch(() => {});
}

dateDebutInp.addEventListener('change', calculerDateFin);
nbJoursInp.addEventListener('input', calculerDateFin);
</script>
@endpush
