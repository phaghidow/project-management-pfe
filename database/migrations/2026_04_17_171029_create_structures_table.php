<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('structures', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nom de la structure (ex: DG, DSI, etc.)
            $table->string('type'); // Type: dg, pole, division, direction, autre
            $table->string('code')->unique()->nullable(); // Code unique (ex: DG, DSI-01)
            
            // Relation parent flexible pour gérer l'arborescence
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('structures')
                ->nullOnDelete(); 

            $table->integer('level')->default(0); // Niveau hiérarchique (0,1,2,3...)
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Index pour optimiser les performances des recherches fréquentes
            $table->index(['type', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('structures');
    }
};