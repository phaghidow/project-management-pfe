<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Structure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin');
        $this->middleware('can:viewAny,App\\Models\\User')->only('index');
    }

    /**
     * Display listing of users with search/filter/pagination.
     */
    public function index(Request $request)
    {
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
        $roles = [User::ROLE_ADMIN, User::ROLE_CHEF_DEPT, User::ROLE_CHEF_DEPARTEMENT, User::ROLE_CHEF_PROJET, User::ROLE_MEMBRE];

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
            'username' => 'required|string|max:50|unique:users,username',
            'email' => 'required|email|unique:users,email|max:255|regex:/@(?:at|algerietelecom)\.dz$/',
            'password' => 'required|min:8|confirmed',
            'role' => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_CHEF_DEPT, User::ROLE_CHEF_DEPARTEMENT, User::ROLE_CHEF_PROJET, User::ROLE_MEMBRE])],
            'structure_id' => 'nullable|exists:structures,id',
            'status' => 'required|in:en_attente,active,disabled',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

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
        $roles = [User::ROLE_ADMIN, User::ROLE_CHEF_DEPT, User::ROLE_CHEF_DEPARTEMENT, User::ROLE_CHEF_PROJET, User::ROLE_MEMBRE];

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
            'username' => ['required', 'string', 'max:50', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id), 'regex:/@(?:at|algerietelecom)\.dz$/'],
            'role' => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_CHEF_DEPT, User::ROLE_CHEF_DEPARTEMENT, User::ROLE_CHEF_PROJET, User::ROLE_MEMBRE])],
            'structure_id' => 'nullable|exists:structures,id',
            'status' => 'required|in:en_attente,active,disabled',
            'password' => 'nullable|min:8|confirmed',
        ]);

        if ($validated['password']) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $oldStatus = $user->status;
        $user->update($validated);
        
        if ($oldStatus !== $user->status) {
            $user->logStatusChange($oldStatus, $user->status, 'Admin update');
        }

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
     * Toggle user status between active/disabled.
     */
    public function toggleStatus(Request $request, User $user)
    {
        $this->authorize('activate', $user) || $this->authorize('deactivate', $user);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'Impossible de modifier votre propre statut.');
        }

        if ($user->isActive()) {
            $user->deactivate();
            $message = "Utilisateur '{$user->name}' désactivé";
        } else {
            $user->activate();
            $message = "Utilisateur '{$user->name}' activé";
        }

        return back()->with('success', $message);
    }
}

