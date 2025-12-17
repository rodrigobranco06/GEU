<?php
// administradores/verAdministrador.php

include 'modelsAdministradores.php';

if (!isset($_GET['id_admin']) || !ctype_digit($_GET['id_admin'])) {
    header('Location: index.php');
    exit;
}

$idAdmin = (int) $_GET['id_admin'];
$admin = getAdministradorById($idAdmin);

if (!$admin) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>GEU — Ver administrador</title>
    <link rel="stylesheet" href="css/verAdministrador.css" />
</head>
<body>

<header id="header">
    <div class="header-logo">
        <a href="../index.php">
            <img src="../img/Logo.png" alt="Gestão de Estágios Universitários">
        </a>
    </div>

    <nav class="nav-menu">
        <a href="../alunos/index.php" class="nav-link">Alunos</a>
        <a href="../professores/index.php" class="nav-link">Professores</a>
        <a href="../empresas/index.php" class="nav-link">Empresas</a>
        <a href="../index.php" class="nav-link">Turmas</a>
        <a href="index.php" class="nav-link active">Administradores</a>

        <button class="btn-conta" id="btn-conta">
            <img src="../img/img_conta.png" alt="Conta">
        </button>
        <a href="../login.php" class="btn-sair">Sair</a>
    </nav>
</header>

<main id="main-content">

    <nav class="subtabs">
        <a href="index.php" class="subtab-link active">Ver Administradores</a>
        <a href="registarAdministrador.php" class="subtab-link">Registar novo administrador</a>
    </nav>

    <section class="content-grid">
        <form class="form-admin">

            <div class="form-group">
                <label for="codigo">Código</label>
                <input id="codigo" type="text" value="<?= htmlspecialchars($admin['id_admin']) ?>" readonly>
            </div>

            <div class="form-group">
                <label for="nome">Nome</label>
                <input id="nome" type="text" value="<?= htmlspecialchars($admin['nome'] ?? '') ?>" readonly>
            </div>

            <div class="form-group">
                <label for="emailInst">Email institucional</label>
                <input id="emailInst" type="text" value="<?= htmlspecialchars($admin['email_institucional'] ?? '') ?>" readonly>
            </div>

            <div class="form-group">
                <label for="emailPessoal">Email pessoal</label>
                <input id="emailPessoal" type="text" value="<?= htmlspecialchars($admin['email_pessoal'] ?? '') ?>" readonly>
            </div>

        </form>

        <aside class="side-panel">
            <div class="side-top">
                <a href="editarAdministrador.php?id_admin=<?= (int)$admin['id_admin'] ?>" class="btn-editar">Editar</a>
                <a class="btn-voltar" href="index.php">Voltar</a>
            </div>

            <div class="side-image-wrapper">
                <img src="../img/img_registarAluno.png" alt="Ilustração administrador">
            </div>
        </aside>
    </section>
</main>

<footer id="footer">
    <div class="contactos">
        <h3>Contactos</h3>
        <p><img src="../img/img_email.png" alt="Email"><strong>Email:</strong> geral@ipsantarem.pt</p>
        <p><img src="../img/img_telemovel.png" alt="Telefone"><strong>Telefone:</strong> +351 243 309 520</p>
        <p><img src="../img/img_localizacao.png" alt="Endereço"><strong>Endereço:</strong> Complexo Andaluz, Apartado 279, 2001-904 Santarém</p>
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
            <div class="perfil-role">Administrador</div>
            <div class="perfil-name">Rodrigo Branco</div>
            <div class="perfil-row">
                <img src="../img/img_email.png" alt="Email" class="perfil-row-img">
                <span class="perfil-row-text">admin@ipsantarem.pt</span>
            </div>
            <a href="../verPerfil.php" class="perfil-row">
                <img src="../img/img_definicoes.png" alt="Definições" class="perfil-row-img">
                <span class="perfil-row-text">Definições de conta</span>
            </a>
            <a href="../login.php" class="perfil-logout-row">
                <img src="../img/img_sair.png" alt="Sair" class="perfil-back-img">
                <span class="perfil-logout-text">Log out</span>
            </a>
            <button type="button" class="perfil-voltar-btn">Voltar</button>
        </div>
    </div>
</div>

<script src="js/verAdministrador.js"></script>
</body>
</html>
