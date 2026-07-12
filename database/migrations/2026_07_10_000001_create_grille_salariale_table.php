<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grille_salariale', function (Blueprint $table) {
            $table->id();
            $table->string('categorie', 10);           // E1..E6, M1..M3, C1, C2
            $table->string('libelle', 30);              // 1ère, 2ème ... 11ème catégorie
            $table->unsignedTinyInteger('ordre');        // ordre d'affichage des catégories
            $table->unsignedTinyInteger('echelon');      // 1 à 11
            $table->unsignedSmallInteger('anciennete_mois'); // ancienneté requise pour cet échelon
            $table->decimal('coefficient', 5, 3);
            $table->unsignedInteger('salaire');          // salaire de base FCFA pour cette case
            $table->timestamps();

            $table->unique(['categorie', 'echelon']);
        });

        // ── Import des données de la grille salariale ISD ───────────────────
        // Ancienneté requise (en mois) et coefficient par échelon (identiques
        // pour toutes les catégories) :
        $echelons = [
            1  => ['mois' => 0,   'coef' => 1.000],
            2  => ['mois' => 24,  'coef' => 1.050],
            3  => ['mois' => 48,  'coef' => 1.100],
            4  => ['mois' => 72,  'coef' => 1.150],
            5  => ['mois' => 96,  'coef' => 1.300],
            6  => ['mois' => 120, 'coef' => 1.400],
            7  => ['mois' => 144, 'coef' => 1.500],
            8  => ['mois' => 168, 'coef' => 1.650],
            9  => ['mois' => 192, 'coef' => 1.750],
            10 => ['mois' => 216, 'coef' => 1.850],
            11 => ['mois' => 240, 'coef' => 2.200],
        ];

        // Catégories : [code, libellé, ordre, [salaire Ech1..Ech11]]
        $categories = [
            ['E1', '1ère catégorie',  1,  [52000,  54600,  57200,  59800,  67600,  72800,  78000,  85800,  91000,  96200,  114400]],
            ['E2', '2ème catégorie',  2,  [53500,  56175,  58850,  61525,  69550,  74900,  80250,  88275,  93625,  98975,  117700]],
            ['E3', '3ème catégorie',  3,  [56500,  59325,  62150,  64975,  73450,  79100,  84750,  93225,  98875,  104525, 124300]],
            ['E4', '4ème catégorie',  4,  [61868,  64961,  68055,  71148,  80428,  86615,  92802,  102082, 108269, 114456, 136110]],
            ['E5', '5ème catégorie',  5,  [65698,  68983,  72268,  75553,  85407,  91977,  98547,  108402, 114972, 121541, 144536]],
            ['E6', '6ème catégorie',  6,  [76415,  80236,  84057,  87877,  114240, 123028, 131816, 217496, 230678, 243859, 536491]],
            ['M1', '7ème catégorie',  7,  [92356,  96974,  101592, 106209, 138072, 148693, 159314, 262868, 278800, 294731, 648408]],
            ['M2', '8ème catégorie',  8,  [115645, 121427, 127210, 132992, 172889, 186188, 199488, 329155, 349103, 369052, 811915]],
            ['M3', '9ème catégorie',  9,  [127402, 133772, 140142, 146512, 190466, 205117, 219768, 362618, 384595, 406572, 894458]],
            ['C1', '10ème catégorie', 10, [137632, 144514, 151395, 158277, 205760, 221588, 237415, 391735, 415477, 439218, 966280]],
            ['C2', '11ème catégorie', 11, [160450, 168473, 176495, 184518, 239873, 258324, 276776, 456681, 484358, 512036, 1126479]],
        ];

        $now = now();
        $rows = [];
        foreach ($categories as [$code, $libelle, $ordre, $salaires]) {
            foreach ($salaires as $i => $salaire) {
                $echelon = $i + 1;
                $rows[] = [
                    'categorie'        => $code,
                    'libelle'          => $libelle,
                    'ordre'            => $ordre,
                    'echelon'          => $echelon,
                    'anciennete_mois'  => $echelons[$echelon]['mois'],
                    'coefficient'      => $echelons[$echelon]['coef'],
                    'salaire'          => $salaire,
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ];
            }
        }

        DB::table('grille_salariale')->insert($rows);
    }

    public function down(): void
    {
        Schema::dropIfExists('grille_salariale');
    }
};
