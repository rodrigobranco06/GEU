<?php
session_start();
include 'db.php';
include 'modelsPerfil.php';

if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit();
}

$conexao = estabelecerConexao();
$cargo = $_SESSION['cargo'];
$idUser = $_SESSION['id_utilizador'];

$dados = getDadoscompletosPerfil($conexao, $idUser, $cargo);

$email_valor = ($cargo === 'Empresa') ? $dados['email'] : $dados['email_institucional'];
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>GEU — Ver perfil (definições de conta)</title>
    <link rel="stylesheet" href="css/verPerfil.css" />
</head>
<body>

    <header id="header">
        <div class="header-logo">
            <a href="index.php">
                <img src="img/Logo.png" alt="GEU">
            </a>
        </div>

        <nav class="nav-menu">
            <?php if ($cargo === 'Administrador'): ?>
                <a href="administradores/index.php" class="nav-link">Administradores</a>
            <?php endif; ?>
            <?php if ($cargo === 'Administrador' || $cargo === 'Professor'): ?>
                <a href="empresas/index.php" class="nav-link">Empresas</a>
            <?php endif; ?>
            <?php if ($cargo === 'Administrador'): ?>
                <a href="professores/index.php" class="nav-link">Professores</a>
                <a href="alunos/index.php" class="nav-link">Alunos</a>
            <?php endif; ?>
            <a href="index.php" class="nav-link active">Turmas</a>

            
            <a href="logout.php" class="btn-sair">Sair</a>
        </nav>
    </header>

    <main id="main-content">
        <div class="perfil-head">
            <h1 class="perfil-titulo">Definições (Informações pessoais)</h1>
            <a href="javascript:history.back()" class="btn-voltar">Voltar</a>
        </div>

        <section class="perfil-card">
            <ul class="perfil-list">

                <li class="perfil-row">
                    <div class="perfil-label">
                        <img src="img/img_conta_def.png" alt="User" class="perfil-icon-img">
                        <span class="perfil-label-text">Nome de utilizador</span>
                    </div>
                    <span class="perfil-value"><?= htmlspecialchars($dados['nome']) ?></span>
                </li>

                <li class="perfil-row">
                    <div class="perfil-label">
                        <img src="img/img_email.png" alt="Email" class="perfil-icon-img">
                        <span class="perfil-label-text">Email</span>
                    </div>
                    <span class="perfil-value"><?= htmlspecialchars($email_valor) ?></span>
                </li>

                <?php if ($cargo === 'Aluno'): ?>
                <li class="perfil-row">
                    <div class="perfil-label">
                        <img src="img/img_curso.png" alt="Curso" class="perfil-icon-img">
                        <span class="perfil-label-text">Curso</span>
                    </div>
                    <span class="perfil-value"><?= htmlspecialchars($dados['curso_desc'] ?? 'N/A') ?></span>
                </li>

                <li class="perfil-row">
                    <div class="perfil-label">
                        <img src="img/img_professorOrientador.png" alt="Professor" class="perfil-icon-img">
                        <span class="perfil-label-text">Professor orientador</span>
                    </div>
                    <span class="perfil-value"><?= htmlspecialchars($dados['professor_nome'] ?? 'A definir') ?></span>
                </li>

                <li class="perfil-row">
                    <div class="perfil-label">
                        <img src="img/img_escola.png" alt="Escola" class="perfil-icon-img">
                        <span class="perfil-label-text">Escola</span>
                    </div>
                    <span class="perfil-value"><?= htmlspecialchars($dados['escola_desc'] ?? 'IPSantarem') ?></span>
                </li>

                <li class="perfil-row">
                    <div class="perfil-label">
                        <img src="img/img_estadoEstagio.png" alt="Estado" class="perfil-icon-img">
                        <span class="perfil-label-text">Estado do estágio</span>
                    </div>
                    <span class="perfil-value"><?= htmlspecialchars($dados['estado_estagio'] ?? 'Sem pedidos') ?></span>
                </li>
                <?php endif; ?>

                <?php if ($cargo === 'Aluno' || $cargo === 'Empresa'): ?>
                <li class="perfil-row">
                    <div class="perfil-label">
                        <img src="img/img_github.png" alt="GitHub" class="perfil-icon-img">
                        <span class="perfil-label-text">GitHub</span>
                    </div>
                    <form action="processarPerfil.php" method="POST" class="perfil-input-group">
                        <input type="url" name="github" class="perfil-input" value="<?= htmlspecialchars($dados['github'] ?? '') ?>" placeholder="https://github.com/o-teu-username" />
                        <button type="submit" name="acao" value="update_github" class="btn-add">Adicionar</button>
                    </form>
                </li>

                <li class="perfil-row">
                    <div class="perfil-label">
                        <img src="img/img_linkedIn.png" alt="LinkedIn" class="perfil-icon-img">
                        <span class="perfil-label-text">LinkedIn</span>
                    </div>
                    <form action="processarPerfil.php" method="POST" class="perfil-input-group">
                        <input type="url" name="linkedin" class="perfil-input" value="<?= htmlspecialchars($dados['linkedin'] ?? '') ?>" placeholder="https://linkedin.com/in/o-teu-perfil" />
                        <button type="submit" name="acao" value="update_linkedin" class="btn-add">Adicionar</button>
                    </form>
                </li>
                <?php endif; ?>

            </ul>
        </section>
    </main>

    <footer id="footer">
        <div class="contactos">
            <h3>Contactos</h3>
            <p>
                <img src="img/img_email.png" alt="Email">
                <strong>Email:</strong> geral@ipsantarem.pt
            </p>
            <p>
                <img src="img/img_telemovel.png" alt="Telefone">
                <strong>Telefone:</strong> +351 243 309 520
            </p>
            <p>
                <img src="img/img_localizacao.png" alt="Endereço">
                <strong>Endereço:</strong> Complexo Andaluz, Apartado 279, 2001-904 Santarém
            </p>
        </div>
        <div class="logos">
            <img src="img/Logo.png" alt="GEU">
            <img src="img/img_confinanciado.png" alt="Confinanciado">
        </div>
    </footer>

    <?php if (isset($_GET['sucesso'])): ?>
        <?php if ($_GET['sucesso'] === 'github'): ?>
            <div id="popup-github" class="popup-overlay show">
                <div class="popup-box">
                    <div class="popup-header">
                        <img src="img/img_github.png" alt="GitHub">
                        <h2>Link do GitHub adicionado!</h2>
                    </div>
                    <button type="button" class="popup-close" onclick="fecharModaisForcado()">Fechar</button>
                </div>
            </div>
        <?php elseif ($_GET['sucesso'] === 'linkedin'): ?>
            <div id="popup-linkedin" class="popup-overlay show">
                <div class="popup-box">
                    <div class="popup-header">
                        <img src="img/img_linkedIn.png" alt="LinkedIn">
                        <h2>Link do LinkedIn adicionado!</h2>
                    </div>
                    <button type="button" class="popup-close" onclick="fecharModaisForcado()">Fechar</button>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <script>
        function fecharModaisForcado() {
            document.querySelectorAll('.popup-overlay').forEach(p => p.classList.remove('show'));
            
            if (window.history.replaceState) {
                const url = window.location.pathname;
                window.history.replaceState({}, '', url);
            }
        }

        window.onclick = function(event) {
            if (event.target.classList.contains('popup-overlay')) {
                fecharModaisForcado();
            }
            
            const modalConta = document.getElementById("perfil-overlay");
            if (event.target == modalConta) {
                modalConta.classList.remove("show");
            }
        }
    </script>
    
    <script src="js/verPerfil.js"></script>

</body>

</html>