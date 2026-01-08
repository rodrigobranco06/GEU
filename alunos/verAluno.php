<?php
// alunos/verAluno.php

session_start();

// 1. Verificação de segurança: Logado + Cargo de Administrador
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['cargo'] !== 'Administrador') {
    // Se não for admin, redireciona para a página principal ou login
    header("Location: ../index.php"); 
    exit();
}

include 'modelsAlunos.php';

// --- LÓGICA PARA O MODAL (Dados do Admin logado) ---
$user_id_logado = $_SESSION['id_utilizador'];
$cargo          = $_SESSION['cargo']; 
$nome_exibicao  = "Administrador";    
$email_exibicao = "Email não disponível";

try {
    // A função estabelecerConexao() já está disponível via modelsAlunos.php
    $db = estabelecerConexao();
    
    // Procurar os dados do Administrador que está a usar o sistema para o modal
    $stmtLogado = $db->prepare("SELECT nome, email_institucional FROM administrador WHERE utilizador_id = ?");
    $stmtLogado->execute([$user_id_logado]);
    $dadosLogado = $stmtLogado->fetch(PDO::FETCH_ASSOC);

    if ($dadosLogado) {
        $nome_exibicao = $dadosLogado['nome'];
        $email_exibicao = $dadosLogado['email_institucional'];
    }
} catch (PDOException $e) {
    error_log("Erro ao carregar dados do modal: " . $e->getMessage());
}

if (!isset($_GET['id_aluno']) || !ctype_digit($_GET['id_aluno'])) {
    header('Location: index.php');
    exit;
}

$idAluno = (int) $_GET['id_aluno'];

$aluno = getAlunoById($idAluno);

if (!$aluno) {
    header('Location: index.php');
    exit;
}

$cvPath  = $aluno['cv'] ?? null;
$cvLabel = $cvPath ? basename($cvPath) : 'Sem CV';
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>GEU — Ver aluno</title>
    <link rel="stylesheet" href="css/verAluno.css" />
</head>
<body>

    <!-- ======= CABEÇALHO ======= -->
<header id="header">
        <div class="header-logo">
            <a href="index.php"><img src="../img/Logo.png" alt="GEU"></a>
        </div>

        <nav class="nav-menu">
            <?php if ($cargo === 'Administrador'): ?>
                <a href="../administradores/index.php" class="nav-link">Administradores</a>
            <?php endif; ?>

            <?php if ($cargo === 'Administrador' || $cargo === 'Professor'): ?>
                <a href="../empresas/index.php" class="nav-link">Empresas</a>
            <?php endif; ?>

            <?php if ($cargo === 'Administrador'): ?>
                <a href="../professores/index.php" class="nav-link">Professores</a>
                <a href="../alunos/index.php" class="nav-link active">Alunos</a>
            <?php endif; ?>

            <a href="../index.php" class="nav-link">Turmas</a>

            <button id="btn-conta" class="btn-conta">
                <img src="../img/img_conta.png" alt="Conta">
            </button>
            <a href="../logout.php" class="btn-sair">Sair</a>
        </nav>
