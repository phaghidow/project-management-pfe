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
        // Migrate existing pending tasks to in_progress
        DB::table('tasks')->where('status', 'pending')->update(['status' => 'in_progress']);

        // Update enum to only allow in_progress and validated
        if (DB::getDriverName() === 'sqlite') {
            // SQLite doesn't support altering enum columns directly
            // We create a new table, copy data, drop old, rename
            Schema::create('tasks_new', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->foreignId('milestone_id')->constrained()->onDelete('cascade');
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->date('due_date')->nullable();
                $table->enum('status', ['in_progress', 'validated'])->default('in_progress');
                $table->timestamp('validated_at')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });

            // Copy only matching columns to avoid "columns mismatch" on SQLite
            DB::statement('INSERT INTO tasks_new (id, name, milestone_id, start_date, end_date, due_date, status, validated_at, created_at, updated_at, deleted_at) SELECT id, name, milestone_id, start_date, end_date, due_date, status, validated_at, created_at, updated_at, deleted_at FROM tasks');

            Schema::disableForeignKeyConstraints();
            Schema::drop('tasks');
            Schema::rename('tasks_new', 'tasks');
            Schema::enableForeignKeyConstraints();
        } else {
            DB::statement("ALTER TABLE tasks MODIFY COLUMN status ENUM('in_progress', 'validated') DEFAULT 'in_progress'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            Schema::create('tasks_new', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->foreignId('milestone_id')->constrained()->onDelete('cascade');
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->date('due_date')->nullable();
                $table->enum('status', ['pending', 'in_progress', 'validated'])->default('pending');
                $table->timestamp('validated_at')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });

            // Copy only matching columns back
            DB::statement('INSERT INTO tasks_new (id, name, milestone_id, start_date, end_date, due_date, status, validated_at, created_at, updated_at, deleted_at) SELECT id, name, milestone_id, start_date, end_date, due_date, status, validated_at, created_at, updated_at, deleted_at FROM tasks');

            Schema::disableForeignKeyConstraints();
            Schema::drop('tasks');
            Schema::rename('tasks_new', 'tasks');
            Schema::enableForeignKeyConstraints();
        } else {
            DB::statement("ALTER TABLE tasks MODIFY COLUMN status ENUM('pending', 'in_progress', 'validated') DEFAULT 'pending'");
        }
    }
};
