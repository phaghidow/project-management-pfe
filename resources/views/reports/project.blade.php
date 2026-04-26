<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Rapport de Projet - {{ $project->name }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; color: #333; line-height: 1.6; }
        .header { text-align: center; margin-bottom: 40px; }
        .header h1 { color: #1e40af; font-size: 28px; margin-bottom: 10px; }
        .header h2 { color: #64748b; font-size: 20px; }
        .info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 30px 0; }
        .info-card { background: #f8fafc; padding: 20px; border-radius: 12px; border-left: 4px solid #3b82f6; }
        .info-label { font-weight: bold; color: #374151; margin-bottom: 5px; }
        .info-value { font-size: 18px; color: #1f2937; }
        table { width: 100%; border-collapse: collapse; margin: 30px 0; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #e5e7eb; }
        th { background: linear-gradient(135deg, #3b82f6, #1d4ed8); color: white; font-weight: bold; }
        tr:hover { background: #f8fafc; }
        .milestone-section { margin: 30px 0; }
        .milestone-header { background: #e0e7ff; padding: 15px; border-radius: 8px 8px 0 0; font-weight: bold; color: #1e40af; }
        .status-badge { padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; text-transform: uppercase; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-started { background: #dbeafe; color: #1e40af; }
        .status-validated { background: #dcfce7; color: #166534; }
        .footer { text-align: center; margin-top: 50px; padding-top: 30px; border-top: 2px solid #e5e7eb; color: #6b7280; font-style: italic; }
        .progress-bar { background: #e5e7eb; height: 20px; border-radius: 10px; overflow: hidden; margin: 10px 0; }
        .progress-fill { height: 100%; background: linear-gradient(90deg, #10b981, #059669); transition: width 0.3s ease; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Algérie Télécom</h1>
        <h2>Rapport Détaillé de Projet</h2>
        <p style="color: #6b7280; font-size: 14px;">Généré le {{ now()->format('d/m/Y à H:i') }}</p>
    </div>

    <div class="info-grid">
        <div class="info-card">
            <div class="info-label">Nom du projet</div>
            <div class="info-value">{{ $project->name }}</div>
        </div>
        <div class="info-card">
            <div class="info-label">Responsable</div>
            <div class="info-value">{{ $project->user->name ?? 'Non assigné' }}</div>
        </div>
        <div class="info-card">
            <div class="info-label">Date de début</div>
            <div class="info-value">{{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('d/m/Y') : '-' }}</div>
        </div>
        <div class="info-card">
            <div class="info-label">Date de fin</div>
            <div class="info-value">{{ $project->end_date ? \Carbon\Carbon::parse($project->end_date)->format('d/m/Y') : '-' }}</div>
        </div>
        <div class="info-card">
            <div class="info-label">Progression</div>
            <div class="info-value">{{ $project->progress ?? 0 }}%</div>
        </div>
        <div class="info-card">
            <div class="info-label">Statut</div>
            <div class="info-value">
                <span class="status-badge status-{{ strtolower($project->status ?? 'started') }}">
                    {{ ucfirst($project->status ?? 'En cours') }}
                </span>
            </div>
        </div>
    </div>

    @if($project->description)
    <div class="info-card" style="grid-column: 1 / -1; background: linear-gradient(135deg, #f8fafc, #f1f5f9);">
        <div class="info-label" style="margin-bottom: 10px;">Description</div>
        <div class="info-value">{{ $project->description }}</div>
    </div>
    @endif

    {{-- Milestones --}}
    @if($project->milestones->count() > 0)
    <div class="milestone-section">
        <div class="milestone-header">Jalons ({{ $project->milestones->count() }})</div>
        @foreach($project->milestones as $milestone)
        <div style="margin: 20px 0; padding: 20px; background: white; border-radius: 0 0 12px 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
            <h4 style="margin: 0 0 15px 0; color: #1e40af;">{{ $milestone->name }}</h4>
            <div class="progress-bar" style="width: 300px;">
                <div class="progress-fill" style="width: {{ $milestone->progress ?? 0 }}%"></div>
            </div>
            <p style="margin: 10px 0 0 0; font-size: 14px; color: #6b7280;">
                {{ $milestone->tasks->count() }} tâches • 
                {{ \Carbon\Carbon::parse($milestone->start_date)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($milestone->end_date)->format('d/m/Y') }}
            </p>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Tasks --}}
    <h3 style="margin: 40px 0 20px 0; color: #1f2937;">Tâches ({{ $project->tasks->count() }})</h3>
    <table>
        <thead>
            <tr>
                <th style="width: 40%;">Nom</th>
                <th style="width: 15%;">Statut</th>
                <th style="width: 20%;">Début</th>
                <th style="width: 20%;">Fin</th>
                <th style="width: 5%;">Resp.</th>
            </tr>
        </thead>
        <tbody>
            @forelse($project->tasks as $task)
            <tr>
                <td style="font-weight: 500;">{{ $task->name }}</td>
                <td><span class="status-badge status-{{ strtolower($task->status) }}">{{ ucfirst($task->status) }}</span></td>
                <td>{{ $task->start_date ? \Carbon\Carbon::parse($task->start_date)->format('d/m/Y') : '-' }}</td>
                <td style="color: {{ $task->end_date && \Carbon\Carbon::parse($task->end_date)->lt(now()) ? '#ef4444' : '#6b7280' }};">
                    {{ $task->end_date ? \Carbon\Carbon::parse($task->end_date)->format('d/m/Y') : '-' }}
                </td>
                <td>{{ $task->users->pluck('name')->implode(', ') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align: center; padding: 40px; color: #9ca3af;">Aucune tâche dans ce projet</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Rapport généré automatiquement par la plateforme de gestion de projets</p>
        <p>{{ now()->format('d F Y à H:i') }}</p>
    </div>
</body>
</html>
