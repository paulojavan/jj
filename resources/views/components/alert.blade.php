@if (session('success'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
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
        
        // Tenta mostrar imediatamente
        showAlert();
        
        // Se JJAlert não estiver pronto, aguarda
        if (!window.JJAlertReady) {
            window.addEventListener('JJAlertReady', showAlert);
            setTimeout(showAlert, 1500); // Fallback final
        }
    });
</script>
@endif

@if (session('error'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Alert component: Erro detectado - {{ session('error') }}');
        
        function showAlert() {
            console.log('Tentando mostrar alerta de erro...');
            console.log('Swal disponível:', typeof Swal !== 'undefined');
            console.log('JJAlert disponível:', typeof JJAlert !== 'undefined');
            console.log('JJAlertReady:', window.JJAlertReady);
            
            if (typeof Swal !== 'undefined') {
                if (typeof JJAlert !== 'undefined' && window.JJAlertReady) {
                    console.log('Usando JJAlert');
                    JJAlert.error('Erro!', "{{ session('error') }}");
                } else {
                    console.log('Usando Swal diretamente');
                    Swal.fire({
                        icon: 'error',
                        title: '<strong>Erro!</strong>',
                        html: "{{ session('error') }}",
                        confirmButtonColor: '#dc2626',
                        confirmButtonText: '<i class="fas fa-times mr-2"></i>OK'
                    });
                }
            } else {
                console.error('SweetAlert2 não está disponível!');
            }
        }
        
        // Tenta mostrar imediatamente
        setTimeout(showAlert, 500);
        
        // Tenta novamente após 2 segundos se JJAlert não estiver pronto
        if (!window.JJAlertReady) {
            window.addEventListener('JJAlertReady', showAlert);
            setTimeout(showAlert, 2000);
        }
    });
</script>
@endif

@if (session('warning'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
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
        setTimeout(showAlert, 100);
    });
</script>
@endif

@if (session('info'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
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
        setTimeout(showAlert, 100);
    });
</script>
@endif

@if (session('cpfok'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
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
        setTimeout(showAlert, 100);
    });
</script>
@endif

@if (session('horario_error'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
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
        
        showAlert();
        
        if (!window.JJAlertReady) {
            window.addEventListener('JJAlertReady', showAlert);
            setTimeout(showAlert, 1500);
        }
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
        setTimeout(showAlert, 100);
    });
</script>
@endif
