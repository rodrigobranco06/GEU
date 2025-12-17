// -------- MODAL PERFIL / CONTA --------
const btnConta      = document.getElementById("btn-conta");
const perfilOverlay = document.getElementById("perfil-overlay");
const perfilVoltar  = perfilOverlay ? perfilOverlay.querySelector(".perfil-voltar-btn") : null;

if (btnConta && perfilOverlay) btnConta.addEventListener("click", () => perfilOverlay.classList.add("show"));
if (perfilVoltar && perfilOverlay) perfilVoltar.addEventListener("click", () => perfilOverlay.classList.remove("show"));
if (perfilOverlay) {
  perfilOverlay.addEventListener("click", (e) => {
    if (e.target === perfilOverlay) perfilOverlay.classList.remove("show");
  });
}

// -------- PESQUISA --------
document.addEventListener("DOMContentLoaded", () => {
  const searchInput = document.getElementById("search-input");
  const tabelaBody  = document.getElementById("admins-table-body");
  if (!searchInput || !tabelaBody) return;

  async function carregar(term = "") {
    const params = new URLSearchParams();
    if (term) params.set("search", term);

    const res = await fetch("fetchAdministradores.php?" + params.toString(), {
      headers: { "Accept": "application/json" }
    });
    if (!res.ok) return;

    const dados = await res.json();
    tabelaBody.innerHTML = "";

    dados.forEach((a) => {
      const tr = document.createElement("tr");
      tr.classList.add("linha-click");
      tr.addEventListener("click", () => {
        window.location.href = `verAdministrador.php?id_admin=${encodeURIComponent(a.id_admin)}`;
      });

      tr.innerHTML = `
        <td>${a.id_admin}</td>
        <td>${a.nome_admin ?? ""}</td>
        <td>${a.email_institucional ?? ""}</td>
        <td>${a.email_pessoal ?? ""}</td>
      `;
      tabelaBody.appendChild(tr);
    });
  }

  carregar();

  let t;
  searchInput.addEventListener("input", () => {
    clearTimeout(t);
    t = setTimeout(() => carregar(searchInput.value.trim()), 250);
  });
});