</header>


    <!-- ======= CONTEÚDO PRINCIPAL ======= -->
    <main id="main-content">

        <!-- Subtabs -->
        <nav class="subtabs">
            <a href="index.php" class="subtab-link active">Ver Alunos</a>
            <a href="registarAluno.php" class="subtab-link">Registar novo aluno</a>
        </nav>

        <section class="content-grid">
            <form class="form-aluno">

                <div class="form-group">
                    <label for="codigo">Código Aluno</label>
                    <input id="codigo" type="text" value="<?= htmlspecialchars($aluno['id_aluno']) ?>" readonly>
                </div>


                <div class="form-group">
                    <label for="nome">Nome Aluno</label>
                    <input id="nome" type="text" value="<?= htmlspecialchars($aluno['nome'] ?? '') ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="dataNascimento">Data nascimento</label>
                    <input id="dataNascimento" type="text" value="<?= htmlspecialchars($aluno['data_nascimento'] ?? '') ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="sexo">Sexo</label>
                    <div class="select-wrapper">
                        <select id="sexo" disabled>
                            <option><?= htmlspecialchars($aluno['sexo'] ?? '') ?></option>
                        </select>
                        <span class="chevron">▾</span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="nacionalidade">Nacionalidade</label>
                    <input id="nacionalidade" type="text" value="<?= htmlspecialchars($aluno['nacionalidade_desc'] ?? '') ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="nif">NIF</label>
                    <input id="nif" type="text" value="<?= htmlspecialchars($aluno['nif'] ?? '') ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="cc">Número CC</label>
                    <input id="cc" type="text" value="<?= htmlspecialchars($aluno['numero_cc'] ?? '') ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="curso">Curso</label>
                    <input id="curso" type="text" value="<?= htmlspecialchars($aluno['curso_desc'] ?? '') ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="turmaId">Turma ID</label>
                    <div class="select-wrapper">
                        <select id="turmaId" disabled>
                            <option><?= htmlspecialchars($aluno['turma_id'] ?? '') ?></option>
                        </select>
                        <span class="chevron">▾</span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="codigoTurma">Código Turma</label>
                    <input id="codigoTurma" type="text" value="<?= htmlspecialchars($aluno['turma_codigo'] ?? '') ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="situacaoAcademica">Situação académica</label>
                    <div class="select-wrapper">
                        <select id="situacaoAcademica" disabled>
                            <option><?= htmlspecialchars($aluno['situacao_academica'] ?? '') ?></option>
                        </select>
                        <span class="chevron">▾</span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="escola">Escola</label>
                    <input id="escola" type="text" value="<?= htmlspecialchars($aluno['escola_desc'] ?? '') ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="emailInstitucional">Email institucional</label>
                    <input id="emailInstitucional" type="text" value="<?= htmlspecialchars($aluno['email_institucional'] ?? '') ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="emailPessoal">Email pessoal</label>
                    <input id="emailPessoal" type="text" value="<?= htmlspecialchars($aluno['email_pessoal'] ?? '') ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="morada">Morada</label>
                    <input id="morada" type="text" value="<?= htmlspecialchars($aluno['morada'] ?? '') ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="cp">Código-Postal</label>
                    <input id="cp" type="text" value="<?= htmlspecialchars($aluno['codigo_postal'] ?? '') ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="cidade">Cidade</label>
                    <input id="cidade" type="text" value="<?= htmlspecialchars($aluno['cidade'] ?? '') ?>" readonly>
                </div>

                <div class="form-group">
                    <label>CV</label>

                    <?php if (!empty($cvPath)): ?>
                        <a
                            href="../<?= htmlspecialchars($cvPath) ?>"
                            class="btn-download"
                            download
                        >
                            Transferir CV (<?= htmlspecialchars($cvLabel) ?>)
                        </a>
                    <?php else: ?>
                        <input type="text" value="Sem CV" readonly>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="linkedin">LinkedIn</label>
                    <input id="linkedin" type="text" value="<?= htmlspecialchars($aluno['linkedin'] ?? '') ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="portfolio">Portefólio (GitHub)</label>
                    <input id="portfolio" type="text" value="<?= htmlspecialchars($aluno['github'] ?? '') ?>" readonly>
                </div>

            </form>

            <aside class="side-panel">
                <div class="side-top">
                    <a href="editarAluno.php?id_aluno=<?= urlencode($aluno['id_aluno']) ?>" class="btn-editar" type="button">
                        Editar
                    </a>
                    <a class="btn-voltar" href="index.php">Voltar</a>
                </div>

            </aside>
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

    <!-- ======= MODAL PERFIL / CONTA ======= -->
    <div id="perfil-overlay" class="perfil-overlay">
        <div class="perfil-card">
            <div class="perfil-banner"></div>

            <div class="perfil-avatar">
                <img src="../img/img_conta.png" alt="Avatar" class="perfil-avatar-img">
            </div>

            <div class="perfil-content">
                <div class="perfil-role"><?= htmlspecialchars($cargo) ?></div>
                <div class="perfil-name"><?= htmlspecialchars($nome_exibicao) ?></div>

                <div class="perfil-row">
                    <img src="../img/img_email.png" alt="Email" class="perfil-row-img">
                    <span class="perfil-row-text"><?= htmlspecialchars($email_exibicao) ?></span>
                </div>

                <a href="../verPerfil.php" class="perfil-row">
                    <img src="../img/img_definicoes.png" alt="Definições" class="perfil-row-img">
                    <span class="perfil-row-text">Definições de conta</span>
                </a>

                <a href="../logout.php" class="perfil-logout-row">
                    <img src="../img/img_sair.png" alt="Sair" class="perfil-back-img">
                    <span class="perfil-logout-text">Log out</span>
                </a>

                <button type="button" class="perfil-voltar-btn">
                    Voltar
                </button>
            </div>
        </div>
    </div>


    <script src="js/verAluno.js"></script>
</body>
</html>
