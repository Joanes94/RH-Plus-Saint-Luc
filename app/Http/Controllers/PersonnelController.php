<?php

namespace App\Http\Controllers;

use App\Models\Personnel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PersonnelController extends Controller
{
    // ── Liste (exclut stagiaires et prestataires) ──────────────────────────────
    public function index(Request $request)
    {
        $query = Personnel::personnelPrincipal();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('nom', 'like', "%$s%")
                  ->orWhere('prenoms', 'like', "%$s%")
                  ->orWhere('email', 'like', "%$s%")
                  ->orWhere('numero_cnss', 'like', "%$s%")
                  ->orWhere('telephone', 'like', "%$s%");
            });
        }

        if ($request->filled('service'))     $query->where('service', $request->service);
        if ($request->filled('corporation')) $query->where('corporation', $request->corporation);
        if ($request->filled('statut'))      $query->where('statut', $request->statut);
        if ($request->filled('contrat'))     $query->where('type_contrat', $request->contrat);

        $personnels = $query->orderBy('nom')->paginate(20)->withQueryString();

        // Stats sur le personnel principal uniquement
        $base = Personnel::personnelPrincipal();
        return view('personnel.index', [
            'personnels'   => $personnels,
            'services'     => Personnel::services(),
            'corporations' => Personnel::corporations(),
            'filters'      => $request->only('search', 'service', 'corporation', 'statut', 'contrat'),
            'stats' => [
                'total'    => (clone $base)->count(),
                'actifs'   => (clone $base)->where('statut', 'actif')->count(),
                'conges'   => (clone $base)->where('statut', 'en_conge')->count(),
                'inactifs' => (clone $base)->where('statut', 'inactif')->count(),
                'hommes'   => (clone $base)->where('sexe', 'M')->count(),
                'femmes'   => (clone $base)->where('sexe', 'F')->count(),
            ],
        ]);
    }

    // ── Création ──────────────────────────────────────────────────────────────
    public function create()
    {
        return view('personnel.create', [
            'corporations' => Personnel::corporations(),
            'services'     => Personnel::services(),
            'contrats'     => Personnel::typesContrat(),
            'situations'   => Personnel::situationsMatrimoniales(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatePersonnel($request);
        $data['created_by'] = Auth::id();

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')
                ->store('photos/personnel', 'public');
        }

        Personnel::create($data);

        return redirect()->route('personnel.index')
            ->with('success', 'Personnel ajouté avec succès.');
    }

    // ── Détail ────────────────────────────────────────────────────────────────
    public function show(Personnel $personnel)
    {
        return view('personnel.show', compact('personnel'));
    }

    // ── Modification ──────────────────────────────────────────────────────────
    public function edit(Personnel $personnel)
    {
        return view('personnel.edit', [
            'personnel'    => $personnel,
            'corporations' => Personnel::corporations(),
            'services'     => Personnel::services(),
            'contrats'     => Personnel::typesContrat(),
            'situations'   => Personnel::situationsMatrimoniales(),
        ]);
    }

    public function update(Request $request, Personnel $personnel)
    {
        $data = $this->validatePersonnel($request, $personnel->id);

        if ($request->hasFile('photo')) {
            if ($personnel->photo_path) {
                Storage::disk('public')->delete($personnel->photo_path);
            }
            $data['photo_path'] = $request->file('photo')
                ->store('photos/personnel', 'public');
        }

        $personnel->update($data);

        return redirect()->route('personnel.show', $personnel)
            ->with('success', 'Fiche mise à jour.');
    }

    // ── Suppression ───────────────────────────────────────────────────────────
    public function destroy(Personnel $personnel)
    {
        $personnel->delete();
        return redirect()->route('personnel.index')
            ->with('success', 'Personnel archivé.');
    }

    // ── Affectation ───────────────────────────────────────────────────────────
    public function affecter(Request $request, Personnel $personnel)
    {
        $request->validate([
            'affectation'      => 'required|string|max:200',
            'service'          => 'nullable|string',
            'date_affectation' => 'nullable|date',
        ]);

        $personnel->update([
            'affectation'      => $request->affectation,
            'service'          => $request->service ?: $personnel->service,
            'date_affectation' => $request->date_affectation,
        ]);

        return redirect()->route('personnel.show', $personnel)
            ->with('success', 'Affectation enregistrée.');
    }

    // ── Import Excel ──────────────────────────────────────────────────────────
    public function importForm()
    {
        return view('personnel.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'fichier' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        $file = $request->file('fichier');
        $ext  = strtolower($file->getClientOriginalExtension());

        try {
            if ($ext === 'csv') {
                $rows = $this->parseCsv($file->getRealPath());
            } else {
                if (class_exists('\PhpOffice\PhpSpreadsheet\IOFactory')) {
                    $rows = $this->parseXlsx($file->getRealPath());
                } else {
                    return back()->with('error',
                        'PhpSpreadsheet non installé. Utilisez le format CSV.');
                }
            }

            $imported = 0;
            $errors   = [];

            foreach ($rows as $i => $row) {
                $line = $i + 2;
                if (empty(trim($row['nom'] ?? '')) && empty(trim($row['prenoms'] ?? ''))) continue;

                $validator = Validator::make($row, [
                    'nom'     => 'required|string',
                    'prenoms' => 'required|string',
                    'sexe'    => 'required|in:M,F',
                ]);

                if ($validator->fails()) {
                    $errors[] = "Ligne $line : " . implode(', ', $validator->errors()->all());
                    continue;
                }

                Personnel::create(array_merge(
                    $this->sanitizeImportRow($row),
                    ['created_by' => Auth::id()]
                ));
                $imported++;
            }

            $msg = "$imported personnel(s) importé(s).";
            if ($errors) $msg .= ' ' . count($errors) . ' ligne(s) ignorée(s).';

            return redirect()->route('personnel.index')->with('success', $msg);

        } catch (\Exception $e) {
            return back()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        $headers = [
            'nom', 'prenoms', 'email', 'date_naissance', 'lieu_naissance', 'sexe',
            'telephone', 'situation_matrimoniale', 'diplome',
            'autorisation_clientele_privee', 'corporation', 'service',
            'type_contrat', 'categorie_echelon', 'date_embauche_centre',
            'date_embauche_isd', 'numero_cnss', 'date_fin_contrat',
            'contact_urgence_nom', 'contact_urgence_telephone',
        ];

        $csv = implode(';', $headers) . "\n";
        $csv .= implode(';', [
            'GBÈDJI', 'Rodrigue', 'rodrigue@exemple.bj', '1990-05-15', 'Cotonou', 'M',
            '+229 97000000', 'Célibataire', 'BTS Informatique',
            '0', 'INFIRMIER', 'MEDECINE', 'CDI', 'C2-E1', '2020-01-15',
            '2019-06-01', 'CN123456', '', 'Akossiwa Gbèdji', '+229 96000000',
        ]) . "\n";

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="modele_import_personnel.csv"',
        ]);
    }

    // ── Helpers privés ────────────────────────────────────────────────────────

    private function validatePersonnel(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'nom'                          => 'required|string|max:100',
            'prenoms'                      => 'required|string|max:150',
            'email'                        => 'nullable|email|unique:personnels,email,' . $ignoreId,
            'date_naissance'               => 'nullable|date',
            'lieu_naissance'               => 'nullable|string|max:150',
            'sexe'                         => 'required|in:M,F',
            'telephone'                    => 'nullable|string|max:20',
            'situation_matrimoniale'       => 'nullable|in:Célibataire,Marié(e),Divorcé(e),Veuf/Veuve',
            'diplome'                      => 'nullable|string|max:200',
            'autorisation_clientele_privee'=> 'nullable|boolean',
            'corporation'                  => 'nullable|string|max:150',
            'service'                      => 'nullable|string|max:150',
            'type_contrat'                 => 'nullable|string|max:100',
            'categorie_echelon'            => 'nullable|string|max:100',
            'date_embauche_centre'         => 'nullable|date',
            'date_embauche_isd'            => 'nullable|date',
            'date_debauchage'              => 'nullable|date',
            'motif_debauchage'             => 'nullable|string|max:255',
            'numero_cnss'                  => 'nullable|string|max:50',
            'date_depart_retraite'         => 'nullable|date',
            'date_fin_contrat'             => 'nullable|date',
            'conge_annee'                  => 'nullable|string|max:10',
            'conge_jours'                  => 'nullable|integer|min:0',
            'contact_urgence_nom'          => 'nullable|string|max:150',
            'contact_urgence_telephone'    => 'nullable|string|max:20',
            'affectation'                  => 'nullable|string|max:200',
            'date_affectation'             => 'nullable|date',
            'statut'                       => 'nullable|in:actif,inactif,en_conge,retraite',
            'photo'                        => 'nullable|image|max:2048|mimes:jpg,jpeg,png',
        ]);
    }

    private function parseCsv(string $path): array
    {
        $rows    = [];
        $handle  = fopen($path, 'r');
        $headers = null;
        while (($line = fgetcsv($handle, 0, ';')) !== false) {
            if (!$headers) { $headers = array_map('trim', $line); continue; }
            if (count($line) < 2) continue;
            $rows[] = array_combine($headers, array_pad($line, count($headers), ''));
        }
        fclose($handle);
        return $rows;
    }

    private function parseXlsx(string $path): array
    {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
        $sheet       = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);
        $headers     = array_map('trim', array_shift($sheet));
        $rows        = [];
        foreach ($sheet as $line) {
            $rows[] = array_combine($headers, array_pad($line, count($headers), ''));
        }
        return $rows;
    }

    private function sanitizeImportRow(array $row): array
    {
        $dateFields = [
            'date_naissance', 'date_embauche_centre', 'date_embauche_isd',
            'date_debauchage', 'date_depart_retraite', 'date_fin_contrat', 'date_affectation',
        ];
        foreach ($dateFields as $field) {
            if (!empty($row[$field])) {
                try { $row[$field] = \Carbon\Carbon::parse($row[$field])->format('Y-m-d'); }
                catch (\Exception $e) { $row[$field] = null; }
            } else { $row[$field] = null; }
        }
        $row['autorisation_clientele_privee'] = in_array(
            strtolower(trim($row['autorisation_clientele_privee'] ?? '')),
            ['1', 'oui', 'yes', 'true']
        ) ? 1 : 0;
        $row['sexe'] = strtoupper(trim($row['sexe'] ?? 'M'));
        if (!in_array($row['sexe'], ['M', 'F'])) $row['sexe'] = 'M';
        return array_intersect_key($row, array_flip((new Personnel)->getFillable()));
    }
}
