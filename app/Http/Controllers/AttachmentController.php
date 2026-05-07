<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AttachmentController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'attachable_type' => 'required|in:project,task',
            'attachable_id' => 'required|integer',
            'file' => 'required|file|max:20480',
            'name' => 'nullable|string|max:255',
        ]);

        $attachable = $this->resolveAttachable($data['attachable_type'], (int) $data['attachable_id']);
        $this->authorizeAttachableAccess($attachable);

        $file = $request->file('file');
        $originalName = ($data['name'] ?? null) ?: $file->getClientOriginalName();
        $safeName = Str::slug(pathinfo($originalName, PATHINFO_FILENAME));
        $extension = $file->getClientOriginalExtension();
        $storedName = $safeName . '-' . Str::random(8) . ($extension ? '.' . $extension : '');
        $directory = 'attachments/' . $data['attachable_type'] . '/' . $attachable->id;
        $path = $file->storeAs($directory, $storedName, 'public');

        $attachable->attachments()->create([
            'user_id' => auth()->id(),
            'name' => $originalName,
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'disk' => 'public',
        ]);

        $attachment = $attachable->attachments()->latest()->first();
        $msg = 'Fichier ajouté avec succès';
        return response()->json([
            'message' => $msg,
            'attachment' => $attachment
        ], 201)->header('X-Flash-Success', $msg);
    }

    public function update(Request $request, Attachment $attachment)
    {
        $this->authorize('update', $attachment);

        $data = $request->validate([
            'name' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('attachments')
                    ->where(fn ($query) => $query
                        ->where('attachable_id', $attachment->attachable_id)
                        ->where('attachable_type', $attachment->attachable_type))
                    ->ignore($attachment->id),
            ],
            'file' => 'nullable|file|max:20480',
        ]);

        if (! $request->filled('name') && ! $request->hasFile('file')) {
            $msg = 'Veuillez modifier le nom ou remplacer le fichier.';
            return response()->json([
                'message' => $msg
            ], 422)->header('X-Flash-Error', $msg);
        }

        $attachment->loadMissing('attachable');
        $attachable = $attachment->attachable;

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $originalName = $data['name'] ?? $file->getClientOriginalName();
            $safeName = Str::slug(pathinfo($originalName, PATHINFO_FILENAME));
            $extension = $file->getClientOriginalExtension();
            $storedName = $safeName . '-' . Str::random(8) . ($extension ? '.' . $extension : '');
            $directory = 'attachments/' . ($attachable instanceof Project ? 'project' : 'task') . '/' . $attachable->id;

            if (Storage::disk($attachment->disk)->exists($attachment->path)) {
                Storage::disk($attachment->disk)->delete($attachment->path);
            }

            $path = $file->storeAs($directory, $storedName, $attachment->disk);

            $attachment->fill([
                'name' => $originalName,
                'path' => $path,
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);
        } elseif (array_key_exists('name', $data)) {
            $attachment->name = $data['name'];
        }

        if ($request->hasFile('file') && ! array_key_exists('name', $data)) {
            $attachment->name = $attachment->name ?: $request->file('file')->getClientOriginalName();
        }

        $attachment->save();

        $msg = 'Fichier mis à jour avec succès';
        return response()->json([
            'message' => $msg,
            'attachment' => $attachment
        ])->header('X-Flash-Success', $msg);
    }

    public function download(Attachment $attachment)
    {
        $this->authorize('download', $attachment);
        $this->authorizeAttachableAccess($attachment->attachable);

        return Storage::disk($attachment->disk)->download($attachment->path, $attachment->name);
    }

    public function destroy(Attachment $attachment)
    {
        $this->authorize('delete', $attachment);

        if (Storage::disk($attachment->disk)->exists($attachment->path)) {
            Storage::disk($attachment->disk)->delete($attachment->path);
        }

        $attachment->delete();

        $msg = 'Fichier supprimé avec succès';
        return response()->json([
            'message' => $msg
        ])->header('X-Flash-Success', $msg);
    }

    private function resolveAttachable(string $type, int $id): Project|Task
    {
        return $type === 'project'
            ? Project::findOrFail($id)
            : Task::findOrFail($id);
    }

    private function authorizeAttachableAccess(Project|Task $attachable): void
    {
        $user = auth()->user();

        // Admin always allowed
        if ($user->isAdmin()) {
            return;
        }

        // Project owner or chef de projet / chef de departement handled by policy
        if ($user->isChefProjet() || $user->isChefDepartement()) {
            $this->authorize('view', $attachable);
            return;
        }

        // Members may upload attachments only to tasks they are assigned to
        if ($user->isMembre()) {
            if ($attachable instanceof Task) {
                if ($attachable->users()->where('users.id', $user->id)->exists()) {
                    return;
                }
            }
            abort(403, 'Forbidden');
        }

        // Fallback to policy
        $this->authorize('view', $attachable);
    }
}
