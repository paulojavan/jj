import './bootstrap';
import 'flowbite';

window.cofirmDelete = function (id) {
    Swal.fire({
        title: "<strong>Tem certeza?</strong>",
        text: "Você não poderá recuperar esse registro!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#dc2626",
        confirmButtonText: "Sim,excluir!",
        cancelButtonText: "Cancelar",
      }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete-form-' + id).submit();
        }

      })
}
