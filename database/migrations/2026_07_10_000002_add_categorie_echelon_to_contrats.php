<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contrats', function (Blueprint $table) {
            $table->string('categorie', 10)->nullable()->after('categorie_echelon');
            $table->unsignedTinyInteger('echelon')->nullable()->after('categorie');
        });
    }

    public function down(): void
    {
        Schema::table('contrats', function (Blueprint $table) {
            $table->dropColumn(['categorie', 'echelon']);
        });
    }
};
