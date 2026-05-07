<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Task;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * Store a new comment on a task.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'task_id' => 'required|integer|exists:tasks,id',
            'content' => 'required|string|max:2000',
        ]);

        $task = Task::findOrFail($data['task_id']);
        // Do not rely on TaskPolicy::view here because members may be allowed
        // to comment on tasks they are assigned to even if TaskPolicy::view denies full view.

        // Check user role-based access
        $user = auth()->user();
        
        if ($user->isMembre()) {
                if (!$task->users()->where('users.id', $user->id)->exists()) {
                    $msg = 'Vous n\'êtes pas assigné à cette tâche.';
                    return response()->json(['message' => $msg], 403)->header('X-Flash-Error', $msg);
            }
        }

        if ($user->isChefDepartement()) {
            $structureIds = $task->milestone?->project?->user?->structure?->getHierarchyIds() ?? [];
                if (!in_array($user->structure_id, $structureIds, true)) {
                    $msg = 'Cette tâche ne fait pas partie de votre département.';
                    return response()->json(['message' => $msg], 403)->header('X-Flash-Error', $msg);
            }
        }

        if ($user->isChefProjet()) {
                if ($task->milestone?->project?->user_id !== $user->id) {
                    $msg = 'Vous n\'êtes pas le responsable de ce projet.';
                    return response()->json(['message' => $msg], 403)->header('X-Flash-Error', $msg);
            }
        }

            $comment = Comment::create([
            'user_id' => $user->id,
            'task_id' => $task->id,
            'content' => $data['content'],
        ]);

            $msg = 'Commentaire ajouté avec succès';
            return response()->json([
                'message' => $msg,
                'comment' => $comment->load('user')
            ], 201)->header('X-Flash-Success', $msg);
    }

    /**
     * Update a comment.
     */
    public function update(Request $request, Comment $comment)
    {
        $this->authorize('update', $comment);

        $data = $request->validate([
            'content' => 'required|string|max:2000',
        ]);

        $comment->update($data);

        $msg = 'Commentaire mis à jour avec succès';
        return response()->json([
            'message' => $msg,
            'comment' => $comment->load('user')
        ])->header('X-Flash-Success', $msg);
    }

    /**
     * Delete a comment.
     */
    public function destroy(Comment $comment)
    {
        $this->authorize('delete', $comment);

        $comment->delete();

        $msg = 'Commentaire supprimé avec succès';
        return response()->json([
            'message' => $msg
        ])->header('X-Flash-Success', $msg);
    }
}
