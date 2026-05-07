<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCalendarIndexes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tasks')) {
            Schema::table('tasks', function (Blueprint $table) {
                if (Schema::hasColumn('tasks', 'start_date')) {
                    $table->index('start_date');
                }
                if (Schema::hasColumn('tasks', 'end_date')) {
                    $table->index('end_date');
                }
                if (Schema::hasColumn('tasks', 'due_date')) {
                    $table->index('due_date');
                }
            });
        }

        if (Schema::hasTable('milestones')) {
            Schema::table('milestones', function (Blueprint $table) {
                if (Schema::hasColumn('milestones', 'due_date')) {
                    $table->index('due_date');
                }
            });
        }

        if (Schema::hasTable('calendar_events')) {
            Schema::table('calendar_events', function (Blueprint $table) {
                if (Schema::hasColumn('calendar_events', 'start_date')) {
                    $table->index('start_date');
                }
                if (Schema::hasColumn('calendar_events', 'end_date')) {
                    $table->index('end_date');
                }
            });
        }

        if (Schema::hasTable('projects')) {
            Schema::table('projects', function (Blueprint $table) {
                if (Schema::hasColumn('projects', 'end_date')) {
                    $table->index('end_date');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('tasks')) {
            Schema::table('tasks', function (Blueprint $table) {
                if (Schema::hasColumn('tasks', 'start_date')) {
                    $table->dropIndex(['start_date']);
                }
                if (Schema::hasColumn('tasks', 'end_date')) {
                    $table->dropIndex(['end_date']);
                }
                if (Schema::hasColumn('tasks', 'due_date')) {
                    $table->dropIndex(['due_date']);
                }
            });
        }

        if (Schema::hasTable('milestones')) {
            Schema::table('milestones', function (Blueprint $table) {
                if (Schema::hasColumn('milestones', 'due_date')) {
                    $table->dropIndex(['due_date']);
                }
            });
        }

        if (Schema::hasTable('calendar_events')) {
            Schema::table('calendar_events', function (Blueprint $table) {
                if (Schema::hasColumn('calendar_events', 'start_date')) {
                    $table->dropIndex(['start_date']);
                }
                if (Schema::hasColumn('calendar_events', 'end_date')) {
                    $table->dropIndex(['end_date']);
                }
            });
        }

        if (Schema::hasTable('projects')) {
            Schema::table('projects', function (Blueprint $table) {
                if (Schema::hasColumn('projects', 'end_date')) {
                    $table->dropIndex(['end_date']);
                }
            });
        }
    }
}
