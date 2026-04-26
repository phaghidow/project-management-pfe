<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AppDataSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('structures')->where('code', 'DG')->exists()) {


            if (DB::table('attachments')->count() === 0) {
                $now = now();
                $projectId = DB::table('projects')->value('id');
                $taskId = DB::table('tasks')->value('id');
                $userId = DB::table('users')->value('id');

                if ($projectId && $taskId && $userId) {
                    $projectFile = 'attachments/project-demo/project-note.txt';
                    $taskFile = 'attachments/task-demo/task-note.txt';

                    Storage::disk('public')->put($projectFile, "Demo attachment for project\n");
                    Storage::disk('public')->put($taskFile, "Demo attachment for task\n");

                    DB::table('attachments')->insert([
                        [
                            'attachable_type' => \App\Models\Project::class,
                            'attachable_id' => $projectId,
                            'user_id' => $userId,
                            'name' => 'Project demo note.txt',
                            'path' => $projectFile,
                            'mime_type' => 'text/plain',
                            'size' => strlen("Demo attachment for project\n"),
                            'disk' => 'public',
                            'created_at' => $now,
                            'updated_at' => $now,
                        ],
                        [
                            'attachable_type' => \App\Models\Task::class,
                            'attachable_id' => $taskId,
                            'user_id' => $userId,
                            'name' => 'Task demo note.txt',
                            'path' => $taskFile,
                            'mime_type' => 'text/plain',
                            'size' => strlen("Demo attachment for task\n"),
                            'disk' => 'public',
                            'created_at' => $now,
                            'updated_at' => $now,
                        ],
                    ]);

                    $this->command->info('AppDataSeeder: sample attachments inserted on existing data.');
                }
            }

            if (DB::table('calendar_events')->count() === 0) {
                $now = now();
                $userId = DB::table('users')->value('id');

                if ($userId) {
                    DB::table('calendar_events')->insert([
                        [
                            'user_id' => $userId,
                            'title' => 'Réunion de pilotage',
                            'description' => 'Suivi hebdomadaire des projets en cours.',
                            'start_date' => $now->copy()->addDay()->setHour(10)->setMinute(0)->setSecond(0),
                            'end_date' => $now->copy()->addDay()->setHour(11)->setMinute(0)->setSecond(0),
                            'all_day' => 0,
                            'color' => '#2563eb',
                            'created_at' => $now,
                            'updated_at' => $now,
                        ],
                        [
                            'user_id' => $userId,
                            'title' => 'Atelier de validation',
                            'description' => 'Atelier de revue des livrables techniques.',
                            'start_date' => $now->copy()->addDays(3)->setHour(14)->setMinute(0)->setSecond(0),
                            'end_date' => $now->copy()->addDays(3)->setHour(16)->setMinute(0)->setSecond(0),
                            'all_day' => 0,
                            'color' => '#8b5cf6',
                            'created_at' => $now,
                            'updated_at' => $now,
                        ],
                    ]);

                    $this->command->info('AppDataSeeder: sample calendar events inserted on existing data.');
                }
            }

            $this->command->warn('AppDataSeeder skipped: sample data seems already present.');
            return;
        }

        $now = now();

        // Structures
        $dgId = DB::table('structures')->insertGetId([
            'name' => 'Direction Generale',
            'type' => 'dg',
            'code' => 'DG',
            'parent_id' => null,
            'level' => 0,
            'description' => 'Direction Generale',
            'is_active' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $techId = DB::table('structures')->insertGetId([
            'name' => 'Pole Technique',
            'type' => 'pole',
            'code' => 'POLE-TECH',
            'parent_id' => $dgId,
            'level' => 1,
            'description' => 'Pole Technique',
            'is_active' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $commId = DB::table('structures')->insertGetId([
            'name' => 'Pole Commercial',
            'type' => 'pole',
            'code' => 'POLE-COMM',
            'parent_id' => $dgId,
            'level' => 1,
            'description' => 'Pole Commercial',
            'is_active' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $dsiId = DB::table('structures')->insertGetId([
            'name' => 'Direction des Systemes d Information',
            'type' => 'direction',
            'code' => 'DSI',
            'parent_id' => $techId,
            'level' => 2,
            'description' => 'DSI',
            'is_active' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $drhId = DB::table('structures')->insertGetId([
            'name' => 'Direction des Ressources Humaines',
            'type' => 'direction',
            'code' => 'DRH',
            'parent_id' => $commId,
            'level' => 2,
            'description' => 'DRH',
            'is_active' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Users
        $adminId = DB::table('users')->insertGetId([
            'name' => 'Admin Principal',
            'username' => 'admin',
            'email' => 'admin@at.dz',
            'email_verified_at' => $now,
            'password' => Hash::make('password'),
            'remember_token' => null,
            'role' => 'admin',
            'structure_id' => $dgId,
            'status' => 'active',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $chefDeptId = DB::table('users')->insertGetId([
            'name' => 'Chef Departement Technique',
            'username' => 'chefdept',
            'email' => 'chef.dept@algerietelecom.dz',
            'email_verified_at' => $now,
            'password' => Hash::make('password'),
            'remember_token' => null,
            'role' => 'chef_departement',
            'structure_id' => $techId,
            'status' => 'active',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $chefProjetAId = DB::table('users')->insertGetId([
            'name' => 'Chef Projet A',
            'username' => 'chefprojet1',
            'email' => 'chef.projet1@at.dz',
            'email_verified_at' => $now,
            'password' => Hash::make('password'),
            'remember_token' => null,
            'role' => 'chef_projet',
            'structure_id' => $dsiId,
            'status' => 'active',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $chefProjetBId = DB::table('users')->insertGetId([
            'name' => 'Chef Projet B',
            'username' => 'chefprojet2',
            'email' => 'chef.projet2@algerietelecom.dz',
            'email_verified_at' => $now,
            'password' => Hash::make('password'),
            'remember_token' => null,
            'role' => 'chef_projet',
            'structure_id' => $drhId,
            'status' => 'active',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $collab1Id = DB::table('users')->insertGetId([
            'name' => 'Intervenant One',
            'username' => 'intervenant1',
            'email' => 'intervenant1@at.dz',
            'email_verified_at' => $now,
            'password' => Hash::make('password'),
            'remember_token' => null,
            'role' => 'chef_projet',
            'structure_id' => $dsiId,
            'status' => 'active',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $collab2Id = DB::table('users')->insertGetId([
            'name' => 'Intervenant Two',
            'username' => 'intervenant2',
            'email' => 'intervenant2@algerietelecom.dz',
            'email_verified_at' => $now,
            'password' => Hash::make('password'),
            'remember_token' => null,
            'role' => 'chef_projet',
            'structure_id' => $dsiId,
            'status' => 'active',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Projects
        $projectAId = DB::table('projects')->insertGetId([
            'name' => 'Migration Fibre Region Est',
            'description' => 'Projet de modernisation du reseau fibre',
            'user_id' => $chefProjetAId,
            'start_date' => now()->subDays(20)->toDateString(),
            'end_date' => now()->addDays(30)->toDateString(),
            'status' => 'in_progress',
            'progress' => 45,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $projectBId = DB::table('projects')->insertGetId([
            'name' => 'Digitalisation RH 2026',
            'description' => 'Automatisation des workflows RH',
            'user_id' => $chefProjetBId,
            'start_date' => now()->subDays(10)->toDateString(),
            'end_date' => now()->addDays(60)->toDateString(),
            'status' => 'draft',
            'progress' => 10,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Milestones
        $m1Id = DB::table('milestones')->insertGetId([
            'name' => 'Preparation Technique',
            'project_id' => $projectAId,
            'due_date' => now()->addDays(7)->toDateString(),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $m2Id = DB::table('milestones')->insertGetId([
            'name' => 'Deploiement Pilote',
            'project_id' => $projectAId,
            'due_date' => now()->addDays(20)->toDateString(),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $m3Id = DB::table('milestones')->insertGetId([
            'name' => 'Conception Process RH',
            'project_id' => $projectBId,
            'due_date' => now()->addDays(14)->toDateString(),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Tasks
        $t1Id = DB::table('tasks')->insertGetId([
            'name' => 'Audit infrastructure existante',
            'milestone_id' => $m1Id,
            'start_date' => now()->subDays(8)->toDateString(),
            'end_date' => now()->subDays(2)->toDateString(),
            'due_date' => now()->subDays(1)->toDateString(),
            'status' => 'validated',
            'validated_at' => now()->subDay(),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $t2Id = DB::table('tasks')->insertGetId([
            'name' => 'Plan d adressage IP',
            'milestone_id' => $m1Id,
            'start_date' => now()->subDays(1)->toDateString(),
            'end_date' => now()->addDays(3)->toDateString(),
            'due_date' => now()->addDays(2)->toDateString(),
            'status' => 'in_progress',
            'validated_at' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $t3Id = DB::table('tasks')->insertGetId([
            'name' => 'Configuration OLT pilote',
            'milestone_id' => $m2Id,
            'start_date' => now()->addDays(4)->toDateString(),
            'end_date' => now()->addDays(10)->toDateString(),
            'due_date' => now()->addDays(10)->toDateString(),
            'status' => 'pending',
            'validated_at' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $t4Id = DB::table('tasks')->insertGetId([
            'name' => 'Ateliers recueil besoins RH',
            'milestone_id' => $m3Id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(5)->toDateString(),
            'due_date' => now()->addDays(6)->toDateString(),
            'status' => 'in_progress',
            'validated_at' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Assignments
        DB::table('task_user')->insert([
            ['task_id' => $t1Id, 'user_id' => $collab1Id, 'created_at' => $now, 'updated_at' => $now],
            ['task_id' => $t2Id, 'user_id' => $collab1Id, 'created_at' => $now, 'updated_at' => $now],
            ['task_id' => $t2Id, 'user_id' => $collab2Id, 'created_at' => $now, 'updated_at' => $now],
            ['task_id' => $t3Id, 'user_id' => $collab2Id, 'created_at' => $now, 'updated_at' => $now],
            ['task_id' => $t4Id, 'user_id' => $chefProjetBId, 'created_at' => $now, 'updated_at' => $now],
        ]);

        // Dependencies: t3 depends on t2, and t2 depends on t1
        DB::table('task_dependencies')->insert([
            ['task_id' => $t2Id, 'depends_on_task_id' => $t1Id, 'created_at' => $now, 'updated_at' => $now],
            ['task_id' => $t3Id, 'depends_on_task_id' => $t2Id, 'created_at' => $now, 'updated_at' => $now],
        ]);



        // A few notifications
        DB::table('notifications')->insert([
            [
                'user_id' => $chefProjetAId,
                'title' => 'Projet pret a suivre',
                'message' => 'Les jalons initiaux sont en place pour Migration Fibre Region Est.',
                'type' => 'task_assigned',
                'is_read' => 0,
                'read_at' => null,
                'related_type' => 'project',
                'related_id' => $projectAId,
                'metadata' => json_encode(['seeded' => true]),
                'sent_at' => $now,
                'acknowledged_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'user_id' => $collab1Id,
                'title' => 'Nouvelle tache assignee',
                'message' => 'Vous etes assigne a la tache Plan d adressage IP.',
                'type' => 'task_assigned',
                'is_read' => 0,
                'read_at' => null,
                'related_type' => 'task',
                'related_id' => $t2Id,
                'metadata' => json_encode(['seeded' => true]),
                'sent_at' => $now,
                'acknowledged_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        // Demo attachments
        $projectFile = 'attachments/project-demo/project-note.txt';
        $taskFile = 'attachments/task-demo/task-note.txt';

        Storage::disk('public')->put($projectFile, "Demo attachment for project\n");
        Storage::disk('public')->put($taskFile, "Demo attachment for task\n");

        DB::table('attachments')->insert([
            [
                'attachable_type' => \App\Models\Project::class,
                'attachable_id' => $projectAId,
                'user_id' => $chefProjetAId,
                'name' => 'Project demo note.txt',
                'path' => $projectFile,
                'mime_type' => 'text/plain',
                'size' => strlen("Demo attachment for project\n"),
                'disk' => 'public',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'attachable_type' => \App\Models\Task::class,
                'attachable_id' => $t2Id,
                'user_id' => $collab1Id,
                'name' => 'Task demo note.txt',
                'path' => $taskFile,
                'mime_type' => 'text/plain',
                'size' => strlen("Demo attachment for task\n"),
                'disk' => 'public',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        DB::table('calendar_events')->insert([
            [
                'user_id' => $adminId,
                'title' => 'Réunion de pilotage',
                'description' => 'Suivi hebdomadaire des projets en cours.',
                'start_date' => $now->copy()->addDay()->setHour(10)->setMinute(0)->setSecond(0),
                'end_date' => $now->copy()->addDay()->setHour(11)->setMinute(0)->setSecond(0),
                'all_day' => 0,
                'color' => '#2563eb',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'user_id' => $adminId,
                'title' => 'Atelier de validation',
                'description' => 'Atelier de revue des livrables techniques.',
                'start_date' => $now->copy()->addDays(3)->setHour(14)->setMinute(0)->setSecond(0),
                'end_date' => $now->copy()->addDays(3)->setHour(16)->setMinute(0)->setSecond(0),
                'all_day' => 0,
                'color' => '#8b5cf6',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        $this->command->info('AppDataSeeder: sample data inserted successfully.');
        $this->command->line('Login accounts (password: password):');
        $this->command->line('- admin@at.dz (admin)');
        $this->command->line('- chef.projet1@at.dz (chef_projet)');
        $this->command->line('- chef.dept@algerietelecom.dz (chef_departement)');
    }
}
