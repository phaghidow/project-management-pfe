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
        if (DB::getDriverName() === 'sqlite') {
            Schema::create('notifications_new', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('title');
                $table->text('message')->nullable();
                $table->string('type', 100);
                $table->boolean('is_read')->default(false);
                $table->timestamps();
                $table->timestamp('read_at')->nullable();
                $table->string('related_type')->nullable();
                $table->unsignedBigInteger('related_id')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->timestamp('acknowledged_at')->nullable();

                $table->unique(['user_id', 'type', 'related_id']);
                $table->index(['user_id', 'type', 'related_id', 'sent_at']);
            });

            DB::statement("INSERT INTO notifications_new (id, user_id, title, message, type, is_read, created_at, updated_at, read_at, related_type, related_id, metadata, sent_at, acknowledged_at) SELECT id, user_id, title, message, type, is_read, created_at, updated_at, read_at, related_type, related_id, metadata, sent_at, acknowledged_at FROM notifications");

            Schema::disableForeignKeyConstraints();
            Schema::drop('notifications');
            Schema::rename('notifications_new', 'notifications');
            Schema::enableForeignKeyConstraints();

            return;
        }

        DB::statement('ALTER TABLE notifications MODIFY COLUMN type VARCHAR(100)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            Schema::create('notifications_new', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('title');
                $table->text('message')->nullable();
                $table->enum('type', [
                    'task_assigned',
                    'task_due',
                    'task_due_soon',
                    'task_overdue',
                    'project_completed',
                    'project_closed',
                    'project_ready_review',
                    'task_validated',
                    'role_announcement',
                    'structure_announcement',
                ]);
                $table->boolean('is_read')->default(false);
                $table->timestamps();
                $table->timestamp('read_at')->nullable();
                $table->string('related_type')->nullable();
                $table->unsignedBigInteger('related_id')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->timestamp('acknowledged_at')->nullable();

                $table->unique(['user_id', 'type', 'related_id']);
                $table->index(['user_id', 'type', 'related_id', 'sent_at']);
            });

            DB::statement("INSERT INTO notifications_new (id, user_id, title, message, type, is_read, created_at, updated_at, read_at, related_type, related_id, metadata, sent_at, acknowledged_at) SELECT id, user_id, title, message, type, is_read, created_at, updated_at, read_at, related_type, related_id, metadata, sent_at, acknowledged_at FROM notifications");

            Schema::disableForeignKeyConstraints();
            Schema::drop('notifications');
            Schema::rename('notifications_new', 'notifications');
            Schema::enableForeignKeyConstraints();

            return;
        }

        DB::statement("ALTER TABLE notifications MODIFY COLUMN type ENUM('task_assigned','task_due','task_due_soon','task_overdue','project_completed','project_closed','project_ready_review','task_validated','role_announcement','structure_announcement')");
    }
};
