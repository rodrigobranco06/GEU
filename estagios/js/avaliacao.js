// confirmarDados.js

console.log("confirmarDados.js carregado");

const btnConfirmar = document.getElementById("btn-confirmar");
const popup        = document.getElementById("popup-salvar");
const btnCancelar  = popup.querySelector(".popup-cancel");
const btnSim       = popup.querySelector(".popup-confirm");

if (!btnConfirmar || !popup) {
  console.error("Elemento btn-confirmar ou popup-salvar não encontrado.");
}

// Abre o popup ao clicar em "Confirmar Dados"
if (btnConfirmar) {
  btnConfirmar.addEventListener("click", function () {
    console.log("Clique em Confirmar Dados");
    popup.classList.add("show");
  });
}

// Fecha ao clicar em "Cancelar"
if (btnCancelar) {
  btnCancelar.addEventListener("click", function () {
    popup.classList.remove("show");
  });
}

// Fecha ao clicar fora da caixa
if (popup) {
  popup.addEventListener("click", function (e) {
    if (e.target === popup) {
      popup.classList.remove("show");
    }
  });
}

// Ação ao clicar em "Sim"
if (btnSim) {
  btnSim.addEventListener("click", function () {
    popup.classList.remove("show");
    console.log("Dados confirmados! (aqui depois fazes o submit ou fetch)");
  });
}


function openModal() {
    document.getElementById("uploadModal").classList.add("show");
}

function closeModal() {
    document.getElementById("uploadModal").classList.remove("show");
}

// fechar ao clicar fora do cartão
window.addEventListener("click", function (e) {
    const modal = document.getElementById("uploadModal");
    const card = document.querySelector(".upload-card");
    if (modal.classList.contains("show") && !card.contains(e.target) && e.target === modal) {
        closeModal();
    }
});

// quando escolher o ficheiro, mostra o nome no botão do formulário
function displayFileName() {
    const fileInput = document.getElementById("fileInput");
    const file = fileInput.files[0];
    if (file) {
        document.querySelector(".upload-btn").textContent = file.name;
        closeModal();
    }
}


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