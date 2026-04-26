<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type');
            $table->unsignedBigInteger('entity_id');
            $table->string('action'); // create, update, delete, validate, activate, disable
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('action_at')->useCurrent();
            $table->json('old_data')->nullable();
            $table->json('new_data')->nullable();
            $table->json('technical_context')->nullable(); // ip, user_agent, route, etc.
            $table->timestamps();

            $table->index(['entity_type', 'entity_id']);
            $table->index(['actor_id']);
            $table->index('action_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};

