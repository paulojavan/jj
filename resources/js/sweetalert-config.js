/**
 * Configurações padronizadas para SweetAlert2
 * JJ Calçados - Sistema de Gestão
 */

// Previne execução múltipla
if (window.JJAlertConfigLoaded) {
    console.log('JJAlert config já foi carregado');
} else {
    window.JJAlertConfigLoaded = true;

// Função para aguardar SweetAlert2 estar disponível
function waitForSwal(callback, maxAttempts = 50) {
    let attempts = 0;
    const checkSwal = () => {
        if (typeof Swal !== 'undefined') {
            callback();
        } else if (attempts < maxAttempts) {
            attempts++;
            setTimeout(checkSwal, 100);
        } else {
            console.error('SweetAlert2 não pôde ser carregado após', maxAttempts, 'tentativas');
        }
    };
    checkSwal();
}

// Inicializa quando SweetAlert2 estiver disponível
waitForSwal(() => {
    // Configurações padrão para todos os alertas
    const defaultSwalConfig = {
    customClass: {
        popup: 'swal2-popup-jj',
        title: 'swal2-title-jj',
        content: 'swal2-content-jj',
        confirmButton: 'swal2-confirm-jj',
        cancelButton: 'swal2-cancel-jj'
    },
    buttonsStyling: false,
    showClass: {
        popup: 'animate__animated animate__fadeInDown animate__faster'
    },
    hideClass: {
        popup: 'animate__animated animate__fadeOutUp animate__faster'
    }
};

// Configurações específicas por tipo
const swalConfigs = {
    success: {
        ...defaultSwalConfig,
        icon: 'success',
        confirmButtonColor: '#16a34a',
        confirmButtonText: '<i class="fas fa-check mr-2"></i>OK',
        timer: 3000,
        timerProgressBar: true,
        customClass: {
            ...defaultSwalConfig.customClass,
            confirmButton: 'swal2-confirm-success'
        }
    },
    
    error: {
        ...defaultSwalConfig,
        icon: 'error',
        confirmButtonColor: '#dc2626',
        confirmButtonText: '<i class="fas fa-times mr-2"></i>OK',
        customClass: {
            ...defaultSwalConfig.customClass,
            confirmButton: 'swal2-confirm-error'
        }
    },
    
    warning: {
        ...defaultSwalConfig,
        icon: 'warning',
        confirmButtonColor: '#f59e0b',
        confirmButtonText: '<i class="fas fa-exclamation-triangle mr-2"></i>OK',
        customClass: {
            ...defaultSwalConfig.customClass,
            confirmButton: 'swal2-confirm-warning'
        }
    },
    
    info: {
        ...defaultSwalConfig,
        icon: 'info',
        confirmButtonColor: '#3b82f6',
        confirmButtonText: '<i class="fas fa-info-circle mr-2"></i>OK',
        customClass: {
            ...defaultSwalConfig.customClass,
            confirmButton: 'swal2-confirm-info'
        }
    },
    
    question: {
        ...defaultSwalConfig,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#16a34a',
        cancelButtonColor: '#dc2626',
        confirmButtonText: '<i class="fas fa-check mr-2"></i>Sim',
        cancelButtonText: '<i class="fas fa-times mr-2"></i>Cancelar',
        reverseButtons: true,
        customClass: {
            ...defaultSwalConfig.customClass,
            confirmButton: 'btn-green w-full sm:w-auto',
            cancelButton: 'btn-red w-full sm:w-auto'
        }
    }
};

// Funções utilitárias para alertas padronizados
window.JJAlert = {
    success: (title, text = '') => {
        return Swal.fire({
            title: `<strong>${title}</strong>`,
            html: text,
            ...swalConfigs.success
        });
    },
    
    error: (title, text = '') => {
        return Swal.fire({
            title: `<strong>${title}</strong>`,
            html: text,
            ...swalConfigs.error
        });
    },
    
    warning: (title, text = '') => {
        return Swal.fire({
            title: `<strong>${title}</strong>`,
            html: text,
            ...swalConfigs.warning
        });
    },
    
    info: (title, text = '') => {
        return Swal.fire({
            title: `<strong>${title}</strong>`,
            html: text,
            ...swalConfigs.info
        });
    },
    
    confirm: (title, text = '', confirmText = 'Sim', cancelText = 'Cancelar') => {
        return Swal.fire({
            title: `<strong>${title}</strong>`,
            html: text,
            confirmButtonText: `<i class="fas fa-check mr-2"></i>${confirmText}`,
            cancelButtonText: `<i class="fas fa-times mr-2"></i>${cancelText}`,
            ...swalConfigs.question
        });
    },
    
    delete: (title = 'Excluir item?', text = 'Esta ação não pode ser desfeita!') => {
        return Swal.fire({
            title: `<strong>${title}</strong>`,
            html: `<div class="text-center">
                <div class="mb-4 p-4 bg-red-50 rounded-lg border border-red-200">
                    <i class="fas fa-exclamation-triangle text-red-500 text-3xl mb-2"></i>
                    <p class="text-gray-700">${text}</p>
                </div>
            </div>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: '<i class="fas fa-trash mr-2"></i>Sim, excluir!',
            cancelButtonText: '<i class="fas fa-times mr-2"></i>Cancelar',
            reverseButtons: true,
            customClass: {
                ...defaultSwalConfig.customClass,
                confirmButton: 'swal2-confirm-error',
                cancelButton: 'swal2-cancel-error'
            },
            buttonsStyling: false
        });
    },
    
    loading: (title = 'Processando...', text = 'Aguarde um momento') => {
        return Swal.fire({
            title: title,
            html: text,
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            },
            ...defaultSwalConfig
        });
    }
};

// Função para finalizar compra com detalhes
window.JJAlert.finalizarCompra = (totalItens, valorTotal) => {
    return Swal.fire({
        title: '<strong>Finalizar Compra</strong>',
        html: `
            <div class="text-left">
                <div class="mb-4 p-4 bg-yellow-50 rounded-lg border border-yellow-200">
                    <div class="flex justify-between items-center mb-2">
                        <span class="font-semibold text-gray-700">Total de itens:</span>
                        <span class="font-bold text-red-600">${totalItens}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="font-semibold text-gray-700">Valor total:</span>
                        <span class="font-bold text-green-600 text-lg">${valorTotal}</span>
                    </div>
                </div>
                <p class="text-gray-600 text-center">
                    <i class="fas fa-exclamation-triangle text-yellow-500 mr-2"></i>
                    Esta ação não pode ser desfeita!
                </p>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-check mr-2"></i>Sim, finalizar!',
        cancelButtonText: '<i class="fas fa-times mr-2"></i>Cancelar',
        reverseButtons: true,
        customClass: {
            popup: 'swal2-popup-jj',
            title: 'swal2-title-jj',
            confirmButton: 'swal2-confirm-success',
            cancelButton: 'swal2-cancel-error'
        },
        buttonsStyling: false
    });
};

    // Marca que JJAlert está pronto
    window.JJAlertReady = true;
    
    // Dispara evento personalizado para indicar que JJAlert está pronto (apenas uma vez)
    if (!window.JJAlertEventDispatched) {
        window.JJAlertEventDispatched = true;
        window.dispatchEvent(new CustomEvent('JJAlertReady'));
    }
});

} // Fim da proteção contra execução múltipla