document.addEventListener("DOMContentLoaded", () => {
    const btnConta = document.getElementById("btn-conta");
    const perfilOverlay = document.getElementById("perfil-overlay");

    if (btnConta && perfilOverlay) {
        // Abrir modal
        btnConta.addEventListener("click", () => {
            perfilOverlay.classList.add("show");
        });

        // Fechar ao clicar no botão Voltar
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

    // Lógica de Logout
    const perfilLogout = document.querySelector(".perfil-logout-row");
    if (perfilLogout) {
        perfilLogout.addEventListener("click", function() {
            console.log("Log out clicado");
        });
    }
});