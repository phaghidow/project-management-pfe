<x-app-layout>
<div class="page-mobile">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-8">
        <div>
            <a href="{{ route('dashboard') }}" class="inline-flex items-center text-gray-500 hover:text-gray-900 mb-2">
                ← Retour Dashboard
            </a>
            <h1 class="text-3xl font-bold text-gray-900">🎯 Diagramme de Gantt</h1>
            <p class="text-lg text-gray-600 mt-1">Visualisation chronologique des tâches</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-3">
            <a href="{{ route('calendar.index') }}" class="btn-primary px-6 py-3 rounded-xl font-medium shadow-lg transition-all">
                📅 Calendrier
            </a>
        </div>
    </div>

    <div class="bg-white shadow-2xl rounded-3xl p-8 border border-gray-100">
        <gantt-chart data-api-url="/api/tasks-gantt" data-view-mode="Day" data-language="en"></gantt-chart>
    </div>
</div>

@vite(['resources/css/app.css', 'resources/js/app.js'])
@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/frappe-gantt/dist/frappe-gantt.css">
@endpush
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Gantt will load data from /api/tasks-gantt
});
</script>
@endpush>
</x-app-layout>
