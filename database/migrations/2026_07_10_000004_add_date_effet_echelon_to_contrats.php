<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contrats', function (Blueprint $table) {
            // Date depuis laquelle l'échelon actuel s'applique. Sert de point de
            // départ pour calculer les 24 mois avant le prochain avancement,
            // tant qu'aucun avancement n'a encore été enregistré automatiquement.
            $table->date('date_effet_echelon')->nullable()->after('echelon');
        });
    }

    public function down(): void
    {
        Schema::table('contrats', function (Blueprint $table) {
            $table->dropColumn('date_effet_echelon');
        });
    }
};
