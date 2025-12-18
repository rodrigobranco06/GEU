<?php
// alunos/index.php

include 'modelsAlunos.php';

$alunos = getTodosAlunos();
?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>GEU — Ver Alunos</title>
    <link rel="stylesheet" href="css/index.css" />
</head>

<body>

    <!-- ======= CABEÇALHO ======= -->
    <header id="header">
        <div class="header-logo">
            <a href="../index.php">
                <img src="../img/Logo.png" alt="Gestão de Estágios Universitários">
            </a>
        </div>

        <nav class="nav-menu">
            <a href="index.php" class="nav-link active">Alunos</a>
            <a href="../professores/index.php" class="nav-link">Professores</a>
            <a href="../empresas/index.php" class="nav-link">Empresas</a>
            <a href="../index.php" class="nav-link">Turmas</a>
            <a href="../index.php" class="nav-link">Administradores</a>

            <button class="btn-conta" id="btn-conta">
                <img src="../img/img_conta.png" alt="Conta">
            </button>
            <a href="../login.php" class="btn-sair">Sair</a>
        </nav>
    </header>

    <!-- ======= CONTEÚDO PRINCIPAL ======= -->
    <main id="main-content">

        <!-- Tabs internas (Ver / Registar) -->
        <nav class="subtabs">
            <a href="index.php" class="subtab-link active">Ver Alunos</a>
            <a href="registarAluno.php" class="subtab-link">Registar novo aluno</a>
        </nav>

        <!-- Tabela de alunos -->
        <section class="search-area">
            <div class="search-wrapper">
                <span class="search-icon">🔍</span>
                <input
                    type="text"
                    id="search-input"
                    placeholder="Procurar por aluno"
                    aria-label="Procurar por aluno">
            </div>
        </section>

        <section class="tabela-alunos">
            <table>
                <thead>
                    <tr>
                        <th>Número de aluno</th>
                        <th>Nome do aluno</th>
                        <th>Curso</th>
                        <th>Empresa</th>
                        <th>Estado do estágio</th>
                    </tr>
                </thead>

                <tbody id="alunos-table-body">
                    <?php foreach ($alunos as $al): ?>
                        <tr
                            class="linha-click"
                            onclick="window.location='verAluno.php?id_aluno=<?= (int)$al['id_aluno'] ?>'">
                            <td><?= htmlspecialchars($al['id_aluno']) ?></td>
                            <td><?= htmlspecialchars($al['nome_aluno']) ?></td>
                            <td><?= htmlspecialchars($al['curso_desc'] ?? 'Sem curso') ?></td>
                            <td><?= htmlspecialchars($al['nome_empresa'] ?? 'Sem empresa') ?></td>
                            <td><?= htmlspecialchars($al['estado_pedido'] ?? 'Esperando empresa') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>

            </table>
        </section>
    </main>

    <!-- ======= RODAPÉ ======= -->
    <footer id="footer">
        <div class="contactos">
            <h3>Contactos</h3>
            <p>
                <img src="../img/img_email.png" alt="Email">
                <strong>Email:</strong> geral@ipsantarem.pt
            </p>
            <p>
                <img src="../img/img_telemovel.png" alt="Telefone">
                <strong>Telefone:</strong> +351 243 309 520
            </p>
            <p>
                <img src="../img/img_localizacao.png" alt="Endereço">
                <strong>Endereço:</strong> Complexo Andaluz, Apartado 279, 2001-904 Santarém
            </p>
        </div>

        <div class="logos">
            <img src="../img/Logo.png" alt="Gestão de Estágios Universitários">
            <img src="../img/img_confinanciado.png" alt="Confinanciado">
        </div>
    </footer>

    <!-- ======= MODAL PERFIL / CONTA ======= -->
    <div id="perfil-overlay" class="perfil-overlay">
        <div class="perfil-card">
            <div class="perfil-banner"></div>

            <div class="perfil-avatar">
                <img src="../img/img_conta.png" alt="Avatar" class="perfil-avatar-img">
            </div>

            <div class="perfil-content">
                <div class="perfil-role">Aluno</div>
                <div class="perfil-name">Rodrigo Branco</div>

                <div class="perfil-row">
                    <img src="../img/img_email.png" alt="Email" class="perfil-row-img">
                    <span class="perfil-row-text">240001087@esg.ipsantarem.pt</span>
                </div>

                <a href="../verPerfil.php" class="perfil-row">
                    <img src="../img/img_definicoes.png" alt="Definições" class="perfil-row-img">
                    <span class="perfil-row-text">Definições de conta</span>
                </a>

                <a href="../login.php" class="perfil-logout-row">
                    <img src="../img/img_sair.png" alt="Sair" class="perfil-back-img">
                    <span class="perfil-logout-text">Log out</span>
                </a>

                <button type="button" class="perfil-voltar-btn">
                    Voltar
                </button>
            </div>
        </div>
    </div>

    <script src="js/index.js"></script>
</body>
</html>
