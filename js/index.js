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

 // GERACAO AUTOMATICA DE CODIGO DE TURMA E NOME DA TURMA

    // Localiza os inputs no DOM
    const inputCurso      = document.getElementById('curso');
    const inputAnoCurric  = document.getElementById('ano-curricular');
    const inputAnoInicio  = document.getElementById('ano-inicio');
    const inputAnoFim     = document.getElementById('ano-fim');
    const inputCodigo     = document.getElementById('codigo-turma');
    const inputNome       = document.getElementById('nome-turma');

    function atualizarCamposAutomaticos() {
        const optionSelecionada = inputCurso.options[inputCurso.selectedIndex];
        // Obtém a sigla diretamente do atributo data que definimos no PHP
        const sigla = optionSelecionada ? optionSelecionada.getAttribute('data-sigla').trim().toUpperCase() : '';
        
        const anoCurric = inputAnoCurric.value.trim();
        const anoIni    = inputAnoInicio.value.trim();
        const anoFim    = inputAnoFim.value.trim();

        if (sigla && anoIni && anoFim) {
            // Gera o Código: tpsi20242026
            inputCodigo.value = sigla.toLowerCase() + anoIni + anoFim;

            // Gera o Nome: TPSI - 2 (2024/2026)
            if (anoCurric) {
                inputNome.value = `${sigla} - ${anoCurric} (${anoIni}/${anoFim})`;
            } else {
                inputNome.value = '';
            }
        } else {
            inputCodigo.value = '';
            inputNome.value = '';
        }
    }

    // Adiciona os ouvintes de evento
    if (inputCurso && inputAnoCurric && inputAnoInicio && inputAnoFim) {
        inputCurso.addEventListener('change', atualizarCamposAutomaticos);
        inputAnoCurric.addEventListener('input', atualizarCamposAutomaticos);
        inputAnoInicio.addEventListener('input', atualizarCamposAutomaticos);
        inputAnoFim.addEventListener('input', atualizarCamposAutomaticos);
    }
});
