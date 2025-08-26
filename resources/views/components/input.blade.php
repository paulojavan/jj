@props([
    'label' => '',
    'name' => '',
    'type' => 'text',
    'value' => '',
    'placeholder' => '',
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'icon' => '',
    'iconPosition' => 'left',
    'help' => '',
    'error' => '',
    'mask' => '', // cpf, telefone, money, etc.
    'options' => [], // para select
    'rows' => 3 // para textarea
])

<div class="mb-4">
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-semibold text-red-600 mb-2">
            @if($icon && $iconPosition === 'left')
                <i class="{{ $icon }} mr-1"></i>
            @endif
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
            @if($icon && $iconPosition === 'right')
                <i class="{{ $icon }} ml-1"></i>
            @endif
        </label>
    @endif
    
    <div class="relative">
        @if($icon && $iconPosition === 'left' && !in_array($type, ['select', 'textarea']))
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="{{ $icon }} text-yellow-600"></i>
            </div>
        @endif
        
        @if($type === 'select')
            <select name="{{ $name }}" id="{{ $name }}" 
                    class="form-input {{ $icon && $iconPosition === 'left' ? 'pl-10' : '' }} {{ $error ? 'border-red-500' : '' }}"
                    {{ $required ? 'required' : '' }}
                    {{ $disabled ? 'disabled' : '' }}
                    {{ $attributes }}>
                @if($placeholder)
                    <option value="">{{ $placeholder }}</option>
                @endif
                @foreach($options as $optionValue => $optionLabel)
                    <option value="{{ $optionValue }}" {{ $value == $optionValue ? 'selected' : '' }}>
                        {{ $optionLabel }}
                    </option>
                @endforeach
            </select>
        @elseif($type === 'textarea')
            <textarea name="{{ $name }}" id="{{ $name }}" rows="{{ $rows }}"
                      class="form-input {{ $error ? 'border-red-500' : '' }}"
                      placeholder="{{ $placeholder }}"
                      {{ $required ? 'required' : '' }}
                      {{ $disabled ? 'disabled' : '' }}
                      {{ $readonly ? 'readonly' : '' }}
                      {{ $attributes }}>{{ $value }}</textarea>
        @elseif($type === 'file')
            <input type="file" name="{{ $name }}" id="{{ $name }}"
                   class="form-file {{ $error ? 'border-red-500' : '' }}"
                   {{ $required ? 'required' : '' }}
                   {{ $disabled ? 'disabled' : '' }}
                   {{ $attributes }}>
        @else
            <input type="{{ $type }}" name="{{ $name }}" id="{{ $name }}"
                   class="form-input {{ $icon && $iconPosition === 'left' ? 'pl-10' : '' }} {{ $icon && $iconPosition === 'right' ? 'pr-10' : '' }} {{ $error ? 'border-red-500' : '' }} {{ $mask ? 'mask-' . $mask : '' }}"
                   value="{{ $value }}"
                   placeholder="{{ $placeholder }}"
                   {{ $required ? 'required' : '' }}
                   {{ $disabled ? 'disabled' : '' }}
                   {{ $readonly ? 'readonly' : '' }}
                   {{ $attributes }}>
        @endif
        
        @if($icon && $iconPosition === 'right' && !in_array($type, ['select', 'textarea']))
            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                <i class="{{ $icon }} text-yellow-600"></i>
            </div>
        @endif
    </div>
    
    @if($help)
        <p class="mt-1 text-sm text-gray-500">
            <i class="fas fa-info-circle mr-1"></i>{{ $help }}
        </p>
    @endif
    
    @if($error)
        <p class="mt-1 text-sm text-red-600">
            <i class="fas fa-exclamation-circle mr-1"></i>{{ $error }}
        </p>
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Máscara para CPF
    const cpfInputs = document.querySelectorAll('.mask-cpf');
    cpfInputs.forEach(function(input) {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 11) value = value.slice(0, 11);
            
            if (value.length > 0) {
                if (value.length > 3) value = value.substring(0, 3) + '.' + value.substring(3);
                if (value.length > 7) value = value.substring(0, 7) + '.' + value.substring(7);
                if (value.length > 11) value = value.substring(0, 11) + '-' + value.substring(11);
            }
            
            e.target.value = value;
        });
    });
    
    // Máscara para telefone
    const telefoneInputs = document.querySelectorAll('.mask-telefone');
    telefoneInputs.forEach(function(input) {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 11) value = value.slice(0, 11);
            
            if (value.length > 0) {
                value = '(' + value;
                if (value.length > 3) value = value.substring(0, 3) + ') ' + value.substring(3);
                if (value.length > 10) value = value.substring(0, 10) + '-' + value.substring(10);
            }
            
            e.target.value = value;
        });
    });
    
    // Máscara para dinheiro
    const moneyInputs = document.querySelectorAll('.mask-money');
    moneyInputs.forEach(function(input) {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            value = (value / 100).toFixed(2) + '';
            value = value.replace('.', ',');
            value = value.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
            e.target.value = 'R$ ' + value;
        });
    });
});
</script>
@endpush