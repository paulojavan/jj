@props([
    'title' => '',
    'subtitle' => '',
    'icon' => '',
    'variant' => 'default', // default, success, error, warning, info
    'padding' => 'p-6'
])

@php
$variants = [
    'default' => 'bg-white border-yellow-200 hover:border-yellow-400',
    'success' => 'bg-green-50 border-green-200 hover:border-green-400',
    'error' => 'bg-red-50 border-red-200 hover:border-red-400',
    'warning' => 'bg-yellow-50 border-yellow-200 hover:border-yellow-400',
    'info' => 'bg-blue-50 border-blue-200 hover:border-blue-400'
];

$iconColors = [
    'default' => 'text-red-600',
    'success' => 'text-green-600',
    'error' => 'text-red-600',
    'warning' => 'text-yellow-600',
    'info' => 'text-blue-600'
];
@endphp

<div class="rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 border-2 {{ $variants[$variant] }} {{ $padding }}">
    @if($title || $icon)
        <div class="flex items-center mb-4">
            @if($icon)
                <i class="{{ $icon }} {{ $iconColors[$variant] }} text-2xl mr-3"></i>
            @endif
            <div>
                @if($title)
                    <h3 class="text-xl font-bold text-red-600">{{ $title }}</h3>
                @endif
                @if($subtitle)
                    <p class="text-gray-600 text-sm">{{ $subtitle }}</p>
                @endif
            </div>
        </div>
    @endif
    
    <div>
        {{ $slot }}
    </div>
</div>