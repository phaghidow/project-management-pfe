<x-app-layout>
<div class="page-mobile">

    <div class="flex flex-col gap-3 sm:flex-row sm:justify-between sm:items-center mb-4">
        <h1 class="text-xl font-bold">Jalons</h1>

                    <a href="{{ route('milestones.create') }}"
                            class="btn-primary">
                        + Nouveau jalon
                </a>
    </div>

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid gap-4">
        @forelse($milestones as $milestone)
            <div class="bg-white p-4 shadow rounded">
                <div class="flex items-center gap-2 mb-2">
                    <svg class="w-5 h-5 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                    <h2 class="font-bold">{{ $milestone->name }}</h2>
                </div>

                <div class="flex flex-wrap gap-2 mb-2">
                    <x-due-date :date="$milestone->due_date" />
                    <span class="text-xs px-2 py-1 bg-primary-50 text-primary-700 rounded-full">
                        {{ $milestone->tasks->count() }} tâches
                    </span>
                </div>

                <p class="text-sm text-gray-600">
                    Projet:
                    @if($milestone->project)
                        <a href="{{ route('projects.show', $milestone->project) }}" class="font-medium text-primary-600 hover:text-primary-700 hover:underline">{{ $milestone->project->name }}</a>
                    @else
                        <span class="text-gray-500 italic">Projet indisponible</span>
                    @endif
                </p>


                <div class="mt-3 flex flex-wrap gap-2">
                    <a href="{{ route('milestones.show', $milestone) }}"
                       class="text-blue-600 hover:underline text-sm">Voir</a>

                    <a href="{{ route('milestones.edit', $milestone) }}"
                       class="text-yellow-600 hover:underline text-sm">Modifier</a>

                    <form method="POST" action="{{ route('milestones.destroy', $milestone) }}" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:underline text-sm"
                                onclick="return confirm('Confirmer la suppression ?')">Supprimer</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="text-center py-8 text-gray-500">
                Aucun jalon trouvé.
            </div>
        @endforelse
    </div>

</div>
</x-app-layout>

