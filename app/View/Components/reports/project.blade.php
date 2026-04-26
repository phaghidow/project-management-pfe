<!DOCTYPE html>
<html>
<head>
    <title>Rapport Projet</title>
    <style>
        body { font-family: Arial; }
        h1 { color: #333; }
        .box { margin-bottom: 10px; }
    </style>
</head>
<body>

<h1>Rapport Projet : {{ $project->name }}</h1>

<div class="box">
    <strong>Progression :</strong> {{ $project->progress }}%
</div>

<div class="box">
    <strong>Status :</strong> {{ $project->status ?? 'En cours' }}
</div>

<hr>

<h3>Tâches</h3>

@foreach($project->tasks as $task)
    <div>
        • {{ $task->name }} ({{ $task->status }})
    </div>
@endforeach

<hr>

<h3>Jalons</h3>

@foreach($project->milestones as $m)
    <div>• {{ $m->name }}</div>
@endforeach

</body>
</html>