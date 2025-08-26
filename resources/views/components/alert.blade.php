@if (session('success'))
<script>
    document.addEventListener('DOMContentLoaded', () => {
        JJAlert.success('Sucesso!', "{{ session('success') }}");
    });
</script>
@endif

@if (session('error'))
<script>
    document.addEventListener('DOMContentLoaded', () => {
        JJAlert.error('Erro!', "{{ session('error') }}");
    });
</script>
@endif

@if (session('warning'))
<script>
    document.addEventListener('DOMContentLoaded', () => {
        JJAlert.warning('Atenção!', "{{ session('warning') }}");
    });
</script>
@endif

@if (session('info'))
<script>
    document.addEventListener('DOMContentLoaded', () => {
        JJAlert.info('Informação', "{{ session('info') }}");
    });
</script>
@endif

@if (session('cpfok'))
<script>
    document.addEventListener('DOMContentLoaded', () => {
        JJAlert.success('CPF OK!', 'CPF está pronto para cadastrar!');
    });
</script>
@endif

@if ($errors->any())
@php
    $message = '';
    foreach ($errors->all() as $error){
        $message .= '<div class="mb-2 p-2 bg-red-50 rounded border-l-4 border-red-400"><i class="fas fa-exclamation-circle text-red-500 mr-2"></i>' . $error . '</div>';
    }
@endphp
<script>
    document.addEventListener('DOMContentLoaded', () => {
        JJAlert.error('Erro de Validação', `
            <div class="text-left space-y-2">
                {!! $message !!}
            </div>
        `);
    });
</script>
@endif
