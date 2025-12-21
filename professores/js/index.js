document.addEventListener('DOMContentLoaded', () => {

    function htmlspecialchars(str) {
        if (typeof str !== 'string') return str;
        return str.replace(/&/g, '&amp;')
                  .replace(/</g, '&lt;')
                  .replace(/>/g, '&gt;')
                  .replace(/"/g, '&quot;')
                  .replace(/'/g, '&#039;');
    }


    // --- Lógica de Pesquisa Instantânea ---
    const searchInput = document.getElementById('search-input');
    const tableBody = document.getElementById('professores-table-body');
    
    if (searchInput && tableBody) {
        
       
        const createTableRow = (prof) => {
            const id = prof.id_professor || '';
            const nome = prof.nome_professor ? htmlspecialchars(prof.nome_professor) : '';
            const email = prof.email_institucional ? htmlspecialchars(prof.email_institucional) : '';
            const espec = prof.especializacao_desc ? htmlspecialchars(prof.especializacao_desc) : '';

            return `
                <tr onclick="window.location.href='verProfessor.php?id_professor=${id}'" class="linha-click">
                    <td>${id}</td>
                    <td>${nome}</td>
                    <td>${email}</td>
                    <td>${espec}</td>
                </tr>
            `;
        };
        
        const fetchAndRenderProfessores = (searchTerm) => {
            const url = `fetchProfessores.php?search=${encodeURIComponent(searchTerm)}`;

            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(professores => {
                    tableBody.innerHTML = '';
                    
                    if (professores.length === 0) {
                        tableBody.innerHTML = '<tr><td colspan="4" style="text-align: center; padding: 20px;">Nenhum professor encontrado.</td></tr>';
                        return;
                    }

                    let newHtml = '';
                    professores.forEach(prof => {
                        newHtml += createTableRow(prof);
                    });
                    tableBody.innerHTML = newHtml;
                })
                .catch(e => {
                    console.error('Erro ao buscar professores:', e);
                    tableBody.innerHTML = '<tr><td colspan="4" style="text-align: center; padding: 20px; color: red;">Erro ao carregar a lista de professores.</td></tr>';
                });
        };

        searchInput.addEventListener('input', (event) => {
            const searchTerm = event.target.value.trim();
            fetchAndRenderProfessores(searchTerm);
        });
    }


    // -------- MODAL PERFIL / CONTA --------
    const btnConta      = document.getElementById("btn-conta");
    const perfilOverlay  = document.getElementById("perfil-overlay");
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

    // Ação de logout
    if (perfilLogout) {
        perfilLogout.addEventListener("click", function () {
            console.log("Log out clicado");
        });
    }

});