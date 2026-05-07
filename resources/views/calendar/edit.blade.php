<x-app-layout>
<div class="page-mobile max-w-3xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('calendar.index') }}" class="inline-flex items-center text-primary-600 hover:text-primary-700 mb-2">
            ← Retour calendrier
        </a>
        <h1 class="text-3xl font-bold text-gray-900">Modifier l'événement</h1>
        <p class="text-gray-600 mt-1">{{ $calendarEvent->title }}</p>
    </div>

    <div class="bg-white shadow-2xl rounded-3xl p-6 border border-[#E2E8F0]">
        <form method="POST" action="{{ route('calendar.manual-events.update', $calendarEvent) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Titre</label>
                <input type="text" name="title" value="{{ old('title', $calendarEvent->title) }}" required class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                @error('title')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <textarea name="description" rows="4" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">{{ old('description', $calendarEvent->description) }}</textarea>
                @error('description')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-[#1A202C] mb-2">Début</label>
                    <input type="datetime-local" name="start_date" value="{{ old('start_date', optional($calendarEvent->start_date)->format('Y-m-d\TH:i')) }}" required class="w-full border-[#E2E8F0] rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500">
                    @error('start_date')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-[#1A202C] mb-2">Fin</label>
                    <input type="datetime-local" name="end_date" value="{{ old('end_date', optional($calendarEvent->end_date)->format('Y-m-d\TH:i')) }}" class="w-full border-[#E2E8F0] rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500">
                    @error('end_date')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-[#1A202C] mb-2">Couleur</label>
                    <input type="color" name="color" value="{{ old('color', $calendarEvent->color ?? '#2E3192') }}" class="h-12 w-24 border border-[#E2E8F0] rounded-md">
                </div>
                <label class="flex items-center gap-2 mt-6">
                    <input type="checkbox" name="all_day" value="1" {{ old('all_day', $calendarEvent->all_day) ? 'checked' : '' }} class="rounded border-[#E2E8F0] text-primary-600 shadow-sm focus:ring-primary-500">
                    <span class="text-sm text-[#1A202C]">Événement toute la journée</span>
                </label>
            </div>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <a href="{{ route('calendar.index') }}" class="btn-light text-center px-6 py-3">Annuler</a>
                <button type="submit" class="inline-flex items-center justify-center bg-primary-500 text-white px-6 py-3 rounded-lg hover:bg-primary-600 focus:bg-primary-600 active:bg-primary-700 transition !text-white">Mettre à jour</button>
            </div>
        </form>
    </div>
</div>
</x-app-layout>
