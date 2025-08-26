@props([
    'headers' => [],
    'responsive' => true,
    'striped' => true,
    'hover' => true
])

<div class="{{ $responsive ? 'overflow-x-auto' : '' }} bg-white shadow-lg rounded-xl border border-yellow-200">
    <table class="min-w-full border-collapse w-full">
        @if(count($headers) > 0)
            <thead class="bg-gradient-to-r from-yellow-400 to-yellow-500 text-red-600 uppercase text-sm leading-normal">
                <tr>
                    @foreach($headers as $header)
                        <th class="py-3 px-6 text-left {{ $header['class'] ?? '' }}">
                            {{ $header['label'] ?? $header }}
                        </th>
                    @endforeach
                </tr>
            </thead>
        @endif
        
        <tbody class="text-gray-700 text-sm font-light">
            {{ $slot }}
        </tbody>
    </table>
</div>

@push('styles')
<style>
    .table-row {
        @apply border-b border-gray-200 transition-colors;
        @if($hover) @apply hover:bg-yellow-50; @endif
    }
    
    @if($striped)
    .table-row:nth-child(even) {
        @apply bg-gray-50;
    }
    @endif
    
    .table-cell {
        @apply py-3 px-6;
    }
    
    .table-actions {
        @apply py-3 px-6 text-center;
    }
    
    .table-actions > * {
        @apply inline-block mr-2 last:mr-0;
    }
</style>
@endpush