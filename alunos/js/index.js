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

// Ação de logout
if (perfilLogout) {
  perfilLogout.addEventListener("click", function () {
    console.log("Log out clicado");
  });
}


// -------- LISTAGEM / PESQUISA DE ALUNOS --------
document.addEventListener("DOMContentLoaded", () => {
  const searchInput = document.getElementById("search-input");
  const tabelaBody  = document.getElementById("alunos-table-body");

  if (!searchInput || !tabelaBody) return;

  async function carregarAlunos(term = "") {
    try {
      const params = new URLSearchParams();
      if (term) params.set("search", term);

      const response = await fetch("fetchAlunos.php?" + params.toString(), {
        headers: { "Accept": "application/json" }
      });

      if (!response.ok) {
        console.error("Erro ao carregar alunos:", response.status, response.statusText);
        return;
      }

      const dados = await response.json();

      tabelaBody.innerHTML = "";

      if (!Array.isArray(dados) || dados.length === 0) {
        const tr = document.createElement("tr");
        tr.innerHTML = `<td colspan="5" style="text-align:center;">Sem resultados</td>`;
        tabelaBody.appendChild(tr);
        return;
      }

      dados.forEach((aluno) => {
        const tr = document.createElement("tr");
        tr.classList.add("linha-click");

        tr.addEventListener("click", () => {
          window.location.href = `verAluno.php?id_aluno=${encodeURIComponent(aluno.id_aluno)}`;
        });

        tr.innerHTML = `
          <td>${aluno.id_aluno}</td>
          <td>${aluno.nome_aluno}</td>
          <td>${aluno.curso_desc ?? "Sem curso"}</td>
          <td>${aluno.nome_empresa ?? "Sem empresa"}</td>
          <td>${aluno.estado_pedido ?? "Esperando empresa"}</td>
        `;

        tabelaBody.appendChild(tr);
      });

    } catch (erro) {
      console.error("Erro ao obter alunos:", erro);
    }
  }

  carregarAlunos();

  let timeoutId;
  searchInput.addEventListener("input", () => {
    const term = searchInput.value.trim();
    clearTimeout(timeoutId);
    timeoutId = setTimeout(() => carregarAlunos(term), 300);
  });
});
