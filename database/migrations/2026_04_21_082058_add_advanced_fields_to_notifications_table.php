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
        Schema::table('notifications', function (Blueprint $table) {
            $table->timestamp('read_at')->nullable()->after('is_read');
            $table->string('related_type')->nullable()->after('read_at');
            $table->unsignedBigInteger('related_id')->nullable()->after('related_type');
            $table->json('metadata')->nullable()->after('related_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn(['read_at', 'related_type', 'related_id', 'metadata']);
        });
    }
};

