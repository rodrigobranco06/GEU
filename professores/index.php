<?php
// professores/index.php

include 'modelsProfessores.php';

$professores = getTodosProfessores();

?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>GEU — Ver Professores</title>
    <link rel="stylesheet" href="css/index.css" />
</head>
<body>

<header id="header">
    <div class="header-logo">
        <a href="index.php">
            <img src="../img/Logo.png" alt="Gestão de Estágios Universitários">
        </a>
    </div>

    <nav class="nav-menu">
        <a href="../alunos/index.html" class="nav-link">Alunos</a>
        <a href="index.php" class="nav-link active">Professores</a>
        <a href="../empresas/index.html" class="nav-link">Empresas</a>
        <a href="../index.php" class="nav-link">Turmas</a>

        <button class="btn-conta" id="btn-conta">
            <img src="../img/img_conta.png" alt="Conta">
        </button>
        <a href="login.html" class="btn-sair">Sair</a>
    </nav>
</header>

<main id="main-content">

    <nav class="subtabs">
        <a href="index.php" class="subtab-link active">Ver Professores</a>
        <a href="registarProfessor.php" class="subtab-link">Registar novo professor</a>
    </nav>

    <section class="search-area">
        <div class="search-wrapper">
            <span class="search-icon">🔍</span>
            <input type="text" id="search-input" placeholder="Procurar por professor" aria-label="Procurar por professor">
        </div>
    </section>

    <section class="table-wrapper">
        <table class="professores-table">
            <thead>
            <tr>
                <th>Número de professor</th>
                <th>Nome do professor</th>
                <th>Email</th>
                <th>Especialização</th>
            </tr>
            </thead>
            <tbody id="professores-table-body">
            <?php foreach ($professores as $prof): ?>
                <tr onclick="window.location.href='verProfessor.php?id_professor=<?= $prof['id_professor'] ?>'" class="linha-click">
                    <td><?= htmlspecialchars($prof['id_professor']) ?></td>
                    <td><?= htmlspecialchars($prof['nome_professor']) ?></td>
                    <td><?= htmlspecialchars($prof['email_institucional']) ?></td>
                    <td><?= htmlspecialchars($prof['especializacao_desc']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>

        </table>
    </section>

</main>

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

            <a href="../verPerfil.html" class="perfil-row">
                <img src="../img/img_definicoes.png" alt="Definições" class="perfil-row-img">
                <span class="perfil-row-text">Definições de conta</span>
            </a>

            <a href="../login.html" class="perfil-logout-row">
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