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
        Schema::table('users', function (Blueprint $table) {

            // Ajouter la colonne structure_id seulement si elle n'existe pas
            if (!Schema::hasColumn('users', 'structure_id')) {
                $table->foreignId('structure_id')
                    ->nullable()
                    ->after('id') // Optionnel : pour placer la colonne au début
                    ->constrained('structures')
                    ->nullOnDelete();
            }

            // Ajouter le rôle seulement si il n'existe pas
            if (!Schema::hasColumn('users', 'role')) {
                $table->enum('role', [
                    'admin',
                    'chef_projet',
                    'chef_departement'
                ])->default('chef_projet')->after('email');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {

            if (Schema::hasColumn('users', 'structure_id')) {
                // On retire la contrainte avant de supprimer la colonne
                $table->dropForeign(['structure_id']);
                $table->dropColumn('structure_id');
            }

            if (Schema::hasColumn('users', 'role')) {
                $table->dropColumn('role');
            }
        });
    }
};