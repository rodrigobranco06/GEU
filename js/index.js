const modalCriar = document.getElementById("modal-criar-turma");
const btnAbrirCriar = document.querySelector(".btn-criar-turma");
const btnFecharCriar = document.getElementById("btn-fechar-modal");

btnAbrirCriar.addEventListener("click", () => {
    modalCriar.style.display = "flex";
});

btnFecharCriar.addEventListener("click", () => {
    modalCriar.style.display = "none";
});

modalCriar.addEventListener("click", (e) => {
    if (e.target === modalCriar) {
        modalCriar.style.display = "none";
    }
});

