<?php
session_start();

// 1. Redirecionamento de segurança
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

require_once 'db.php';
$conexao = estabelecerConexao();

$user_id = $_SESSION['id_utilizador'];
$cargo   = $_SESSION['cargo']; 

// --- LÓGICA DO PERFIL DINÂMICO ---
$nome_exibicao = "Utilizador";
$email_exibicao = "Email não disponível";

try {
    if ($cargo === 'Aluno') {
        $stmt = $conexao->prepare("SELECT nome, email_institucional FROM aluno WHERE utilizador_id = ?");
    } elseif ($cargo === 'Professor') {
        $stmt = $conexao->prepare("SELECT nome, email_institucional FROM professor WHERE utilizador_id = ?");
    } elseif ($cargo === 'Administrador') {
        $stmt = $conexao->prepare("SELECT nome, email_institucional FROM administrador WHERE utilizador_id = ?");
    } elseif ($cargo === 'Empresa') {
        $stmt = $conexao->prepare("SELECT nome, email FROM empresa WHERE utilizador_id = ?");
    }

    if (isset($stmt)) {
        $stmt->execute([$user_id]);
        $dados = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($dados) {
            $nome_exibicao = $dados['nome'];
            $email_exibicao = $dados['email_institucional'] ?? $dados['email'];
        }
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
}

// --- DADOS PARA TURMAS E MODAIS ---
$sqlTurmas = "SELECT t.id_turma, t.nome AS turma_nome, t.codigo, t.ano_inicio, p.nome AS professor_nome
              FROM turma t LEFT JOIN professor p ON t.professor_id = p.id_professor
              ORDER BY t.ano_inicio DESC";
$turmas = $conexao->query($sqlTurmas)->fetchAll(PDO::FETCH_ASSOC);

$cursos = $conexao->query("SELECT id_curso, curso_desc FROM curso ORDER BY curso_desc")->fetchAll(PDO::FETCH_ASSOC);
$professores = $conexao->query("SELECT id_professor, nome FROM professor ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GEU - Turmas</title>
    <link rel="stylesheet" href="css/index.css">
</head>
<body>

    <header id="header">
        <div class="header-logo">
            <a href="index.php"><img src="img/Logo.png" alt="GEU"></a>
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
            <?php endif; ?>

            <?php if ($cargo === 'Administrador'): ?>
                <a href="alunos/index.php" class="nav-link">Alunos</a>
            <?php endif; ?>

            <a href="index.php" class="nav-link active">Turmas</a>

            <button id="btn-conta" class="btn-conta">
                <img src="img/img_conta.png" alt="Conta">
            </button>
            <a href="logout.php" class="btn-sair">Sair</a>
        </nav>
    </header>

    <main id="main-content">
        <div class="main-header">
            <h2 class="titulo-pagina">Turmas</h2>
            <?php if ($cargo === 'Administrador' || $cargo === 'Professor'): ?>
                <button class="btn-criar-turma">Criar Nova Turma</button>
            <?php endif; ?>
        </div>

        <div class="turmas-container">
            <?php foreach ($turmas as $t): ?>
                <a href="turma.php?id_turma=<?= $t['id_turma'] ?>" class="turma-link">
                    <div class="turma-card">
                        <h3 class="turma-nome"><?= htmlspecialchars($t['turma_nome']) ?></h3>
                        <p class="turma-professor-label">Professor orientador</p>
                        <p class="turma-professor-nome"><?= htmlspecialchars($t['professor_nome'] ?: 'Indefinido') ?></p>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </main>

    <?php if ($cargo === 'Administrador' || $cargo === 'Professor'): ?>
    <div id="modal-criar-turma" class="modal-overlay" style="display:none;">
        <div class="modal-content-box">
            <div class="modal-flex">
                <form class="modal-form" action="adicionarTurma.php" method="POST">
                    <label>Curso:</label>
                    <select name="curso_id" id="curso" required>
                        <option value="" disabled selected>Selecione...</option>
                        <?php foreach ($cursos as $c): ?>
                            <option value="<?= $c['id_curso'] ?>"><?= htmlspecialchars($c['curso_desc']) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <label>Ano Início:</label>
                    <input type="number" name="ano_inicio" id="ano-inicio" required>
                    <label>Ano Fim:</label>
                    <input type="number" name="ano_fim" id="ano-fim">
                    <label>Código Gerado:</label>
                    <input type="text" name="codigo" id="codigo-turma" readonly>
                    <label>Nome da Turma:</label>
                    <input type="text" name="nome" required>

                    <label>Professor Orientador:</label>
                    <select name="professor_id">
                        <option value="">Indefinido</option>
                        <?php foreach ($professores as $p): ?>
                            <option value="<?= $p['id_professor'] ?>"><?= htmlspecialchars($p['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    
                    <div class="modal-buttons">
                        <button class="modal-btn criar" type="submit">Criar</button>
                        <button class="modal-btn voltar" type="button" id="btn-fechar-modal">Voltar</button>
                    </div>
                </form>
                <img class="modal-img" src="img/img_editar_turma.png" alt="Turma">
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div id="perfil-overlay" class="perfil-overlay">
        <div class="perfil-card">
            <div class="perfil-banner"></div>
            <div class="perfil-avatar">
                <img src="img/img_conta.png" alt="Avatar" class="perfil-avatar-img">
            </div>
            <div class="perfil-content">
                <div class="perfil-role"><?= htmlspecialchars($cargo) ?></div>
                <div class="perfil-name"><?= htmlspecialchars($nome_exibicao) ?></div>
                <div class="perfil-row">
                    <img src="img/img_email.png" alt="Email" class="perfil-row-img">
                    <span class="perfil-row-text"><?= htmlspecialchars($email_exibicao) ?></span>
                </div>
                <a href="verPerfil.php" class="perfil-row">
                    <img src="img/img_definicoes.png" alt="Definições" class="perfil-row-img">
                    <span class="perfil-row-text">Definições de conta</span>
                </a>
                <a href="logout.php" class="perfil-logout-row">
                    <img src="img/img_sair.png" alt="Sair" class="perfil-back-img">
                    <span class="perfil-logout-text">Log out</span>
                </a>
                <button type="button" class="perfil-voltar-btn">Voltar</button>
            </div>
        </div>
    </div>

    <script src="js/index.js"></script>
</body>
</html>