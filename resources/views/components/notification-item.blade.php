@props(['notification', 'compact' => false])

@php
    // Determine icon based on notification type
    $icon = 'ℹ️';
    if (str_contains($notification->type ?? '', 'task')) $icon = '📋';
    elseif (str_contains($notification->type ?? '', 'milestone')) $icon = '📌';
    elseif (str_contains($notification->type ?? '', 'project')) $icon = '👥';
    elseif (str_contains($notification->type ?? '', 'status')) $icon = '🔄';
    
    $canAccess = $notification->can_access ?? false;
    $accessReason = $notification->access_reason ?? '';
    $relatedLink = $notification->related_type && $notification->related_id && $canAccess 
        ? "/{$notification->related_type}s/{$notification->related_id}" 
        : null;
@endphp

@if($compact)
    {{-- Compact version for dropdown --}}
    <div class="p-3 border-b hover:bg-gray-100 cursor-pointer transition">
        <div class="flex items-start">
            <span class="mr-2">{{ $icon }}</span>
            <div class="flex-1">
                <div class="font-semibold text-sm text-[#1A202C]">{{ $notification->title }}</div>
                <div class="text-xs text-slate-700 mt-1">{{ $notification->message ?? '' }}</div>
                @if($notification->related_type && !$canAccess)
                    <div class="text-xs text-red-600 mt-1">⛔ {{ $accessReason ?? 'Accès refusé' }}</div>
                @endif
            </div>
        </div>
    </div>
@else
    {{-- Full version for notification page --}}
    <div class="p-6 hover:bg-gray-50 transition {{ !$notification->read_at ? 'bg-blue-50 border-blue-200 border-l-4' : '' }}">
        <div class="flex items-start space-x-3">
            <div class="flex-shrink-0">
                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center text-sm">
                    {{ $icon }}
                </div>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900">{{ $notification->title }}</p>
                <p class="mt-1 text-sm text-gray-500">{{ $notification->message }}</p>
                
                @if($relatedLink)
                    {{-- User has access to the related resource --}}
                    <a href="{{ $relatedLink }}" class="text-blue-500 hover:underline text-sm block mt-2">
                        → Voir l'élément
                    </a>
                @elseif($notification->related_type && !$canAccess)
                    {{-- User doesn't have access to the related resource --}}
                    <div class="bg-red-50 border border-red-200 rounded p-2 mt-2">
                        <p class="text-sm text-red-700">
                            <span class="font-semibold">⛔ Accès refusé</span><br>
                            <span class="text-xs">{{ $accessReason ?? 'Vous n\'avez pas les permissions nécessaires pour accéder à cette ressource.' }}</span>
                        </p>
                    </div>
                @endif
                
                <p class="mt-2 text-xs text-gray-400">{{ $notification->created_at->toLocaleString('fr_FR') }}</p>
            </div>
            
            @if(!$notification->read_at)
                <button onclick="markAsRead({{ $notification->id }})" 
                        class="ml-4 text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full hover:bg-green-200 whitespace-nowrap">
                    Marquer comme lu
                </button>
            @endif
        </div>
    </div>
@endif
