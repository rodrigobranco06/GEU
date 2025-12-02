document.addEventListener('DOMContentLoaded', function () {
    console.log('index.js carregado e DOM pronto');

    // -------- MODAL CRIAR TURMA --------
    const modalCriar      = document.getElementById("modal-criar-turma");
    const btnAbrirCriar   = document.querySelector(".btn-criar-turma");
    const btnFecharCriar  = document.getElementById("btn-fechar-modal");

    if (modalCriar && btnAbrirCriar && btnFecharCriar) {
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
    }

    // -------- MODAL PERFIL / CONTA --------
    const btnConta       = document.getElementById("btn-conta");
    const perfilOverlay  = document.getElementById("perfil-overlay");
    const perfilVoltar   = perfilOverlay ? perfilOverlay.querySelector(".perfil-voltar-btn") : null;
    const perfilLogout   = perfilOverlay ? perfilOverlay.querySelector(".perfil-logout-row") : null;

    if (btnConta && perfilOverlay) {
        btnConta.addEventListener("click", function () {
            perfilOverlay.classList.add("show");
        });
    }

    if (perfilVoltar && perfilOverlay) {
        perfilVoltar.addEventListener("click", function () {
            perfilOverlay.classList.remove("show");
        });
    }

    if (perfilOverlay) {
        perfilOverlay.addEventListener("click", function (e) {
            if (e.target === perfilOverlay) {
                perfilOverlay.classList.remove("show");
            }
        });
    }

    if (perfilLogout) {
        perfilLogout.addEventListener("click", function () {
            console.log("Log out clicado");
            // window.location.href = "../login.html";
        });
    }

    // ====== GERAÇÃO AUTOMÁTICA DO CÓDIGO DA TURMA ======
    const inputCurso      = document.getElementById('curso');
    const inputAnoInicio  = document.getElementById('ano-inicio');
    const inputAnoFim     = document.getElementById('ano-fim');
    const inputCodigo     = document.getElementById('codigo-turma');

    console.log('inputs para código turma:', { inputCurso, inputAnoInicio, inputAnoFim, inputCodigo });

    if (!inputCurso || !inputAnoInicio || !inputAnoFim || !inputCodigo) {
        console.warn('Algum dos inputs não foi encontrado, geração de código desativada.');
        return;
    }

    function gerarCodigoTurma() {
        let curso    = inputCurso.value.trim().toLowerCase();
        const anoIni = inputAnoInicio.value.trim();
        const anoFim = inputAnoFim.value.trim();

        if (curso && anoIni && anoFim) {
            curso = curso
                .normalize('NFD').replace(/[\u0300-\u036f]/g, '') // remove acentos
                .replace(/\s+/g, ''); // remove espaços

            const codigo = curso + anoIni + anoFim;
            inputCodigo.value = codigo;
        } else {
            inputCodigo.value = '';
        }
    }

    inputCurso.addEventListener('input', gerarCodigoTurma);
    inputAnoInicio.addEventListener('input', gerarCodigoTurma);
    inputAnoFim.addEventListener('input', gerarCodigoTurma);
});
