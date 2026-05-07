<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Structure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    /**
     * Display listing of users with search/filter/pagination.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $query = User::with('structure')->orderBy('created_at', 'desc');

        // Search
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filters
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }
        if ($request->filled('structure_id')) {
            $query->where('structure_id', $request->structure_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $users = $query->paginate(15)->withQueryString();
        $structures = Structure::where('is_active', true)->get();
        $roles = User::select('role')->distinct()->pluck('role');

        return view('admin.users.index', compact('users', 'structures', 'roles'));
    }

    /**
     * Show create user form.
     */
    public function create()
    {
        $this->authorize('create', User::class);
        $structures = Structure::where('is_active', true)->get()->sortBy('hierarchy_path');
        $roles = User::ROLES;

        return view('admin.users.create', compact('structures', 'roles'));
    }

    /**
     * Store new user.
     */
    public function store(Request $request)
    {
        $this->authorize('create', User::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'function' => 'nullable|string|max:255',
            'username' => 'required|string|max:50|unique:users,username',
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $email = strtolower((string) $value);
                    $allowedDomains = ['@at.dz', '@algerietelecom.dz'];

                    foreach ($allowedDomains as $domain) {
                        if (str_ends_with($email, $domain)) {
                            return;
                        }
                    }

                    $fail('L\'adresse email doit se terminer par @at.dz ou @algerietelecom.dz.');
                },
            ],
            'password' => 'required|min:8|confirmed',
            'role' => ['required', Rule::in(User::ROLES)],
            'structure_id' => 'nullable|exists:structures,id',
            'status' => 'required|in:en_attente,active,disabled',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        // Forced status history logging on creation if not default
        if ($user->status !== 'en_attente') {
            $user->pendingStatusChangeReason = 'Création utilisateur';
            $user->save();
        }

        return redirect()->route('admin.users.index')
            ->with('success', "Utilisateur '{$user->name}' créé avec succès (statut: {$user->status}).");
    }

    /**
     * Show edit user form.
     */
    public function edit(User $user)
    {
        $this->authorize('update', $user);
        $structures = Structure::where('is_active', true)->get()->sortBy('hierarchy_path');
        $roles = User::ROLES;

        return view('admin.users.edit', compact('user', 'structures', 'roles'));
    }

    /**
     * Update user.
     */
    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'function' => 'nullable|string|max:255',
            'username' => ['required', 'string', 'max:50', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
                function (string $attribute, mixed $value, \Closure $fail) {
                    $email = strtolower((string) $value);
                    $allowedDomains = ['@at.dz', '@algerietelecom.dz'];

                    foreach ($allowedDomains as $domain) {
                        if (str_ends_with($email, $domain)) {
                            return;
                        }
                    }

                    $fail('L\'adresse email doit se terminer par @at.dz ou @algerietelecom.dz.');
                },
            ],
            'role' => ['required', Rule::in(User::ROLES)],
            'structure_id' => 'nullable|exists:structures,id',
            'status' => 'required|in:en_attente,active,disabled',
            'password' => 'nullable|min:8|confirmed',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $oldStatus = $user->status;

        // Set reason before update so bootHasStatusHistory can capture it
        if ($oldStatus !== $validated['status']) {
            $user->pendingStatusChangeReason = 'Mise à jour admin';
        }

        $user->update($validated);

        return redirect()->route('admin.users.index')
            ->with('success', "Utilisateur '{$user->name}' mis à jour.");
    }

    /**
     * Delete/Soft-delete user.
     */
    public function destroy(User $user)
    {
        $this->authorize('delete', $user);
        $name = $user->name;
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', "Utilisateur '$name' supprimé (archivé).");
    }

    /**
     * Display user details and status history.
     */
    public function show(User $user)
    {
        $this->authorize('view', $user);
        $user->load('structure', 'statusHistory.actor');
        return view('admin.users.show', compact('user'));
    }

    /**
     * Toggle user status between active/disabled with forced history logging.
     */
    public function toggleStatus(Request $request, User $user)
    {
        $this->authorize('activate', $user) || $this->authorize('deactivate', $user);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'Impossible de modifier votre propre statut.');
        }

        if ($user->isActive()) {
            $user->pendingStatusChangeReason = 'Désactivation manuelle admin';
            $user->status = User::STATUS_DISABLED;
            $user->save();
            $message = "Utilisateur '{$user->name}' désactivé";
        } else {
            $user->pendingStatusChangeReason = 'Activation manuelle admin';
            $user->status = User::STATUS_ACTIVE;
            $user->save();
            $message = "Utilisateur '{$user->name}' activé";
        }

        return back()->with('success', $message);
    }
}

