<?php

namespace App\Http\Controllers;

use App\Models\Structure;
use Illuminate\Http\Request;

class OrganigrammeController extends Controller
{
    public function index()
    {
        $structures = Structure::with('children')->whereNull('parent_id')->get();
        return view('organigramme.index', compact('structures'));
    }

    // API pour obtenir les structures en JSON (pour JS dynamique)
    public function getStructures()
    {
        $structures = Structure::with('children')->get();
        return response()->json($structures);
    }

    // Obtenir la hiérarchie d'une structure spécifique
    public function getHierarchy($id)
    {
        $structure = Structure::with('children')->findOrFail($id);
        return response()->json($structure);
    }
}