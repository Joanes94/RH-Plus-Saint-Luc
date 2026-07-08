{{-- SECTION 1 : IDENTITÉ --}}
<div class="form-section">
    <div class="form-section-header">
        <div class="section-icon section-blue">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        </div>
        <h3>Identité</h3>
    </div>
    <div class="form-grid">
        <div class="form-group fg-2">
            <label>Nom <span class="req">*</span></label>
            <div class="input-wrapper">
                <input type="text" name="nom" value="{{ old('nom', $personnel->nom ?? '') }}" placeholder="DUPONT" class="{{ $errors->has('nom') ? 'is-invalid' : '' }}">
            </div>
            @error('nom') <span class="field-error">{{ $message }}</span> @enderror
        </div>
        <div class="form-group fg-3">
            <label>Prénoms <span class="req">*</span></label>
            <div class="input-wrapper">
                <input type="text" name="prenoms" value="{{ old('prenoms', $personnel->prenoms ?? '') }}" placeholder="Jean-Paul" class="{{ $errors->has('prenoms') ? 'is-invalid' : '' }}">
            </div>
            @error('prenoms') <span class="field-error">{{ $message }}</span> @enderror
        </div>
        <div class="form-group fg-3">
            <label>Email <span style="font-size:.72rem;color:var(--col-text-3)">(identifiant unique)</span></label>
            <div class="input-wrapper">
                <input type="email" name="email" value="{{ old('email', $personnel->email ?? '') }}" placeholder="agent@csvh.bj" class="{{ $errors->has('email') ? 'is-invalid' : '' }}">
            </div>
            @error('email') <span class="field-error">{{ $message }}</span> @enderror
        </div>

        <div class="form-group fg-2">
            <label>Date de naissance</label>
            <div class="input-wrapper">
                <input type="date" name="date_naissance" value="{{ old('date_naissance', isset($personnel->date_naissance) ? $personnel->date_naissance->format('Y-m-d') : '') }}">
            </div>
        </div>
        <div class="form-group fg-3">
            <label>Lieu de naissance</label>
            <div class="input-wrapper">
                <input type="text" name="lieu_naissance" value="{{ old('lieu_naissance', $personnel->lieu_naissance ?? '') }}" placeholder="Cotonou">
            </div>
        </div>
        <div class="form-group fg-2">
            <label>Sexe <span class="req">*</span></label>
            <div class="input-wrapper select-wrapper">
                <select name="sexe" class="{{ $errors->has('sexe') ? 'is-invalid' : '' }}">
                    <option value="">—</option>
                    <option value="M" {{ old('sexe', $personnel->sexe ?? '') === 'M' ? 'selected' : '' }}>Masculin</option>
                    <option value="F" {{ old('sexe', $personnel->sexe ?? '') === 'F' ? 'selected' : '' }}>Féminin</option>
                </select>
            </div>
            @error('sexe') <span class="field-error">{{ $message }}</span> @enderror
        </div>
        <div class="form-group fg-2">
            <label>Situation matrimoniale</label>
            <div class="input-wrapper select-wrapper">
                <select name="situation_matrimoniale">
                    <option value="">—</option>
                    @foreach($situations as $s)
                        <option value="{{ $s }}" {{ old('situation_matrimoniale', $personnel->situation_matrimoniale ?? '') === $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="form-group fg-3">
            <label>Téléphone</label>
            <div class="input-wrapper">
                <input type="tel" name="telephone" value="{{ old('telephone', $personnel->telephone ?? '') }}" placeholder="+229 97 00 00 00">
            </div>
        </div>
        <div class="form-group fg-3">
            <label>Diplôme</label>
            <div class="input-wrapper">
                <input type="text" name="diplome" value="{{ old('diplome', $personnel->diplome ?? '') }}" placeholder="Ex: BEPC, BAC, Licence...">
            </div>
        </div>

        {{-- Photo --}}
        <div class="form-group fg-2">
            <label>Photo</label>
            @if(isset($personnel) && $personnel->photo_url)
            <div style="margin-bottom:8px">
                <img src="{{ $personnel->photo_url }}" alt="Photo"
                     style="width:56px;height:56px;object-fit:cover;border-radius:50%;border:2px solid var(--col-border-lg)">
            </div>
            @endif
            <div class="input-wrapper">
                <input type="file" name="photo" accept="image/jpeg,image/png" class="file-input-std">
            </div>
            @error('photo') <span class="field-error">{{ $message }}</span> @enderror
        </div>

        <div class="form-group fg-4 fg-align-center">
            <label class="checkbox-switch-label">
                <span>Autorisation d'exercice en clientèle privée</span>
                <label class="switch">
                    <input type="hidden" name="autorisation_clientele_privee" value="0">
                    <input type="checkbox" name="autorisation_clientele_privee" value="1"
                           {{ old('autorisation_clientele_privee', $personnel->autorisation_clientele_privee ?? false) ? 'checked' : '' }}>
                    <span class="switch-slider"></span>
                </label>
            </label>
        </div>
    </div>
</div>

{{-- SECTION 2 : POSTE --}}
<div class="form-section">
    <div class="form-section-header">
        <div class="section-icon section-green">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
        </div>
        <h3>Poste & Affectation</h3>
    </div>
    <div class="form-grid">
        <div class="form-group fg-3">
            <label>Corporation / Titre-Fonction</label>
            <div class="input-wrapper select-wrapper">
                <select name="corporation">
                    <option value="">— Sélectionner —</option>
                    @foreach($corporations as $c)
                        <option value="{{ $c }}" {{ old('corporation', $personnel->corporation ?? '') === $c ? 'selected' : '' }}>{{ $c }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="form-group fg-3">
            <label>Service</label>
            <div class="input-wrapper select-wrapper">
                <select name="service">
                    <option value="">— Sélectionner —</option>
                    @foreach($services as $s)
                        <option value="{{ $s }}" {{ old('service', $personnel->service ?? '') === $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="form-group fg-2">
            <label>Type de contrat</label>
            <div class="input-wrapper select-wrapper">
                <select name="type_contrat">
                    <option value="">—</option>
                    @foreach($contrats as $c)
                        <option value="{{ $c }}" {{ old('type_contrat', $personnel->type_contrat ?? '') === $c ? 'selected' : '' }}>{{ $c }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="form-group fg-2">
            <label>Catégorie / Échelon</label>
            <div class="input-wrapper">
                <input type="text" name="categorie_echelon" value="{{ old('categorie_echelon', $personnel->categorie_echelon ?? '') }}" placeholder="Ex: C2-E1">
            </div>
        </div>
        <div class="form-group fg-2">
            <label>Statut</label>
            <div class="input-wrapper select-wrapper">
                <select name="statut">
                    <option value="actif"    {{ old('statut', $personnel->statut ?? 'actif') === 'actif'    ? 'selected' : '' }}>Actif</option>
                    <option value="inactif"  {{ old('statut', $personnel->statut ?? '') === 'inactif'  ? 'selected' : '' }}>Inactif</option>
                    <option value="en_conge" {{ old('statut', $personnel->statut ?? '') === 'en_conge' ? 'selected' : '' }}>En congé</option>
                    <option value="retraite" {{ old('statut', $personnel->statut ?? '') === 'retraite' ? 'selected' : '' }}>Retraité</option>
                </select>
            </div>
        </div>
    </div>
</div>

{{-- SECTION 3 : DATES --}}
<div class="form-section">
    <div class="form-section-header">
        <div class="section-icon section-amber">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        </div>
        <h3>Dates & Contrat</h3>
    </div>
    <div class="form-grid">
        <div class="form-group fg-2"><label>Date d'embauche dans le Centre</label><div class="input-wrapper"><input type="date" name="date_embauche_centre" value="{{ old('date_embauche_centre', isset($personnel->date_embauche_centre) ? $personnel->date_embauche_centre->format('Y-m-d') : '') }}"></div></div>
        <div class="form-group fg-2"><label>Date d'embauche dans les ISD</label><div class="input-wrapper"><input type="date" name="date_embauche_isd" value="{{ old('date_embauche_isd', isset($personnel->date_embauche_isd) ? $personnel->date_embauche_isd->format('Y-m-d') : '') }}"></div></div>
        <div class="form-group fg-2"><label>N° CNSS</label><div class="input-wrapper"><input type="text" name="numero_cnss" value="{{ old('numero_cnss', $personnel->numero_cnss ?? '') }}" placeholder="CN123456" class="mono-input"></div></div>
        <div class="form-group fg-2"><label>Date de fin de contrat</label><div class="input-wrapper"><input type="date" name="date_fin_contrat" value="{{ old('date_fin_contrat', isset($personnel->date_fin_contrat) ? $personnel->date_fin_contrat->format('Y-m-d') : '') }}"></div></div>
        <div class="form-group fg-2"><label>Date de départ à la retraite</label><div class="input-wrapper"><input type="date" name="date_depart_retraite" value="{{ old('date_depart_retraite', isset($personnel->date_depart_retraite) ? $personnel->date_depart_retraite->format('Y-m-d') : '') }}"></div></div>
        <div class="form-group fg-2"><label>Date de débauchage</label><div class="input-wrapper"><input type="date" name="date_debauchage" value="{{ old('date_debauchage', isset($personnel->date_debauchage) ? $personnel->date_debauchage->format('Y-m-d') : '') }}"></div></div>
        <div class="form-group fg-5"><label>Motif de débauchage</label><div class="input-wrapper"><input type="text" name="motif_debauchage" value="{{ old('motif_debauchage', $personnel->motif_debauchage ?? '') }}" placeholder="Ex: Démission, Licenciement..."></div></div>
    </div>
</div>

{{-- SECTION 4 : CONGÉ --}}
<div class="form-section">
    <div class="form-section-header">
        <div class="section-icon section-purple">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
        </div>
        <h3>Congé</h3>
    </div>
    <div class="form-grid">
        <div class="form-group fg-2"><label>Année de congé</label><div class="input-wrapper"><input type="text" name="conge_annee" value="{{ old('conge_annee', $personnel->conge_annee ?? '') }}" placeholder="{{ date('Y') }}" maxlength="10"></div></div>
        <div class="form-group fg-2"><label>Nombre de jours</label><div class="input-wrapper"><input type="number" name="conge_jours" min="0" max="365" value="{{ old('conge_jours', $personnel->conge_jours ?? '') }}" placeholder="30"></div></div>
    </div>
</div>

{{-- SECTION 5 : AFFECTATION --}}
<div class="form-section">
    <div class="form-section-header">
        <div class="section-icon section-teal">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/></svg>
        </div>
        <h3>Affectation</h3>
    </div>
    <div class="form-grid">
        <div class="form-group fg-4"><label>Lieu / Structure d'affectation</label><div class="input-wrapper"><input type="text" name="affectation" value="{{ old('affectation', $personnel->affectation ?? '') }}" placeholder="Ex: Centre hospitalier de Cotonou..."></div></div>
        <div class="form-group fg-2"><label>Date d'affectation</label><div class="input-wrapper"><input type="date" name="date_affectation" value="{{ old('date_affectation', isset($personnel->date_affectation) ? $personnel->date_affectation->format('Y-m-d') : '') }}"></div></div>
    </div>
</div>

{{-- SECTION 6 : CONTACT URGENCE --}}
<div class="form-section">
    <div class="form-section-header">
        <div class="section-icon section-red">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.7 12a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.61 1h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9a16 16 0 0 0 6.29 6.29l.13-.14a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
        </div>
        <h3>Contact en cas d'urgence</h3>
    </div>
    <div class="form-grid">
        <div class="form-group fg-3"><label>Nom et prénoms</label><div class="input-wrapper"><input type="text" name="contact_urgence_nom" value="{{ old('contact_urgence_nom', $personnel->contact_urgence_nom ?? '') }}" placeholder="Ex: Marie DUPONT"></div></div>
        <div class="form-group fg-2"><label>Téléphone</label><div class="input-wrapper"><input type="tel" name="contact_urgence_telephone" value="{{ old('contact_urgence_telephone', $personnel->contact_urgence_telephone ?? '') }}" placeholder="+229 97 00 00 00"></div></div>
    </div>
</div>
