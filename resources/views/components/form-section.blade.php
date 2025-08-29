@props([
    'title' => '',
    'subtitle' => ''
])

<div class="bg-white shadow-lg rounded-xl p-6 border border-yellow-200 mb-6">
    @if($title)
        <div class="mb-6">
            <h3 class="text-xl font-bold text-red-600 mb-2">{{ $title }}</h3>
            @if($subtitle)
                <p class="text-gray-600 text-sm">{{ $subtitle }}</p>
            @endif
            <div class="h-0.5 bg-gradient-to-r from-yellow-400 to-red-500 rounded-full mt-3 w-16"></div>
        </div>
    @endif
    
    <div class="space-y-4">
        {{ $slot }}
    </div>
</div>