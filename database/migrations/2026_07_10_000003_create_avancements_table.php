<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('avancements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personnel_id')->constrained('personnels')->cascadeOnDelete();
            $table->foreignId('contrat_id')->nullable()->constrained('contrats')->nullOnDelete();

            $table->enum('type', ['echelon', 'bonification']);
            $table->date('date_effet');

            $table->string('ancienne_categorie', 10)->nullable();
            $table->unsignedTinyInteger('ancien_echelon')->nullable();
            $table->string('nouvelle_categorie', 10)->nullable();
            $table->unsignedTinyInteger('nouvel_echelon')->nullable();

            $table->unsignedInteger('ancien_salaire')->nullable();
            $table->unsignedInteger('nouveau_salaire');
            $table->decimal('coefficient_applique', 5, 3)->nullable();

            $table->string('numero_reference', 100)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('avancements');
    }
};
