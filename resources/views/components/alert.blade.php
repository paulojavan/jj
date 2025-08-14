@if (session('success'))
<script>
    document.addEventListener('DOMContentLoaded', () => {
        Swal.fire({
            title: "<strong>Sucesso!</strong>",
            html: "{{ session('success') }}",
            icon: "success",
            confirmButtonColor: '#16a34a',
            confirmButtonText: '<i class="fas fa-check mr-2"></i>OK',
            customClass: {
                popup: 'swal2-popup-custom',
                title: 'swal2-title-success',
                confirmButton: 'swal2-confirm-custom'
            },
            buttonsStyling: false
        });
    });
</script>
@endif

@if (session('error'))
<script>
    document.addEventListener('DOMContentLoaded', () => {
        Swal.fire({
            title: "<strong>Erro!</strong>",
            html: "{{ session('error') }}",
            icon: "error",
            confirmButtonColor: '#dc2626',
            confirmButtonText: '<i class="fas fa-times mr-2"></i>OK',
            customClass: {
                popup: 'swal2-popup-custom',
                title: 'swal2-title-error',
                confirmButton: 'swal2-confirm-error'
            },
            buttonsStyling: false
        });
    });
</script>
@endif

@if (session('cpfok'))
<script>
    document.addEventListener('DOMContentLoaded', () => {
        Swal.fire({
            title: "CPF OK!",
            text: "CPF está pronto para cadastrar!",
            icon: "success"
            });
    });
</script>
@endif

@if ($errors->any())
@php
    $message = '';
    foreach ($errors->all() as $error){
        $message .=$error.'<br>';
    }
@endphp
<script>
    document.addEventListener('DOMContentLoaded', () => {
        Swal.fire({
            title: "Erro!",
            html: "{!! $message !!}",
            icon: "error"
            });
    });
</script>

{{--
<div class="alert-error">
        @foreach ($errors->all() as $error)
            {{ $error }}
        @endforeach
    </div>
--}}

@endif

<!-- Estilos para SweetAlert2 -->
<style>
.swal2-popup-custom {
    border-radius: 15px !important;
    padding: 2rem !important;
}

.swal2-title-success {
    color: #16a34a !important;
    font-size: 1.5rem !important;
}

.swal2-title-error {
    color: #dc2626 !important;
    font-size: 1.5rem !important;
}

.swal2-confirm-custom {
    background-color: #16a34a !important;
    color: white !important;
    border: none !important;
    border-radius: 8px !important;
    padding: 12px 24px !important;
    font-weight: 600 !important;
    transition: all 0.3s ease !important;
}

.swal2-confirm-custom:hover {
    background-color: #15803d !important;
    transform: translateY(-1px) !important;
    box-shadow: 0 4px 12px rgba(22, 163, 74, 0.4) !important;
}

.swal2-confirm-error {
    background-color: #dc2626 !important;
    color: white !important;
    border: none !important;
    border-radius: 8px !important;
    padding: 12px 24px !important;
    font-weight: 600 !important;
    transition: all 0.3s ease !important;
}

.swal2-confirm-error:hover {
    background-color: #b91c1c !important;
    transform: translateY(-1px) !important;
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.4) !important;
}
</style>
