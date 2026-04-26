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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->text('description')->nullable();

            // Responsable du projet
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Dates du projet
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            // Statut du projet
            $table->enum('status', ['draft', 'in_progress', 'completed'])
                  ->default('draft');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};