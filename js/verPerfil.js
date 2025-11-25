document.addEventListener("DOMContentLoaded", () => {

            const popupGithub = document.getElementById("popup-github");
            const popupLinkedIn = document.getElementById("popup-linkedin");

            const btnGithub = document.getElementById("btn-add-github");
            const btnLinkedIn = document.getElementById("btn-add-linkedin");

            // Abrir popups
            btnGithub?.addEventListener("click", () => {
                popupGithub.classList.add("show");
            });

            btnLinkedIn?.addEventListener("click", () => {
                popupLinkedIn.classList.add("show");
            });

            // Botões de fechar
            document.querySelectorAll(".popup-close").forEach(btn => {
                btn.addEventListener("click", () => {
                    if (btn.dataset.close === "github") popupGithub.classList.remove("show");
                    if (btn.dataset.close === "linkedin") popupLinkedIn.classList.remove("show");
                });
            });

            // Fechar ao clicar fora
            popupGithub?.addEventListener("click", e => {
                if (e.target === popupGithub) popupGithub.classList.remove("show");
            });

            popupLinkedIn?.addEventListener("click", e => {
                if (e.target === popupLinkedIn) popupLinkedIn.classList.remove("show");
            });

        });