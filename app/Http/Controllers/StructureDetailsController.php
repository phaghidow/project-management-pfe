<?php

namespace App\Http\Controllers;

use App\Models\Structure;
use Illuminate\Http\JsonResponse;

class StructureDetailsController extends Controller
{
    /**
     * Retourne une liste PLATE de toutes les structures actives
     * 
     * Format: 
     * [
     *   { "id": 1, "name": "PDG", "type": "executive", "parent_id": null },
     *   { "id": 2, "name": "DGA Finance", "type": "director", "parent_id": 1 },
     *   ...
     * ]
     * 
     * - Structures actives uniquement (is_active=true)
     * - Soft-deleted exclus par défaut
     * - Une seule requête, aucun eager loading inutile
     * 
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $structures = Structure::query()
            ->where('is_active', true)
            ->select(['id', 'name', 'type', 'parent_id'])
            ->orderBy('id')
            ->get();

        return response()->json($structures);
    }

    public function details(int $id): JsonResponse
    {
        $structure = Structure::with([
            'users:id,name,function,email,structure_id',
            'parent:id,name',
        ])->find($id);

        if (!$structure) {
            return response()->json(['message' => 'Structure introuvable.'], 404);
        }

        return response()->json([
            'id' => $structure->id,
            'name' => $structure->name,
            'type' => $structure->type,
            'code' => $structure->code,
            'level' => $structure->level,
            'description' => $structure->description,
            'parent' => $structure->parent ? [
                'id' => $structure->parent->id,
                'name' => $structure->parent->name,
            ] : null,
            'users' => $structure->users->map(fn ($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'function' => $user->function,
                'email' => $user->email,
            ])->values(),
        ]);
    }
}
