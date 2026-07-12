<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contrats', function (Blueprint $table) {
            // Honoraires spécifiques aux contrats de Prestation (indépendants
            // du salaire_base, utilisé pour CDI/CDD/Stagiaire/Vacataire).
            $table->decimal('honoraire_garde', 10, 2)->nullable()->after('salaire_base');
            $table->decimal('honoraire_permanence', 10, 2)->nullable()->after('honoraire_garde');
        });
    }

    public function down(): void
    {
        Schema::table('contrats', function (Blueprint $table) {
            $table->dropColumn(['honoraire_garde', 'honoraire_permanence']);
        });
    }
};
