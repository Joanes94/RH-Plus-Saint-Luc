<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personnels', function (Blueprint $table) {
            $table->enum('statut', ['actif', 'inactif', 'en_conge', 'retraite', 'ancien'])
                  ->default('actif')->change();
        });

        Schema::table('personnels', function (Blueprint $table) {
            $table->string('motif_depart', 50)->nullable()->after('statut');
            $table->date('date_depart')->nullable()->after('motif_depart');
        });
    }

    public function down(): void
    {
        Schema::table('personnels', function (Blueprint $table) {
            $table->dropColumn(['motif_depart', 'date_depart']);
        });

        Schema::table('personnels', function (Blueprint $table) {
            $table->enum('statut', ['actif', 'inactif', 'en_conge', 'retraite'])
                  ->default('actif')->change();
        });
    }
};
