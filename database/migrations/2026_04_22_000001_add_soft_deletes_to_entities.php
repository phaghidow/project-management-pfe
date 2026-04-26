<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'deleted_at')) {
                $table->softDeletes();
            }
            if (!Schema::hasColumn('users', 'delete_force')) {
                $table->boolean('delete_force')->default(false)->after('deleted_at');
            }
        });

        Schema::table('projects', function (Blueprint $table) {
            if (!Schema::hasColumn('projects', 'deleted_at')) {
                $table->softDeletes();
            }
            if (!Schema::hasColumn('projects', 'delete_force')) {
                $table->boolean('delete_force')->default(false)->after('deleted_at');
            }
        });

        Schema::table('tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('tasks', 'deleted_at')) {
                $table->softDeletes();
            }
            if (!Schema::hasColumn('tasks', 'delete_force')) {
                $table->boolean('delete_force')->default(false)->after('deleted_at');
            }
        });

        Schema::table('milestones', function (Blueprint $table) {
            if (!Schema::hasColumn('milestones', 'deleted_at')) {
                $table->softDeletes();
            }
            if (!Schema::hasColumn('milestones', 'delete_force')) {
                $table->boolean('delete_force')->default(false)->after('deleted_at');
            }
        });

        Schema::table('structures', function (Blueprint $table) {
            if (!Schema::hasColumn('structures', 'deleted_at')) {
                $table->softDeletes();
            }
            if (!Schema::hasColumn('structures', 'delete_force')) {
                $table->boolean('delete_force')->default(false)->after('deleted_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn('delete_force');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn('delete_force');
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn('delete_force');
        });

        Schema::table('milestones', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn('delete_force');
        });

        Schema::table('structures', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn('delete_force');
        });
    }
};

