@props(['percent' => 0, 'label' => null, 'color' => 'green'])

<div class="w-full">
    @if($label)
        <div class="flex justify-between text-sm mb-1">
            <span>{{ $label }}</span>
            <span class="font-mono">{{ number_format($percent, 1) }}%</span>
        </div>
    @endif
    <div class="bg-gray-200 rounded-full h-3 overflow-hidden">
        <div class="bg-linear-to-r from-{{ $color }}-400 to-{{ $color }}-600 h-3 rounded-full transition-all duration-300" style="width: {{ $percent }}%"></div>
    </div>
</div>

