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

// Fechar ao clicar em Voltar
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

if (perfilLogout) {
  perfilLogout.addEventListener("click", function () {
    console.log("Log out clicado");
  });
}


// -------- LÓGICA DO FORM DE REGISTO --------
document.addEventListener("DOMContentLoaded", () => {
  const passwordInput  = document.getElementById("password");
  const togglePassword = document.getElementById("togglePassword");

  if (passwordInput && togglePassword) {
    togglePassword.addEventListener("change", () => {
      passwordInput.type = togglePassword.checked ? "text" : "password";
    });
  }

  // -------- FILTRAR TURMAS PELO CURSO SELECIONADO --------
  const cursoSelect = document.getElementById("curso");
  const turmaSelect = document.getElementById("turmaId");

  if (!cursoSelect || !turmaSelect) {
    return;
  }

  const allTurmaOptions = Array.from(turmaSelect.querySelectorAll("option"))
    .slice(1) 
    .map(opt => opt.cloneNode(true)); 

  function filtrarTurmasPorCurso() {
    const cursoId = cursoSelect.value;

    turmaSelect.innerHTML = "";
    const placeholder = document.createElement("option");
    placeholder.value = "";
    placeholder.textContent = cursoId
      ? "Selecione uma Turma"
      : "Selecione um curso primeiro";
    turmaSelect.appendChild(placeholder);

    if (!cursoId) {
      turmaSelect.disabled = true;
      return;
    }

    const filtradas = allTurmaOptions.filter(opt => {
      return opt.dataset.cursoId === cursoId;
    });

    filtradas.forEach(opt => turmaSelect.appendChild(opt));

    turmaSelect.disabled = filtradas.length === 0;
  }

  cursoSelect.addEventListener("change", filtrarTurmasPorCurso);

  if (cursoSelect.value) {
    filtrarTurmasPorCurso();
  } else {
    turmaSelect.disabled = true;
  }
});
