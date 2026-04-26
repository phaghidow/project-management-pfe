<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'structure_id')) {
                $table->foreignId('structure_id')
                      ->nullable()
                      ->constrained('structures')
                      ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'structure_id')) {
                // Check if foreign key exists before dropping
                $indexName = 'users_structure_id_foreign';
                $indexes = DB::select("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_NAME='users' AND COLUMN_NAME='structure_id' AND REFERENCED_TABLE_NAME='structures'");
                if (!empty($indexes)) {
                    $table->dropForeign([$indexName]);
                }
                $table->dropColumn('structure_id');
            }
        });
    }
};