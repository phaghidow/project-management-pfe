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
        Schema::create('task_dependencies', function (Blueprint $table) {
            $table->id();

            // La tâche concernée
            $table->foreignId('task_id')->constrained()->onDelete('cascade');

            // La tâche dont elle dépend (clé étrangère pointant explicitement vers 'tasks')
            $table->foreignId('depends_on_task_id')
                  ->constrained('tasks')
                  ->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_dependencies');
    }
};