// -------- MODAL PERFIL / CONTA --------
const btnConta      = document.getElementById("btn-conta");
const perfilOverlay = document.getElementById("perfil-overlay");
const perfilVoltar  = perfilOverlay ? perfilOverlay.querySelector(".perfil-voltar-btn") : null;
const perfilLogout  = perfilOverlay ? perfilOverlay.querySelector(".perfil-logout-row") : null;

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
// se quiseres podes redirecionar para login.html / login.php)
if (perfilLogout) {
  perfilLogout.addEventListener("click", function () {
    console.log("Log out clicado");
    // window.location.href = "../login.php";
  });
}



// -------- LISTAGEM / PESQUISA DE ALUNOS --------
// (funciona na página de "Ver Alunos")

document.addEventListener("DOMContentLoaded", () => {
  // procura o input de pesquisa e o corpo da tabela
  const searchInput = document.querySelector(".search-area input[type='text']");
  const tabelaBody  = document.querySelector(".tabela-alunos tbody");

  // Se a página não tiver estes elementos (outra página qualquer), não faz nada
  if (!searchInput || !tabelaBody) {
    return;
  }

  async function carregarAlunos(term = "") {
    try {
      const params = new URLSearchParams();
      if (term) {
        params.set("search", term);
      }

      const response = await fetch("fetchAlunos.php?" + params.toString(), {
        headers: {
          "Accept": "application/json"
        }
      });

      if (!response.ok) {
        console.error("Erro ao carregar alunos:", response.status, response.statusText);
        return;
      }

      const dados = await response.json();

      // Limpa a tabela
      tabelaBody.innerHTML = "";

      // Preenche com os alunos recebidos
      dados.forEach((aluno) => {
        const tr = document.createElement("tr");
        tr.classList.add("linha-click");

        tr.addEventListener("click", () => {
          window.location.href = `verAluno.php?id_aluno=${encodeURIComponent(aluno.id_aluno)}`;
        });

        tr.innerHTML = `
          <td>${aluno.id_aluno}</td>
          <td>${aluno.nome_aluno}</td>
          <td>${aluno.curso_desc      ?? "Sem curso"}</td>
          <td>${aluno.nome_empresa    ?? "Sem empresa"}</td>
          <td>${aluno.estado_pedido   ?? "Esperando empresa"}</td>
        `;

        tabelaBody.appendChild(tr);
      });
    } catch (erro) {
      console.error("Erro ao obter alunos:", erro);
    }
  }

  // Carregamento inicial (sem filtro)
  carregarAlunos();

  // Debounce da pesquisa para não enviar pedido a cada tecla
  let timeoutId;
  searchInput.addEventListener("input", () => {
    const term = searchInput.value.trim();

    clearTimeout(timeoutId);
    timeoutId = setTimeout(() => {
      carregarAlunos(term);
    }, 300);
  });
});
