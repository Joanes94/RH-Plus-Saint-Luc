{{-- resources/views/evaluations/document.blade.php --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fiche d'évaluation - {{ $stagiaire->nom_complet }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html { font-size: 13px; -webkit-font-smoothing: antialiased; }
        body { font-family: 'Times New Roman', Times, serif; background: #f5f4f0; color: #000; }

        .doc-page {
            width: 210mm; min-height: 297mm; margin: 20px auto;
            background: white; position: relative;
            padding: 12mm 18mm 16mm;
            box-shadow: 0 4px 24px rgba(0,0,0,0.12);
        }

        /* En-tête */
        .letterhead { position: relative; text-align: center; padding-bottom: 8px; border-bottom: 2.5px solid #000; margin-bottom: 18px; min-height: 90px; }
        .letterhead-logo-left  { position: absolute; left: -4px; top: -6px; width: 70px; height: auto; }
        .lh-line1 { font-size: 1.05rem; font-weight: 700; letter-spacing: .02em; margin-bottom: 1px; }
        .lh-line2 { font-size: .95rem; font-weight: 400; margin-bottom: 3px; }
        .lh-line3 { font-size: 1.05rem; font-weight: 700; text-transform: uppercase; margin-bottom: 0; }
        .lh-line4 { font-size: 1rem; font-weight: 700; margin-bottom: 4px; }
        .lh-line5 { font-size: .82rem; line-height: 1.5; padding: 0 75px; }

        .doc-title {
            text-align: center;
            font-size: 1.1rem;
            font-weight: 700;
            text-transform: uppercase;
            text-decoration: underline;
            margin: 20px 0 30px;
            letter-spacing: .04em;
        }

        .doc-body { font-size: .98rem; line-height: 2.2; text-align: justify; }
        .doc-body .field-label {
            font-weight: 700;
            display: block;
            margin-top: 16px;
            margin-bottom: 4px;
            font-size: 1rem;
        }
        .doc-body .field-value {
            padding-left: 30px;
            min-height: 60px;
            border-bottom: 1px dashed #ccc;
            margin-bottom: 8px;
            white-space: pre-wrap;
        }
        .doc-body .field-value:last-child {
            border-bottom: none;
        }

        .signature-block {
            margin-top: 40px;
            text-align: right;
            padding-right: 40px;
        }
        .sig-lieu { font-style: italic; font-size: 11px; margin-bottom: 6px; }
        .sig-titre { font-weight: 700; font-size: 11px; }
        .sig-image-wrap { text-align: center; margin: 0; line-height: 0; }
        .sig-image { height: 130px; width: auto; max-width: 300px; object-fit: contain; display: block; margin: 0 auto; }
        .sig-line { height: 80px; width: 240px; border-bottom: 1.5px solid #000; margin: 0 0 0 auto; }
        .sig-name { font-weight: 700; text-align: center; margin-top: 0; text-decoration: underline; font-size: 1rem; }

        .doc-footer {
            position: absolute; bottom: 10mm; left: 18mm; right: 18mm;
            border-top: 1.5px solid #000; padding-top: 6px;
            text-align: center; font-size: .72rem; font-weight: 700;
        }
        .doc-footer .ft-line2 { display: flex; justify-content: center; gap: 30px; margin-top: 2px; }

        .no-print { margin: 20px auto; max-width: 210mm; display: flex; gap: 10px; justify-content: center; }
        .btn-print { padding: 10px 24px; background: #1a5c45; color: white; border: none; border-radius: 8px; font-size: .875rem; font-weight: 600; cursor: pointer; font-family: 'DM Sans', Arial, sans-serif; }
        .btn-close { padding: 10px 20px; background: #e8e6e0; color: #1a1916; border: none; border-radius: 8px; font-size: .875rem; cursor: pointer; font-family: 'DM Sans', Arial, sans-serif; }

        @media print {
            body { background: white; }
            .doc-page { margin: 0; box-shadow: none; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="no-print">
    <button class="btn-print" onclick="window.print()">Imprimer / Sauvegarder en PDF</button>
    <button class="btn-close" onclick="window.close()">Fermer</button>
</div>

<div class="doc-page">

    {{-- En-tête --}}
    <div class="letterhead">
        <img src="{{ $logo_b64 ?? '' }}" alt="Logo" class="letterhead-logo-left">
        <div class="lh-line1">ARCHIDIOCESE DE COTONOU</div>
        <div class="lh-line2">DIRECTION DIOCESAINE DE LA SANTE</div>
        <div class="lh-line3">CENTRE DE SANTE A VOCATION HUMANITAIRE SAINT LUC</div>
        <div class="lh-line4">C.S.V.H (ex : Hôpital Saint LUC)</div>
        <div class="lh-line5">
            Qtier Missèkplé Ste Rita - 01 BP 3603 Tél : 66 43 44 78 – 90 07 49 67 /
            Email : hopitalsaintluc@gmail.com / Cotonou – BENIN
        </div>
    </div>

    {{-- Titre --}}
    <div class="doc-title">FICHE D'ÉVALUATION INDIVIDUELLE DES STAGIAIRES</div>

    {{-- Corps --}}
    <div class="doc-body">

        {{-- Identité --}}
        <p style="font-weight:700;font-size:1rem;margin-bottom:10px;">
            NOM : ................................................... &nbsp;&nbsp;&nbsp; <span style="font-weight:400;">{{ strtoupper($stagiaire->nom) }}</span>
        </p>
        <p style="font-weight:700;font-size:1rem;margin-bottom:10px;">
            PRENOMS : ................................................. &nbsp;&nbsp;&nbsp; <span style="font-weight:400;">{{ $stagiaire->prenoms }}</span>
        </p>
        <p style="font-weight:700;font-size:1rem;margin-bottom:20px;">
            SERVICE : ................................................... &nbsp;&nbsp;&nbsp; <span style="font-weight:400;">{{ $stagiaire->service ?: '_________________' }}</span>
        </p>

        {{-- I. Qualités --}}
        <div class="field-label">I. QUALITES</div>
        <div class="field-value">{{ $evaluation->qualites ?: '_____________________________________________' }}</div>

        {{-- II. Défauts --}}
        <div class="field-label">II. DEFAUTS</div>
        <div class="field-value">{{ $evaluation->defauts ?: '_____________________________________________' }}</div>

        {{-- III. Maîtrise de la pratique --}}
        <div class="field-label">III. MAITRISE DE LA PRATIQUE</div>
        <div class="field-value">{{ $evaluation->maitrise_pratique ?: '_____________________________________________' }}</div>

        {{-- IV. Appréciation personnelle --}}
        <div class="field-label">IV. APPRECIATION PERSONNELLE</div>
        <div class="field-value">{{ $evaluation->appreciation_personnelle ?: '_____________________________________________' }}</div>
    </div>

    {{-- Date et signature --}}
    <div style="margin-top: 30px;">
        <p style="font-size:1rem;margin-bottom:20px;">
            Cotonou le <span style="text-decoration:underline;">{{ $date_doc }}</span>
        </p>
    </div>

    <div class="signature-block">
        <div style="font-weight:700;text-align:center;margin-bottom:10px;">[Nom, Prénoms et Signature du chef service]</div>
        @if($signature_url)
            <div class="sig-image-wrap">
                <img src="{{ $signature_url }}" alt="Signature" class="sig-image">
            </div>
        @else
            <div class="sig-line"></div>
        @endif
        <div class="sig-name">{{ $drh_nom }}</div>
        <div style="font-size:0.9rem;text-align:center;margin-top:2px;">{{ $drh_titre }}</div>
    </div>

    {{-- Pied de page --}}
    <div class="doc-footer">
        <div>NOUVELLE AUTORISATION MINISTERIELLE N°071/MS/DC/SGM/CJ/DNSP/SRS/SA/063SGG20 DU 02/07/2020</div>
        <div class="ft-line2">
            <span>N°INSAE : 2988511276715</span>
            <span>N° IFU 3200800472415</span>
        </div>
    </div>

</div>
</body>
</html>