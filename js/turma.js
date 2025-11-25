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
