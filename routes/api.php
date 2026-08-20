<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\OrganigrammeController;
use App\Http\Controllers\StructureDetailsController;
use App\Models\Structure;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/my-tasks', [TaskController::class, 'apiMyTasks'])->name('api.my-tasks');
});

Route::middleware(['web', 'auth', 'active_user'])->group(function () {
    // API Structures hiérarchique (id, nom, parent_id, enfants[])
    Route::get('/structures', function () {
        $tree = Structure::with(['children', 'users'])
            ->whereNull('parent_id')
            ->get()
            ->map(function ($structure) {
                return $structure->toTreeArray();
            });

        return response()->json($tree);
    })->name('api.structures');

    // API Organigramme V2 - Liste plate des structures actives
    Route::get('/organigramme-v2', [StructureDetailsController::class, 'index'])->name('api.organigramme-v2');
    Route::get('/structures/{id}/details', [StructureDetailsController::class, 'details'])->name('api.structures.details');
});

