<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->morphs('attachable'); // project_id/task_id + attachable_type
            $table->string('name');
            $table->string('path');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->string('disk', 20)->default('public');
            $table->timestamps();

            $table->unique(['attachable_id', 'attachable_type', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};

