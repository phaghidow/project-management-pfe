<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AttachmentController extends Controller
{
    public function store(Request $request): RedirectResponse
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
        $originalName = $data['name'] ?: $file->getClientOriginalName();
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

        return back()->with('success', 'Fichier ajoute.');
    }

    public function download(Attachment $attachment)
    {
        $this->authorize('download', $attachment);
        $this->authorizeAttachableAccess($attachment->attachable);

        return Storage::disk($attachment->disk)->download($attachment->path, $attachment->name);
    }

    public function destroy(Attachment $attachment): RedirectResponse
    {
        $this->authorize('delete', $attachment);

        if (Storage::disk($attachment->disk)->exists($attachment->path)) {
            Storage::disk($attachment->disk)->delete($attachment->path);
        }

        $attachment->delete();

        return back()->with('success', 'Fichier supprime.');
    }

    private function resolveAttachable(string $type, int $id): Project|Task
    {
        return $type === 'project'
            ? Project::findOrFail($id)
            : Task::findOrFail($id);
    }

    private function authorizeAttachableAccess(Project|Task $attachable): void
    {
        $this->authorize('view', $attachable);
    }
}
