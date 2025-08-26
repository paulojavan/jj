@props([
    'action' => '',
    'method' => 'POST',
    'enctype' => null,
    'title' => '',
    'subtitle' => ''
])

<div class="bg-white shadow-lg rounded-xl p-6 border border-yellow-200">
    @if($title)
        <div class="mb-6 text-center">
            <h2 class="text-2xl font-bold text-red-600 mb-2">{{ $title }}</h2>
            @if($subtitle)
                <p class="text-gray-600">{{ $subtitle }}</p>
            @endif
            <div class="h-1 bg-gradient-to-r from-yellow-400 to-red-500 rounded-full mt-4 mx-auto w-24"></div>
        </div>
    @endif
    
    <form action="{{ $action }}" method="{{ $method }}" {{ $enctype ? "enctype={$enctype}" : '' }} {{ $attributes }}>
        @if($method !== 'GET')
            @csrf
        @endif
        
        @if(in_array(strtoupper($method), ['PUT', 'PATCH', 'DELETE']))
            @method($method)
        @endif
        
        {{ $slot }}
    </form>
</div>