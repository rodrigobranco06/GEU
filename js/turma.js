document.addEventListener("DOMContentLoaded", () => {
    // -------- MODAL EDITAR TURMA --------
    const modalEditar = document.getElementById("modal-editar-turma");
    const btnAbrirEditar = document.querySelector(".btn-editar");
    // Selecionamos o botão de cancelar/voltar dentro do modal de edição
    const btnFecharEditar = document.querySelector("#modal-editar-turma .modal-btn.voltar");

    if (modalEditar && btnAbrirEditar) {
        btnAbrirEditar.addEventListener("click", () => {
            modalEditar.style.display = "flex";
        });

        if (btnFecharEditar) {
            btnFecharEditar.addEventListener("click", () => {
                modalEditar.style.display = "none";
            });
        }

        // Fechar ao clicar fora do conteúdo (no overlay escuro)
        modalEditar.addEventListener("click", (e) => {
            if (e.target === modalEditar) {
                modalEditar.style.display = "none";
            }
        });
    }

    // -------- MODAL PERFIL / CONTA --------
    const btnConta = document.getElementById("btn-conta");
    const perfilOverlay = document.getElementById("perfil-overlay");

    if (btnConta && perfilOverlay) {
        // Abrir perfil
        btnConta.addEventListener("click", () => {
            perfilOverlay.classList.add("show");
        });

        // Fechar no botão Voltar
        const perfilVoltar = perfilOverlay.querySelector(".perfil-voltar-btn");
        if (perfilVoltar) {
            perfilVoltar.addEventListener("click", () => {
                perfilOverlay.classList.remove("show");
            });
        }

        // Fechar ao clicar fora do cartão (no fundo escuro)
        perfilOverlay.addEventListener("click", (e) => {
            if (e.target === perfilOverlay) {
                perfilOverlay.classList.remove("show");
            }
        });
    }
});