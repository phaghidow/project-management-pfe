<?php
$projectRoot = realpath(__DIR__ . '/..');
require $projectRoot . '/vendor/autoload.php';
$app = require $projectRoot . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$app->make(Illuminate\Contracts\Console\Kernel::class);

function call_route($kernel, $method, $uri, $query = []) {
    $request = Illuminate\Http\Request::create($uri, $method, $query);
    $response = $kernel->handle($request);
    $content = method_exists($response, 'getContent') ? $response->getContent() : (string)$response;
    $kernel->terminate($request, $response);
    return [$response->getStatusCode(), $content];
}

// Calendar events
$start = (new Carbon\Carbon())->subDays(7)->toIsoString();
$end = (new Carbon\Carbon())->toIsoString();
list($status, $body) = call_route($kernel, 'GET', '/api/calendar/events', ['start' => $start, 'end' => $end]);
echo "== /api/calendar/events ($status) ==\n";
echo substr($body, 0, 4000) . (strlen($body) > 4000 ? "\n... (truncated)\n" : "\n");

echo "\n";

// Tasks Gantt
list($status2, $body2) = call_route($kernel, 'GET', '/api/tasks-gantt');
echo "== /api/tasks-gantt ($status2) ==\n";
echo substr($body2, 0, 4000) . (strlen($body2) > 4000 ? "\n... (truncated)\n" : "\n");

return 0;
