@props([
    'variant' => 'primary', // primary, secondary, success, danger, warning, info
    'size' => 'md', // sm, md, lg
    'icon' => '',
    'iconPosition' => 'left', // left, right
    'type' => 'button',
    'href' => null,
    'loading' => false,
    'disabled' => false
])

@php
$variants = [
    'primary' => 'bg-yellow-400 text-red-600 hover:bg-yellow-500 focus:ring-yellow-200',
    'secondary' => 'bg-gray-500 text-white hover:bg-gray-600 focus:ring-gray-200',
    'success' => 'bg-green-500 text-white hover:bg-green-600 focus:ring-green-200',
    'danger' => 'bg-red-500 text-white hover:bg-red-600 focus:ring-red-200',
    'warning' => 'bg-orange-500 text-white hover:bg-orange-600 focus:ring-orange-200',
    'info' => 'bg-blue-500 text-white hover:bg-blue-600 focus:ring-blue-200'
];

$sizes = [
    'sm' => 'px-3 py-2 text-sm',
    'md' => 'px-6 py-3 text-base',
    'lg' => 'px-8 py-4 text-lg'
];

$classes = implode(' ', [
    $variants[$variant],
    $sizes[$size],
    'font-semibold rounded-lg transition-all duration-300 shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2',
    $disabled || $loading ? 'opacity-50 cursor-not-allowed' : 'transform hover:-translate-y-0.5'
]);
@endphp

@if($href)
    <a href="{{ $href }}" class="{{ $classes }} inline-flex items-center justify-center" {{ $attributes }}>
        @if($icon && $iconPosition === 'left')
            <i class="{{ $icon }} mr-2"></i>
        @endif
        
        {{ $slot }}
        
        @if($icon && $iconPosition === 'right')
            <i class="{{ $icon }} ml-2"></i>
        @endif
    </a>
@else
    <button type="{{ $type }}" class="{{ $classes }} inline-flex items-center justify-center" 
            {{ $disabled || $loading ? 'disabled' : '' }} {{ $attributes }}>
        @if($loading)
            <svg class="animate-spin -ml-1 mr-3 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Processando...
        @else
            @if($icon && $iconPosition === 'left')
                <i class="{{ $icon }} mr-2"></i>
            @endif
            
            {{ $slot }}
            
            @if($icon && $iconPosition === 'right')
                <i class="{{ $icon }} ml-2"></i>
            @endif
        @endif
    </button>
@endif