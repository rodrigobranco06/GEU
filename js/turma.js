const modalEditar = document.getElementById("modal-editar-turma");
const btnAbrirEditar = document.querySelector(".btn-editar");
const btnFecharEditar = document.getElementById("btn-fechar-modal");

btnAbrirEditar.addEventListener("click", () => {
    modalEditar.style.display = "flex";
});

btnFecharEditar.addEventListener("click", () => {
    modalEditar.style.display = "none";
});

modalEditar.addEventListener("click", (e) => {
    if (e.target === modalEditar) {
        modalEditar.style.display = "none";
    }
});
