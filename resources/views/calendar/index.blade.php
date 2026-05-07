<x-app-layout>
<div class="page-mobile">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-8">
        <div>
            <a href="{{ route('dashboard') }}" class="inline-flex items-center text-gray-500 hover:text-gray-900 mb-2">
                ← Retour Dashboard
            </a>
            <h1 class="text-3xl font-bold text-gray-900">📅 Calendrier</h1>
            <p class="text-lg text-gray-600 mt-1">Tâches et jalons - Vue mensuelle/semaine/jour</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-3">
            <a href="/gantt" class="btn-primary">
                🎯 Gantt
            </a>
        </div>
    </div>

    <div class="bg-white shadow-2xl rounded-3xl p-8 border border-gray-100">
        <div id="calendar-container" class="min-h-175">
            <div id="calendar-app"></div>
        </div>
    </div>

    <div class="grid gap-8 xl:grid-cols-2 mt-8">
        <div class="bg-white shadow-2xl rounded-3xl p-6 border border-gray-100">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Créer un événement</h2>

            <form method="POST" action="{{ route('calendar.manual-events.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Titre</label>
                    <input type="text" name="title" value="{{ old('title') }}" required class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    @error('title')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea name="description" rows="4" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">{{ old('description') }}</textarea>
                    @error('description')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Début</label>
                        <input type="datetime-local" name="start_date" value="{{ old('start_date') }}" required class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        @error('start_date')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Fin</label>
                        <input type="datetime-local" name="end_date" value="{{ old('end_date') }}" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        @error('end_date')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Couleur</label>
                        <input type="color" name="color" value="{{ old('color', '#2563eb') }}" class="h-12 w-24 border border-gray-300 rounded-md">
                    </div>
                    <label class="flex items-center gap-2 mt-6">
                        <input type="checkbox" name="all_day" value="1" {{ old('all_day') ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                        <span class="text-sm text-gray-700">Événement toute la journée</span>
                    </label>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="inline-flex items-center justify-center bg-primary-500 text-white px-5 py-3 rounded-lg hover:bg-primary-600 focus:bg-primary-600 active:bg-primary-700 transition !text-white">Créer</button>
                </div>
            </form>
        </div>

        <div class="bg-white shadow-2xl rounded-3xl p-6 border border-gray-100">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-2xl font-bold text-gray-900">Événements enregistrés</h2>
                <span class="text-sm text-gray-500">{{ isset($events) ? $events->count() : 0 }} élément(s)</span>
            </div>

            <div class="space-y-3 max-h-130 overflow-y-auto pr-1">
                @forelse($events ?? [] as $event)
                    <div class="rounded-xl border p-4 bg-gray-50 flex flex-col gap-3">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="font-semibold text-gray-900">{{ $event->title }}</div>
                                <div class="text-xs text-gray-500">
                                    {{ $event->start_date->format('d/m/Y H:i') }}
                                    @if($event->end_date)
                                        → {{ $event->end_date->format('d/m/Y H:i') }}
                                    @endif
                                    @if($event->all_day)
                                        • Journée entière
                                    @endif
                                </div>
                            </div>
                            <span class="inline-block h-4 w-4 rounded-full" style="background-color: {{ $event->color ?? '#2563eb' }}"></span>
                        </div>

                        @if($event->description)
                            <p class="text-sm text-gray-700 whitespace-pre-line">{{ $event->description }}</p>
                        @endif

                        <div class="flex flex-wrap gap-2 justify-end">
                            <a href="{{ route('calendar.manual-events.edit', $event) }}" class="inline-flex items-center justify-center text-sm bg-white border border-gray-300 px-3 py-2 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition">Modifier</a>
                            <form method="POST" action="{{ route('calendar.manual-events.destroy', $event) }}" onsubmit="return confirm('Supprimer cet événement ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center justify-center text-sm bg-red-600 text-white px-3 py-2 rounded-md hover:bg-red-700 focus:bg-red-700 active:bg-red-800 transition !text-white">Supprimer</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">Aucun événement manuel enregistré.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

@vite(['resources/js/calendar-mount.js'])
@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fullcalendar/core/main.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid/main.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fullcalendar/timegrid/main.min.css">
@endpush
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const todayBtn = document.getElementById('todayBtn')
    if (todayBtn) {
        todayBtn.addEventListener('click', () => {
            const calendarApi = document.querySelector('.fc')?.fc
            if (calendarApi) {
                calendarApi.today()
            }
        })
    }
})
</script>
@endpush
</x-app-layout>

