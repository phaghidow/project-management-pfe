<?php

namespace App\Console\Commands;

use App\Http\Controllers\CalendarController;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Console\Command;

class TestCalendarControllerOutput extends Command
{
    protected $signature = 'test:calendar-controller {--user-id=1}';
    protected $description = 'Test calendar controller output';

    public function handle()
    {
        $userId = $this->option('user-id');
        $user = User::find($userId);

        if (!$user) {
            $this->error("❌ Utilisateur $userId non trouvé");
            return;
        }

        auth()->login($user);

        $this->line("=== TEST CALENDAR CONTROLLER ===");
        $this->line("Utilisateur: {$user->name}");
        $this->newLine();

        // Simulate FullCalendar request with current month
        $start = Carbon::now()->startOfMonth()->toIso8601String();
        $end = Carbon::now()->endOfMonth()->toIso8601String();

        $this->line("Paramètres envoyés:");
        $this->line("  start: $start");
        $this->line("  end: $end");
        $this->newLine();

        // Create a request like FullCalendar would send
        $request = Request::create(
            '/api/calendar/events',
            'GET',
            ['start' => $start, 'end' => $end],
            [],
            [],
            ['HTTP_ACCEPT' => 'application/json'],
            null
        );

        // Set the authenticated user
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        // Call the controller
        $controller = new CalendarController();
        $response = $controller->events($request);

        // Get the JSON content
        $content = $response->getContent();
        $data = json_decode($content, true);

        $this->line("Réponse reçue: " . count($data) . " événements");
        $this->newLine();

        if (count($data) === 0) {
            $this->warn("⚠️  Pas d'événements retournés!");
        } else {
            $this->line("Exemples d'événements:");
            foreach (array_slice($data, 0, 5) as $event) {
                $type = $event['extendedProps']['type'] ?? 'unknown';
                $this->line("  - {$event['title']} (type: $type)");
                $this->line("    start: {$event['start']}, end: {$event['end']}");
            }
        }

        // Log response for debugging
        $this->newLine();
        $this->line("JSON brut (premiers 1000 chars):");
        $this->line(substr($content, 0, 1000));
        if (strlen($content) > 1000) {
            $this->line("... (truncated)");
        }
    }
}
