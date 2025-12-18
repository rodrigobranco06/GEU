<?php
session_start();
include 'db.php'; // Substituído require_once por include conforme solicitado

// 1. Redirecionamento de segurança
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

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
        $perfil = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($perfil) {
            $nome_exibicao = $perfil['nome'];
            $email_exibicao = $perfil['email_institucional'] ?? $perfil['email'];
        }
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
}

// --- LÓGICA DE FILTRAGEM DE TURMAS POR CARGO ---
try {
    $sqlTurmas = "SELECT DISTINCT t.id_turma, t.nome AS turma_nome, t.codigo, t.ano_inicio, p.nome AS professor_nome
                  FROM turma t 
                  LEFT JOIN professor p ON t.professor_id = p.id_professor";

    if ($cargo === 'Administrador') {
        // Vê todas as turmas
        $stmtT = $conexao->prepare($sqlTurmas . " ORDER BY t.ano_inicio DESC");
        $stmtT->execute();
    } 
    elseif ($cargo === 'Professor') {
        // Vê apenas as turmas que orienta
        $sqlTurmas .= " JOIN professor prof_logado ON t.professor_id = prof_logado.id_professor 
                        WHERE prof_logado.utilizador_id = ? ORDER BY t.ano_inicio DESC";
        $stmtT = $conexao->prepare($sqlTurmas);
        $stmtT->execute([$user_id]);
    } 
    elseif ($cargo === 'Aluno') {
        // Vê apenas a sua turma
        $sqlTurmas .= " JOIN aluno a ON t.id_turma = a.turma_id 
                        WHERE a.utilizador_id = ? ORDER BY t.ano_inicio DESC";
        $stmtT = $conexao->prepare($sqlTurmas);
        $stmtT->execute([$user_id]);
    } 
    elseif ($cargo === 'Empresa') {
        // Vê turmas que têm alunos com pedidos para esta empresa
        $sqlTurmas .= " JOIN aluno a ON t.id_turma = a.turma_id
                        JOIN pedido_estagio pe ON a.id_aluno = pe.aluno_id
                        JOIN empresa e ON pe.empresa_id = e.id_empresa
                        WHERE e.utilizador_id = ? ORDER BY t.ano_inicio DESC";
        $stmtT = $conexao->prepare($sqlTurmas);
        $stmtT->execute([$user_id]);
    }

    $turmas = $stmtT->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log($e->getMessage());
    $turmas = [];
}

// Dados para modais (apenas Admin/Professor)
$cursos = [];
$professores = [];
if (in_array($cargo, ['Administrador', 'Professor'])) {
    $cursos = $conexao->query("SELECT id_curso, curso_desc FROM curso ORDER BY curso_desc")->fetchAll(PDO::FETCH_ASSOC);
    $professores = $conexao->query("SELECT id_professor, nome FROM professor ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
}
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
            <h2 class="titulo-pagina">Minhas Turmas</h2>
            <?php if ($cargo === 'Administrador' || $cargo === 'Professor'): ?>
                <button class="btn-criar-turma">Criar Nova Turma</button>
            <?php endif; ?>
        </div>

        <div class="turmas-container">
            <?php if (empty($turmas)): ?>
                <div class="sem-dados">Não foram encontradas turmas associadas ao seu perfil.</div>
            <?php else: ?>
                <?php foreach ($turmas as $t): ?>
                    <a href="turma.php?id_turma=<?= $t['id_turma'] ?>" class="turma-link">
                        <div class="turma-card">
                            <h3 class="turma-nome"><?= htmlspecialchars($t['turma_nome']) ?></h3>
                            <p class="turma-professor-label">Professor orientador</p>
                            <p class="turma-professor-nome"><?= htmlspecialchars($t['professor_nome'] ?: 'Indefinido') ?></p>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <?php if (in_array($cargo, ['Administrador', 'Professor'])): ?>
    <div id="modal-criar-turma" class="modal-overlay" style="display:none;">
        <div class="modal-content-box">
            <div class="modal-flex">
                <form class="modal-form" action="adicionarTurma.php" method="POST">
                    <label>Curso (Sigla):</label>
                    <select name="curso_id" id="curso" required>
                        <option value="" disabled selected>Selecione o curso...</option>
                        <?php foreach ($cursos as $c): ?>
                            <option value="<?= $c['id_curso'] ?>" data-sigla="<?= htmlspecialchars($c['curso_desc']) ?>">
                                <?= htmlspecialchars($c['curso_desc']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label>Ano Curricular:</label>
                    <input type="number" name="ano_curricular" id="ano-curricular" placeholder="Ex: 2" min="1" max="5" required>

                    <label>Ano Início:</label>
                    <input type="number" name="ano_inicio" id="ano-inicio" placeholder="Ex: 2024" required>
                    
                    <label>Ano Fim:</label>
                    <input type="number" name="ano_fim" id="ano-fim" placeholder="Ex: 2026">
                    
                    <label>Código Gerado:</label>
                    <input type="text" name="codigo" id="codigo-turma" readonly>
                    
                    <label>Nome da Turma:</label>
                    <input type="text" name="nome" id="nome-turma" readonly required>

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

    <script src="js/index.js"></script>
</body>
</html>