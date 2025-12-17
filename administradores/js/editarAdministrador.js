// modal
const btnConta = document.getElementById("btn-conta");
const perfilOverlay = document.getElementById("perfil-overlay");
const perfilVoltar = perfilOverlay ? perfilOverlay.querySelector(".perfil-voltar-btn") : null;

if (btnConta && perfilOverlay) btnConta.addEventListener("click", () => perfilOverlay.classList.add("show"));
if (perfilVoltar && perfilOverlay) perfilVoltar.addEventListener("click", () => perfilOverlay.classList.remove("show"));
if (perfilOverlay) perfilOverlay.addEventListener("click", (e) => { if (e.target === perfilOverlay) perfilOverlay.classList.remove("show"); });

// mostrar/ocultar nova password
const toggle = document.getElementById("toggleNovaPassword");
const input  = document.getElementById("novaPassword");
if (toggle && input) {
  toggle.addEventListener("change", () => {
    input.type = toggle.checked ? "text" : "password";
  });
}
