document.addEventListener("DOMContentLoaded", function () {
  const btnConfirmar = document.getElementById("btn-confirmar");
  const popup = document.getElementById("popup-salvar");
  const btnCancelar = popup.querySelector(".popup-cancel");
  const btnSim = popup.querySelector(".popup-confirm");

  // Abre o popup
  btnConfirmar.addEventListener("click", function () {
    popup.classList.add("show");
  });

  // Fecha ao clicar em "Cancelar"
  btnCancelar.addEventListener("click", function () {
    popup.classList.remove("show");
  });

  // Fecha ao clicar fora da caixa
  popup.addEventListener("click", function (e) {
    if (e.target === popup) {
      popup.classList.remove("show");
    }
  });

  // Ação ao clicar em "Sim"
  btnSim.addEventListener("click", function () {
    popup.classList.remove("show");
  });
});