<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function generate(Project $project)
    {
        $project->load('tasks.users', 'milestones');

        $pdf = Pdf::loadView('reports.project', compact('project'));

        return $pdf->download('project-report.pdf');
    }
}
