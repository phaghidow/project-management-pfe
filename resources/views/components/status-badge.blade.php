@props(['status' => 'draft', 'size' => 'sm'])

@php
$colors = [
    'draft' => 'gray',
    'pending' => 'orange',
    'in_progress' => 'blue',
    'validated' => 'success',
    'completed' => 'success',
    'closed' => 'gray',
    'en_attente' => 'orange',
    'active' => 'success',
    'disabled' => 'red'
];

// Custom color classes for statuses that need exact AT colors
$customClasses = match($status) {
    'validated', 'completed', 'active' => 'bg-[#E8F5E9] text-[#397B44]',
    default => null,
};

$icon = match($status) {
    'draft' => '<svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>',
    'pending' => '<svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
    'in_progress' => '<svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>',
    'validated' => '<svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>',
    'completed' => '<svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
    'closed' => '<svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>',
        'en_attente' => '<svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
        'active' => '<svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>',
        'disabled' => '<svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>',
        default => ''
    };


@endphp

@if($customClasses)
<span class="inline-flex items-center {{ $size === 'sm' ? 'px-2 py-0.5 text-xs' : 'px-3 py-1 text-sm' }} font-medium rounded-full {{ $customClasses }}">
    {!! $icon !!}
    {{ ucfirst(str_replace('_', ' ', $status)) }}
</span>
@else
<span class="inline-flex items-center {{ $size === 'sm' ? 'px-2 py-0.5 text-xs' : 'px-3 py-1 text-sm' }} font-medium rounded-full bg-{{ $colors[$status] ?? 'gray' }}-100 text-{{ $colors[$status] ?? 'gray' }}-800">
    {!! $icon !!}
    {{ ucfirst(str_replace('_', ' ', $status)) }}
</span>
@endif

