<?php
// administradores/editarAdministrador.php

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

$erros = $erros ?? [];
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>GEU — Editar administrador</title>
    <link rel="stylesheet" href="css/editarAdministrador.css" />
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
        <form class="form-admin" method="post" action="updateAdministrador.php?id_admin=<?= (int)$idAdmin ?>" id="formEditarAdmin">

            <input type="hidden" name="id_admin" value="<?= (int)$idAdmin ?>">

            <?php if (!empty($erros)): ?>
                <div class="erros">
                    <ul>
                        <?php foreach ($erros as $e): ?>
                            <li><?= htmlspecialchars($e) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label>Código</label>
                <input type="text" value="<?= htmlspecialchars($admin['id_admin']) ?>" readonly>
            </div>

            <div class="form-group">
                <label for="novaPassword">Nova password</label>
                <div class="password-wrapper">
                    <input id="novaPassword" name="nova_password" type="password" placeholder="Deixe vazio para manter a mesma">
                    <label class="toggle-password-inline">
                        <input type="checkbox" id="toggleNovaPassword">
                        Mostrar password
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label for="nome">Nome</label>
                <input id="nome" name="nome" type="text" value="<?= htmlspecialchars($admin['nome'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="emailInstitucional">Email institucional</label>
                <input id="emailInstitucional" name="emailInstitucional" type="email" value="<?= htmlspecialchars($admin['email_institucional'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="emailPessoal">Email pessoal</label>
                <input id="emailPessoal" name="emailPessoal" type="email" value="<?= htmlspecialchars($admin['email_pessoal'] ?? '') ?>">
            </div>

            <div class="side-top side-top-inside-form">
                <button class="btn-salvar" type="submit">Salvar</button>

                <button
                    class="btn-eliminar"
                    type="submit"
                    formaction="deleteAdministrador.php?id_admin=<?= (int)$idAdmin ?>"
                    onclick="return confirm('Tem a certeza que pretende eliminar este administrador? Esta ação é irreversível.');"
                >
                    Eliminar
                </button>

                <a class="btn-voltar" href="verAdministrador.php?id_admin=<?= (int)$idAdmin ?>">Voltar</a>
            </div>

        </form>

        <aside class="side-panel">
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

<script src="js/editarAdministrador.js"></script>
</body>
</html>
