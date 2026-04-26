@props(['date', 'format' => 'd/m/Y', 'size' => 'sm'])

@if($date)
    @php
        // Convert string to Carbon if needed
        $dateCarbon = is_string($date) ? \Carbon\Carbon::parse($date) : $date;
        $days = now()->diffInDays($dateCarbon, false);
        $color = $days < 0 ? 'red' : ($days <= 7 ? 'orange' : 'green');
    @endphp
    <span class="inline-flex items-center {{ $size === 'sm' ? 'px-2 py-0.5 text-xs' : 'px-3 py-1 text-sm' }} font-medium rounded-full bg-{{ $color }}-100 text-{{ $color }}-800">
        <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
        </svg>
        {{ $dateCarbon->format($format) }}
        @if($days < 0)(expirée)@elseif($days <= 7)(proche)@endif
    </span>
@else
    <span class="text-xs text-gray-500">-</span>
@endif

