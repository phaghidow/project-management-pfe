<div class="bg-white shadow rounded-lg p-6 mb-8">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold flex items-center gap-2">
            <svg class="w-5 h-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Historique des changements ({{ $audits->count() }})
        </h2>
        @if($audits->count() > 10)
            <a href="#" class="text-sm text-blue-600 hover:underline">Voir tout</a>
        @endif
    </div>

    @if($audits->isEmpty())
        <div class="text-center py-12 text-gray-500 bg-gray-50 rounded-lg">
            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            <p>Aucun changement enregistré pour le moment.</p>
        </div>
    @else
        <div class="space-y-4 max-h-96 overflow-y-auto">
            @foreach($audits as $audit)
                <div class="flex items-start space-x-4 p-4 border-l-4 {{ $audit->action === 'create' ? 'border-green-400 bg-green-50' : ($audit->action === 'delete' ? 'border-red-400 bg-red-50' : 'border-blue-400 bg-blue-50') }} rounded-r-lg">
                    
                    <!-- Icon -->
                    <div class="shrink-0">
                        <div class="w-12 h-12 rounded-full bg-white border-2 flex items-center justify-center">
                            @if($audit->action === 'create')
                                <svg class="w-6 h-6 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            @elseif($audit->action === 'delete')
                                <svg class="w-6 h-6 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9zM4 5a2 2 0 012-2h8a2 2 0 012 2v9a3 3 0 00-3 3H7a3 3 0 00-3-3V5z" clip-rule="evenodd" />
                                </svg>
                            @elseif(str_contains($audit->action, 'status') || $audit->action === 'validation')
                                <svg class="w-6 h-6 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            @else
                                <svg class="w-6 h-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                            @endif
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-900">
                                {{ $audit->actor->name ?? 'Système' }}
                            </span>
                            <span class="text-xs text-gray-500">
                                {{ $audit->action_at?->format('d/m/Y H:i') ?? $audit->created_at->format('d/m/Y H:i') }}
                            </span>
                        </div>

                        <!-- Message -->
                        <p class="mt-1 text-sm text-gray-900">
                            @php
                                $message = match($audit->action) {
                                    'create' => 'a créé cet élément',
                                    'update' => 'a modifié cet élément',
                                    'delete' => 'a supprimé cet élément',
                                    'status_change' => 'a changé le statut',
                                    'validation' => 'a validé cet élément',
                                    default => 'a effectué une action'
                                };

                                if ($audit->action === 'status_change' || $audit->action === 'validation') {
                                    $oldStatus = data_get($audit->old_data, 'status', 'N/A');
                                    $newStatus = data_get($audit->new_data, 'status', 'N/A');
                                    $message .= " de '" . $oldStatus . "' à '" . $newStatus . "' ";
                                } elseif ($audit->action === 'update') {
                                    $changes = [];
                                    foreach($audit->new_data ?? [] as $key => $newVal) {
                                        if (isset($audit->old_data[$key]) && $audit->old_data[$key] != $newVal) {
                                            $label = match($key) {
                                                'name' => 'nom',
                                                'status' => 'statut',
                                                'description' => 'description',
                                                default => $key
                                            };
                                            $changes[] = ucfirst($label) . ": '" . $audit->old_data[$key] . "' → '" . $newVal . "' ";
                                        }
                                    }
                                    if (!empty($changes)) $message .= ': ' . implode(', ', $changes);
                                }
                            @endphp
                            {{ $message }}
                        </p>

                        @if($audit->technical_context)
                            <div class="mt-2 text-xs text-gray-500 bg-gray-100 px-3 py-1 rounded-full inline-flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L19.11 19.11"/>
                                </svg>
                                {{ data_get($audit->technical_context, 'url', 'N/A') }}
                            </div>
                        @endif
                    </div>

                </div>
            @endforeach
        </div>
    @endif
</div>