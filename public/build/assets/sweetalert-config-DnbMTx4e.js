const e={customClass:{popup:"swal2-popup-jj",title:"swal2-title-jj",content:"swal2-content-jj",confirmButton:"swal2-confirm-jj",cancelButton:"swal2-cancel-jj"},buttonsStyling:!1,showClass:{popup:"animate__animated animate__fadeInDown animate__faster"},hideClass:{popup:"animate__animated animate__fadeOutUp animate__faster"}},a={success:{...e,icon:"success",confirmButtonColor:"#16a34a",confirmButtonText:'<i class="fas fa-check mr-2"></i>OK',timer:3e3,timerProgressBar:!0,customClass:{...e.customClass,confirmButton:"swal2-confirm-success"}},error:{...e,icon:"error",confirmButtonColor:"#dc2626",confirmButtonText:'<i class="fas fa-times mr-2"></i>OK',customClass:{...e.customClass,confirmButton:"swal2-confirm-error"}},warning:{...e,icon:"warning",confirmButtonColor:"#f59e0b",confirmButtonText:'<i class="fas fa-exclamation-triangle mr-2"></i>OK',customClass:{...e.customClass,confirmButton:"swal2-confirm-warning"}},info:{...e,icon:"info",confirmButtonColor:"#3b82f6",confirmButtonText:'<i class="fas fa-info-circle mr-2"></i>OK',customClass:{...e.customClass,confirmButton:"swal2-confirm-info"}},question:{...e,icon:"question",showCancelButton:!0,confirmButtonColor:"#16a34a",cancelButtonColor:"#dc2626",confirmButtonText:'<i class="fas fa-check mr-2"></i>Sim',cancelButtonText:'<i class="fas fa-times mr-2"></i>Cancelar',reverseButtons:!0,customClass:{...e.customClass,confirmButton:"swal2-confirm-success",cancelButton:"swal2-cancel-error"}}};window.JJAlert={success:(t,s="")=>Swal.fire({title:`<strong>${t}</strong>`,html:s,...a.success}),error:(t,s="")=>Swal.fire({title:`<strong>${t}</strong>`,html:s,...a.error}),warning:(t,s="")=>Swal.fire({title:`<strong>${t}</strong>`,html:s,...a.warning}),info:(t,s="")=>Swal.fire({title:`<strong>${t}</strong>`,html:s,...a.info}),confirm:(t,s="",n="Sim",o="Cancelar")=>Swal.fire({title:`<strong>${t}</strong>`,html:s,confirmButtonText:`<i class="fas fa-check mr-2"></i>${n}`,cancelButtonText:`<i class="fas fa-times mr-2"></i>${o}`,...a.question}),delete:(t="Excluir item?",s="Esta ação não pode ser desfeita!")=>Swal.fire({title:`<strong>${t}</strong>`,html:`<div class="text-center">
                <div class="mb-4 p-4 bg-red-50 rounded-lg border border-red-200">
                    <i class="fas fa-exclamation-triangle text-red-500 text-3xl mb-2"></i>
                    <p class="text-gray-700">${s}</p>
                </div>
            </div>`,icon:"warning",showCancelButton:!0,confirmButtonColor:"#dc2626",cancelButtonColor:"#6b7280",confirmButtonText:'<i class="fas fa-trash mr-2"></i>Sim, excluir!',cancelButtonText:'<i class="fas fa-times mr-2"></i>Cancelar',reverseButtons:!0,...e}),loading:(t="Processando...",s="Aguarde um momento")=>Swal.fire({title:t,html:s,allowOutsideClick:!1,allowEscapeKey:!1,showConfirmButton:!1,didOpen:()=>{Swal.showLoading()},...e})};window.JJAlert.finalizarCompra=(t,s)=>Swal.fire({title:"<strong>Finalizar Compra</strong>",html:`
            <div class="text-left">
                <div class="mb-4 p-4 bg-yellow-50 rounded-lg border border-yellow-200">
                    <div class="flex justify-between items-center mb-2">
                        <span class="font-semibold text-gray-700">Total de itens:</span>
                        <span class="font-bold text-red-600">${t}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="font-semibold text-gray-700">Valor total:</span>
                        <span class="font-bold text-green-600 text-lg">${s}</span>
                    </div>
                </div>
                <p class="text-gray-600 text-center">
                    <i class="fas fa-exclamation-triangle text-yellow-500 mr-2"></i>
                    Esta ação não pode ser desfeita!
                </p>
            </div>
        `,icon:"question",showCancelButton:!0,confirmButtonColor:"#16a34a",cancelButtonColor:"#dc2626",confirmButtonText:'<i class="fas fa-check mr-2"></i>Sim, finalizar!',cancelButtonText:'<i class="fas fa-times mr-2"></i>Cancelar',reverseButtons:!0,...e});
