<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->timestamp('sent_at')->nullable()->after('metadata');
            $table->timestamp('acknowledged_at')->nullable()->after('sent_at');
            
            // Unique constraint to prevent duplicates per user/type/related (nulls allowed)
            $table->unique(['user_id', 'type', 'related_id'], 'notifications_user_type_related_unique');
            
            // Index for dedup queries
            $table->index(['user_id', 'type', 'related_id', 'sent_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            DB::statement('ALTER TABLE notifications DROP FOREIGN KEY notifications_user_id_foreign');
        } catch (\Throwable $e) {
            // The foreign key may already be absent on partially-applied databases.
        }

        try {
            DB::statement('ALTER TABLE notifications DROP INDEX notifications_user_type_related_unique');
        } catch (\Throwable $e) {
            // The unique index may already be absent on partially-applied databases.
        }

        try {
            DB::statement('ALTER TABLE notifications DROP INDEX notifications_user_id_type_related_id_sent_at_index');
        } catch (\Throwable $e) {
            // The dedup index may already be absent on partially-applied databases.
        }

        $columnsToDrop = array_values(array_filter([
            Schema::hasColumn('notifications', 'sent_at') ? 'sent_at' : null,
            Schema::hasColumn('notifications', 'acknowledged_at') ? 'acknowledged_at' : null,
        ]));

        if ($columnsToDrop !== []) {
            Schema::table('notifications', function (Blueprint $table) use ($columnsToDrop) {
                $table->dropColumn($columnsToDrop);
            });
        }

        try {
            DB::statement('ALTER TABLE notifications ADD CONSTRAINT notifications_user_id_foreign FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        } catch (\Throwable $e) {
            // If the foreign key already exists, leave the table as-is.
        }
    }
};
