@extends('layouts.app')

@section('title', $structure->name . ' - Admin')

@section('content')
<div class="p-6">
    <div class="flex justify-between items-start mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ $structure->name }}</h1>
            <p class="text-gray-600 mt-2">{{ $structure->hierarchy_path }} • {{ ucfirst($structure->type) }} • Niv. {{ $structure->level }}</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.structures.edit', $structure) }}" class="inline-flex items-center justify-center bg-primary-500 text-white px-6 py-2.5 rounded-lg shadow hover:bg-primary-600 focus:bg-primary-600 active:bg-primary-700 transition !text-white">
                Modifier
            </a>
            <a href="{{ route('admin.structures.index') }}" class="inline-flex items-center justify-center bg-gray-50 text-gray-700 px-6 py-2.5 rounded-lg shadow hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition">
                Liste
            </a>
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-8 mb-8">
        {{-- Main Info --}}
        <div class="lg:col-span-2">
            <div class="bg-white shadow-xl rounded-2xl p-8 border border-gray-200">
                <h2 class="text-2xl font-bold mb-6">Détails</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="text-sm font-medium text-gray-700 mb-1 block">Code</label>
                        <p class="text-lg font-semibold text-gray-900">{{ $structure->code ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700 mb-1 block">Type</label>
                        <span class="px-4 py-2 bg-primary-50 text-primary-700 rounded-full text-sm font-semibold">
                            {{ ucfirst($structure->type) }}
                        </span>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700 mb-1 block">Niveau</label>
                        <p class="text-lg font-semibold text-gray-900">Niveau {{ $structure->level }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700 mb-1 block">Parent</label>
                        <p class="text-lg font-semibold text-gray-900">
                            {{ $structure->parent?->name ?? 'Aucune (Racine)' }}
                        </p>
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-sm font-medium text-gray-700 mb-1 block">Chemin complet</label>
                        <p class="text-lg font-mono bg-gray-100 p-3 rounded-lg">{{ $structure->hierarchy_path }}</p>
                    </div>
                    @if($structure->description)
                    <div class="md:col-span-2">
                        <label class="text-sm font-medium text-gray-700 mb-1 block">Description</label>
                        <p class="text-gray-900 leading-relaxed">{{ $structure->description }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Children List --}}
        <div>
            <div class="bg-white shadow-xl rounded-2xl p-6 border border-gray-200 h-fit">
                <h3 class="text-xl font-bold mb-4">Enfants ({!! $structure->children->count() !!})</h3>
                <div class="space-y-3 max-h-96 overflow-y-auto">
                    @forelse($structure->children as $child)
                        <div class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 group">
                            <div class="ml-6 w-px bg-gray-300 h-8"></div>
                            <div class="flex-1">
                                <div class="font-semibold text-gray-900">{{ $child->name }}</div>
                                <div class="text-xs text-gray-500">{{ ucfirst($child->type) }}</div>
                            </div>
                            <a href="{{ route('admin.structures.show', $child) }}" class="text-primary-600 hover:text-primary-700 text-sm">Voir</a>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-500">
                            Aucune structure enfant
                        </div>
                    @endforelse
                </div>
                @if($structure->children->isEmpty())
                    <a href="{{ route('admin.structures.create') }}?parent_id={{ $structure->id }}" class="mt-4 inline-flex items-center justify-center w-full bg-primary-500 text-white text-center py-3 rounded-lg hover:bg-primary-600 focus:bg-primary-600 active:bg-primary-700 transition !text-white">
                        + Ajouter enfant
                    </a>
                @endif
            </div>
        </div>
    </div>

    {{-- Audit Timeline --}}
    @include('partials.audit-timeline', ['model' => $structure])

    {{-- Tree Partial --}}
    <div class="mt-12">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold">Arbre complet</h3>
            <a href="{{ route('organigramme.index') }}" class="text-blue-600 hover:text-blue-800 font-medium">Vue organigramme →</a>
        </div>
        @include('structures.partials.tree', ['structures' => $structure->descendantsAndSelf()->get()])
    </div>
</div>
@endsection

