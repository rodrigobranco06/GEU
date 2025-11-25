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

// -------- MODAL PERFIL / CONTA --------
const btnConta       = document.getElementById("btn-conta");
const perfilOverlay  = document.getElementById("perfil-overlay");
const perfilVoltar   = perfilOverlay ? perfilOverlay.querySelector(".perfil-voltar-btn") : null;
const perfilLogout   = perfilOverlay ? perfilOverlay.querySelector(".perfil-logout-row") : null;

if (!btnConta || !perfilOverlay) {
  console.warn("Botão de conta ou overlay de perfil não encontrado.");
}

// Abrir modal de perfil
if (btnConta && perfilOverlay) {
  btnConta.addEventListener("click", function () {
    perfilOverlay.classList.add("show");
  });
}

// Fechar ao clicar em "Voltar"
if (perfilVoltar && perfilOverlay) {
  perfilVoltar.addEventListener("click", function () {
    perfilOverlay.classList.remove("show");
  });
}

// Fechar ao clicar fora do cartão
if (perfilOverlay) {
  perfilOverlay.addEventListener("click", function (e) {
    if (e.target === perfilOverlay) {
      perfilOverlay.classList.remove("show");
    }
  });
}

// Ação de logout (por agora só consola;
// se quiseres podes redirecionar para login.html)
if (perfilLogout) {
  perfilLogout.addEventListener("click", function () {
    console.log("Log out clicado");
    // window.location.href = "../login.html";
  });
}