<?php
// professores/verProfessor.php

session_start();

// 1. Verificação de segurança: Logado + Cargo de Administrador
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['cargo'] !== 'Administrador') {
    // Se não for admin, redireciona para a página principal ou login
    header("Location: ../index.php"); 
    exit();
}


include 'modelsProfessores.php';

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


$idProfessor = isset($_GET['id_professor']) ? (int) $_GET['id_professor'] : 0;

if ($idProfessor <= 0) {
    die('ID de professor inválido.');
}

$professor = getProfessorById($idProfessor);

if (!$professor) {
    die('Professor não encontrado.');
}

// Formatar data para DD/MM/YYYY
$dataNascimentoFormatada = '';
if (!empty($professor['data_nascimento'])) {
    $dataNascimentoFormatada = date('d/m/Y', strtotime($professor['data_nascimento']));
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>GEU — Ver professor</title>
    <link rel="stylesheet" href="css/verProfessor.css" />
</head>
<body>

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
                <a href="../professores/index.php" class="nav-link active">Professores</a>
                <a href="../alunos/index.php" class="nav-link">Alunos</a>
            <?php endif; ?>

            <a href="../index.php" class="nav-link">Turmas</a>

            <button id="btn-conta" class="btn-conta">
                <img src="../img/img_conta.png" alt="Conta">
            </button>
            <a href="logout.php" class="btn-sair">Sair</a>
        </nav>
</header>

<main id="main-content">

    <nav class="subtabs">
        <a href="index.php" class="subtab-link active">Ver Professores</a>
        <a href="registarProfessor.php" class="subtab-link">Registar novo professor</a>
    </nav>

    <section class="content-grid">
        <!-- Coluna esquerda: “formulário” só de leitura -->
        <form class="form-professor">

            <div class="form-group">
                <label for="codigoProf">Código Professor</label>
                <input
                    id="codigoProf"
                    type="text"
                    value="<?= htmlspecialchars($professor['id_professor'] ?? '') ?>"
                    readonly
                >
            </div>

            <div class="form-group">
                <label for="nomeProf">Nome Professor</label>
                <input
                    id="nomeProf"
                    type="text"
                    value="<?= htmlspecialchars($professor['nome'] ?? '') ?>"
                    readonly
                >
            </div>

            <div class="form-group">
                <label for="dataNascimento">Data nascimento</label>
                <input
                    id="dataNascimento"
                    type="text"
                    value="<?= htmlspecialchars($dataNascimentoFormatada) ?>"
                    readonly
                >
            </div>

            <div class="form-group">
                <label for="sexo">Sexo</label>
                <div class="select-wrapper">
                    <select id="sexo" disabled>
                        <option>
                            <?= htmlspecialchars($professor['sexo'] ?: 'Não definido') ?>
                        </option>
                    </select>
                    <span class="chevron">▾</span>
                </div>
            </div>

            <div class="form-group">
                <label for="nacionalidade">Nacionalidade</label>
                <input
                    id="nacionalidade"
                    type="text"
                    value="<?= htmlspecialchars($professor['nacionalidade_desc'] ?? '') ?>"
                    readonly
                >
            </div>

            <div class="form-group">
                <label for="especializacao">Especialização</label>
                <input
                    id="especializacao"
                    type="text"
                    value="<?= htmlspecialchars($professor['especializacao_desc'] ?? '') ?>"
                    readonly
                >
            </div>

            <div class="form-group">
                <label for="nif">NIF</label>
                <input
                    id="nif"
                    type="text"
                    value="<?= htmlspecialchars($professor['nif'] ?? '') ?>"
                    readonly
                >
            </div>

            <div class="form-group">
                <label for="cc">Número CC</label>
                <input
                    id="cc"
                    type="text"
                    value="<?= htmlspecialchars($professor['numero_cc'] ?? '') ?>"
                    readonly
                >
            </div>

            <div class="form-group">
                <label for="escola">Escola</label>
                <input
                    id="escola"
                    type="text"
                    value="<?= htmlspecialchars($professor['escola_desc'] ?? '') ?>"
                    readonly
                >
            </div>

            <div class="form-group">
                <label for="emailInstitucional">Email institucional</label>
                <input
                    id="emailInstitucional"
                    type="text"
                    value="<?= htmlspecialchars($professor['email_institucional'] ?? '') ?>"
                    readonly
                >
            </div>

            <div class="form-group">
                <label for="emailPessoal">Email pessoal</label>
                <input
                    id="emailPessoal"
                    type="text"
                    value="<?= htmlspecialchars($professor['email_pessoal'] ?? '') ?>"
                    readonly
                >
            </div>

            <div class="form-group">
                <label for="morada">Morada</label>
                <input
                    id="morada"
                    type="text"
                    value="<?= htmlspecialchars($professor['morada'] ?? '') ?>"
                    readonly
                >
            </div>

            <div class="form-group">
                <label for="cp">Código-Postal</label>
                <input
                    id="cp"
                    type="text"
                    value="<?= htmlspecialchars($professor['codigo_postal'] ?? '') ?>"
                    readonly
                >
            </div>

            <div class="form-group">
                <label for="cidade">Cidade</label>
                <input
                    id="cidade"
                    type="text"
                    value="<?= htmlspecialchars($professor['cidade'] ?? '') ?>"
                    readonly
                >
            </div>

        </form>

        <!-- Coluna direita: botões + imagem -->
        <aside class="side-panel">
            <div class="side-top">
                <a
                    href="editarProfessor.php?id_professor=<?= urlencode($professor['id_professor']) ?>"
                    class="btn-editar"
                >
                    Editar
                </a>
                <a class="btn-voltar" href="index.php">
                    Voltar
                </a>
            </div>

            <div class="side-image-wrapper">
                <img src="../img/img_registarAluno.png" alt="Ilustração professor">
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


<script src="js/verProfessor.js"></script>
</body>
</html>
