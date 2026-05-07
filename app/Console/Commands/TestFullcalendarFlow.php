<?php

namespace App\Console\Commands;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Console\Command;

class TestFullcalendarFlow extends Command
{
    protected $signature = 'test:fullcalendar {--user-id=1} {--month=5}';
    protected $description = 'Test the complete FullCalendar flow with logging';

    public function handle()
    {
        $userId = $this->option('user-id');
        $month = $this->option('month');
        $user = User::find($userId);

        if (!$user) {
            $this->error("❌ Utilisateur $userId non trouvé");
            return;
        }

        auth()->login($user);

        $this->line("=== TEST FULLCALENDAR FLOW ===");
        $this->line("Utilisateur: {$user->name} (ID: $userId, Rôle: {$user->role})");
        $this->newLine();

        // Simulate FullCalendar sending dates like it really does
        // FullCalendar sends in ISO format
        $year = 2026;
        $start = Carbon::create($year, $month, 1)->startOfDay()->toIso8601String();
        $end = Carbon::create($year, $month, 1)->endOfMonth()->endOfDay()->toIso8601String();

        $this->line("Simulating FullCalendar request for month $month/$year:");
        $this->line("  start (ISO): $start");
        $this->line("  end (ISO): $end");
        $this->newLine();

        // Create request
        $request = Request::create(
            '/api/calendar/events',
            'GET',
            ['start' => $start, 'end' => $end],
            [],
            [],
            ['HTTP_ACCEPT' => 'application/json']
        );

        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        // Call controller
        $controller = new \App\Http\Controllers\CalendarController();
        $response = $controller->events($request);

        // Check response
        $statusCode = $response->getStatusCode();
        $content = $response->getContent();
        $data = json_decode($content, true);

        $this->line("Response Status: $statusCode");
        $this->line("Events Count: " . count($data));
        $this->newLine();

        // Check logs
        $logFile = storage_path('logs/laravel.log');
        if (file_exists($logFile)) {
            $this->line("Last 20 log lines (from laravel.log):");
            $lines = array_slice(file($logFile), -20);
            foreach ($lines as $line) {
                if (strpos($line, 'Calendar') !== false || strpos($line, 'calendar') !== false) {
                    $this->line(trim($line));
                }
            }
        }

        $this->newLine();
        if (count($data) > 0) {
            $this->info("✅ SUCCESS: Got " . count($data) . " events");
            $this->line("Sample events:");
            foreach (array_slice($data, 0, 3) as $event) {
                $this->line("  - {$event['title']} ({$event['start']} → {$event['end']})");
            }
        } else {
            $this->warn("⚠️  Got 0 events - check if dates match your data");
        }
    }
}
