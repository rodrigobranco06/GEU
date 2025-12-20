document.addEventListener("DOMContentLoaded", () => {
    const btnConta = document.getElementById("btn-conta");
    const perfilOverlay = document.getElementById("perfil-overlay");

    if (btnConta && perfilOverlay) {
        btnConta.addEventListener("click", () => {
            perfilOverlay.classList.add("show");
        });

        const btnVoltar = perfilOverlay.querySelector(".perfil-voltar-btn");
        btnVoltar?.addEventListener("click", () => {
            perfilOverlay.classList.remove("show");
        });
    }
});