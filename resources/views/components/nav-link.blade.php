@props(['active'])

@php
$classes = ($active ?? false)
            ? 'bg-primary-50 text-primary-700 font-semibold transition duration-150 ease-in-out'
            : 'text-[#1A202C] hover:bg-gray-100 hover:text-primary-600 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>

