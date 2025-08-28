{{-- Sistema de controle de alertas duplicados --}}
<script>
    // Limpa flags de alertas quando a página carrega
    document.addEventListener('DOMContentLoaded', function() {
        // Reseta flags de alertas para nova página
        window.alertShown_success = false;
        window.alertShown_error = false;
        window.alertShown_warning = false;
        window.alertShown_info = false;
        window.alertShown_cpfok = false;
        window.alertShown_horario_error = false;
        window.alertShown_validation_errors = false;
    });
</script>

@if (session('success'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Previne alertas duplicados
        if (window.alertShown_success) return;
        window.alertShown_success = true;
        
        function showAlert() {
            if (typeof Swal !== 'undefined') {
                if (typeof JJAlert !== 'undefined' && window.JJAlertReady) {
                    JJAlert.success('Sucesso!', "{{ session('success') }}");
                } else {
                    Swal.fire({
                        icon: 'success',
                        title: '<strong>Sucesso!</strong>',
                        html: "{{ session('success') }}",
                        confirmButtonColor: '#16a34a',
                        confirmButtonText: '<i class="fas fa-check mr-2"></i>OK',
                        timer: 3000,
                        timerProgressBar: true
                    });
                }
            }
        }
        
        // Aguarda um pouco para garantir que os scripts estejam carregados
        setTimeout(showAlert, 300);
    });
</script>
@endif

@if (session('error'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Previne alertas duplicados
        if (window.alertShown_error) return;
        window.alertShown_error = true;
        
        function showAlert() {
            if (typeof Swal !== 'undefined') {
                if (typeof JJAlert !== 'undefined' && window.JJAlertReady) {
                    JJAlert.error('Erro!', "{{ session('error') }}");
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: '<strong>Erro!</strong>',
                        html: "{{ session('error') }}",
                        confirmButtonColor: '#dc2626',
                        confirmButtonText: '<i class="fas fa-times mr-2"></i>OK'
                    });
                }
            }
        }
        
        setTimeout(showAlert, 300);
    });
</script>
@endif

@if (session('warning'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Previne alertas duplicados
        if (window.alertShown_warning) return;
        window.alertShown_warning = true;
        
        function showAlert() {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: '<strong>Atenção!</strong>',
                    html: "{{ session('warning') }}",
                    confirmButtonColor: '#f59e0b',
                    confirmButtonText: '<i class="fas fa-exclamation-triangle mr-2"></i>OK'
                });
            }
        }
        setTimeout(showAlert, 300);
    });
</script>
@endif

@if (session('info'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Previne alertas duplicados
        if (window.alertShown_info) return;
        window.alertShown_info = true;
        
        function showAlert() {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'info',
                    title: '<strong>Informação</strong>',
                    html: "{{ session('info') }}",
                    confirmButtonColor: '#3b82f6',
                    confirmButtonText: '<i class="fas fa-info-circle mr-2"></i>OK'
                });
            }
        }
        setTimeout(showAlert, 300);
    });
</script>
@endif

@if (session('cpfok'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Previne alertas duplicados
        if (window.alertShown_cpfok) return;
        window.alertShown_cpfok = true;
        
        function showAlert() {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: '<strong>CPF OK!</strong>',
                    html: 'CPF está pronto para cadastrar!',
                    confirmButtonColor: '#16a34a',
                    confirmButtonText: '<i class="fas fa-check mr-2"></i>OK',
                    timer: 3000,
                    timerProgressBar: true
                });
            }
        }
        setTimeout(showAlert, 300);
    });
</script>
@endif

@if (session('horario_error'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Previne alertas duplicados
        if (window.alertShown_horario_error) return;
        window.alertShown_horario_error = true;
        
        function showAlert() {
            if (typeof Swal !== 'undefined') {
                if (typeof JJAlert !== 'undefined' && window.JJAlertReady) {
                    JJAlert.error('Acesso Negado', "{{ session('horario_error') }}");
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: '<strong>Acesso Negado</strong>',
                        html: "{{ session('horario_error') }}",
                        confirmButtonColor: '#dc2626',
                        confirmButtonText: '<i class="fas fa-times mr-2"></i>OK'
                    });
                }
            }
        }
        
        setTimeout(showAlert, 300);
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
    document.addEventListener('DOMContentLoaded', function() {
        // Previne alertas duplicados
        if (window.alertShown_validation_errors) return;
        window.alertShown_validation_errors = true;
        
        function showAlert() {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: '<strong>Erro de Validação</strong>',
                    html: `
                        <div class="text-left space-y-2">
                            {!! $message !!}
                        </div>
                    `,
                    confirmButtonColor: '#dc2626',
                    confirmButtonText: '<i class="fas fa-times mr-2"></i>OK'
                });
            }
        }
        setTimeout(showAlert, 300);
    });
</script>
@endif
