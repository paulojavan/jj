@props([
    'title' => '',
    'subtitle' => '',
    'icon' => '',
    'actions' => null
])

<div class="mb-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-4">
        <div class="flex items-center mb-4 sm:mb-0">
            @if($icon)
                <i class="{{ $icon }} text-red-600 text-3xl mr-3"></i>
            @endif
            <div>
                <h1 class="text-3xl font-bold text-red-600">{{ $title }}</h1>
                @if($subtitle)
                    <p class="text-gray-600 mt-1">{{ $subtitle }}</p>
                @endif
            </div>
        </div>
        
        @if($actions)
            <div class="flex flex-wrap gap-2">
                {{ $actions }}
            </div>
        @endif
    </div>
    
    <!-- Linha decorativa -->
    <div class="h-1 bg-gradient-to-r from-yellow-400 via-yellow-500 to-red-500 rounded-full"></div>
</div>