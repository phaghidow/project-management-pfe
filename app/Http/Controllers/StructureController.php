<?php

namespace App\Http\Controllers;

use App\Models\Structure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StructureController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin'); // Seul l'admin peut gérer les structures
    }

    public function index()
    {
        $structures = Structure::with('parent')->orderBy('level')->get();
        $hierarchy = Structure::getHierarchyTree();
        return view('structures.index', compact('structures', 'hierarchy'));
    }

    public function create()
    {
        $structures = Structure::all();
        return view('structures.create', compact('structures'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:dg,pole,division,direction,autre',
            'code' => 'nullable|string|unique:structures',
            'parent_id' => 'nullable|exists:structures,id',
            'description' => 'nullable|string',
        ]);

        // Calculer le niveau automatiquement
        if ($validated['parent_id']) {
            $parent = Structure::find($validated['parent_id']);
            $validated['level'] = $parent->level + 1;
        } else {
            $validated['level'] = 0;
        }

        Structure::create($validated);

        return redirect()->route('structures.index')
            ->with('success', 'Structure créée avec succès.');
    }

    public function edit(Structure $structure)
    {
        $structures = Structure::where('id', '!=', $structure->id)->get();
        return view('structures.edit', compact('structure', 'structures'));
    }

    public function update(Request $request, Structure $structure)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:dg,pole,division,direction,autre',
            'code' => 'nullable|string|unique:structures,code,' . $structure->id,
            'parent_id' => 'nullable|exists:structures,id',
            'description' => 'nullable|string',
        ]);

        if ($validated['parent_id']) {
            $parent = Structure::find($validated['parent_id']);
            $validated['level'] = $parent->level + 1;
        } else {
            $validated['level'] = 0;
        }

        $structure->update($validated);

        return redirect()->route('structures.index')
            ->with('success', 'Structure mise à jour avec succès.');
    }

    public function destroy(Structure $structure)
    {
        // Vérifier si la structure a des enfants
        if ($structure->children()->count() > 0) {
            return back()->with('error', 'Impossible de supprimer une structure qui a des enfants.');
        }

        $structure->delete();
        return redirect()->route('structures.index')
            ->with('success', 'Structure supprimée avec succès.');
    }

    // API pour obtenir enfants d'une structure
    public function getChildren($parentId)
    {
        $children = Structure::where('parent_id', $parentId)->with('children')->get();
        return response()->json($children);
    }

    // API pour obtenir une structure (pour level calc + cycle check)
    public function getStructure($id)
    {
        $structure = Structure::findOrFail($id);
        return response()->json($structure);
    }

    // Check if parent creates cycle
    public function checkParent(Request $request)
    {
        $validated = $request->validate([
            'structure_id' => 'required|exists:structures,id',
            'parent_id' => 'nullable|exists:structures,id'
        ]);

        if ($validated['parent_id']) {
            $isDescendant = Structure::where('id', $validated['structure_id'])
                ->whereHas('ancestors', function ($q) use ($validated) {
                    $q->where('id', $validated['parent_id']);
                })->exists();

            if ($isDescendant) {
                return response()->json(['valid' => false, 'error' => 'Parent crée un cycle hiérarchique'], 422);
            }
        }

        return response()->json(['valid' => true]);
    }

    // API pour obtenir l'arbre hiérarchique (pour JS)
    public function getHierarchy()
    {
        $structures = Structure::with('children')->whereNull('parent_id')->get();
        return response()->json($structures);
    }
}