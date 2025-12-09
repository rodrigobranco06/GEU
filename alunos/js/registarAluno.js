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
// se quiseres podes redirecionar para login.php)
if (perfilLogout) {
  perfilLogout.addEventListener("click", function () {
    console.log("Log out clicado");
    // window.location.href = "../login.php";
  });
}


// -------- LÓGICA DO FORM DE REGISTO --------
document.addEventListener("DOMContentLoaded", () => {
  // Toggle da password
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

  // Guardar TODAS as opções originais de turma (menos o placeholder)
  const allTurmaOptions = Array.from(turmaSelect.querySelectorAll("option"))
    .slice(1) // ignora a primeira linha ("Selecione um curso primeiro")
    .map(opt => opt.cloneNode(true)); // clona com atributos (incluindo data-curso-id)

  function filtrarTurmasPorCurso() {
    const cursoId = cursoSelect.value;

    // Limpa o select e volta a meter o placeholder
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

    // Filtra as turmas cujo data-curso-id coincide com o curso selecionado
    const filtradas = allTurmaOptions.filter(opt => {
      return opt.dataset.cursoId === cursoId;
    });

    filtradas.forEach(opt => turmaSelect.appendChild(opt));

    turmaSelect.disabled = filtradas.length === 0;
  }

  // Quando o utilizador muda de curso
  cursoSelect.addEventListener("change", filtrarTurmasPorCurso);

  // Estado inicial (se já vier um curso selecionado por causa de erro de validação)
  if (cursoSelect.value) {
    filtrarTurmasPorCurso();
  } else {
    turmaSelect.disabled = true;
  }
});
