<?php
session_start();
require_once 'db.php';

// 1. Redirecionamento de segurança
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$conexao = estabelecerConexao();

if (!isset($_GET['id_turma'])) {
    header('Location: index.php'); 
    exit;
}

$idTurma = $_GET['id_turma'];
$cargoUtilizador = $_SESSION['cargo'];
$idUtilizador = $_SESSION['id_utilizador'];

// --- LÓGICA DO PERFIL DINÂMICO (Para o Modal de Conta) ---
$nome_exibicao = "Utilizador";
$email_exibicao = "Email não disponível";
try {
    if ($cargoUtilizador === 'Aluno') {
        $stmtP = $conexao->prepare("SELECT nome, email_institucional FROM aluno WHERE utilizador_id = ?");
    } elseif ($cargoUtilizador === 'Professor') {
        $stmtP = $conexao->prepare("SELECT nome, email_institucional FROM professor WHERE utilizador_id = ?");
    } elseif ($cargoUtilizador === 'Administrador') {
        $stmtP = $conexao->prepare("SELECT nome, email_institucional FROM administrador WHERE utilizador_id = ?");
    } elseif ($cargoUtilizador === 'Empresa') {
        $stmtP = $conexao->prepare("SELECT nome, email FROM empresa WHERE utilizador_id = ?");
    }
    if (isset($stmtP)) {
        $stmtP->execute([$idUtilizador]);
        $dadosPerfil = $stmtP->fetch(PDO::FETCH_ASSOC);
        if ($dadosPerfil) {
            $nome_exibicao = $dadosPerfil['nome'];
            $email_exibicao = $dadosPerfil['email_institucional'] ?? $dadosPerfil['email'];
        }
    }
} catch (PDOException $e) { error_log($e->getMessage()); }

// 2. Buscar informações da Turma com Filtro de Professor
$sqlTurma = "SELECT t.*, c.curso_desc FROM turma t 
             LEFT JOIN curso c ON t.curso_id = c.id_curso 
             WHERE t.id_turma = :id";

if ($cargoUtilizador === 'Professor') {
    $stmtProf = $conexao->prepare("SELECT id_professor FROM professor WHERE utilizador_id = ?");
    $stmtProf->execute([$idUtilizador]);
    $prof = $stmtProf->fetch();
    $sqlTurma .= " AND t.professor_id = " . ($prof['id_professor'] ?? 0);
}

$stmt = $conexao->prepare($sqlTurma);
$stmt->execute([':id' => $idTurma]);
$turma = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$turma) {
    die("Acesso negado ou turma inexistente.");
}

// 3. Buscar Alunos com Filtros de Visibilidade
$sqlAlunos = "SELECT DISTINCT a.id_aluno, a.nome, pe.estado_pedido 
              FROM aluno a
              LEFT JOIN pedido_estagio pe ON a.id_aluno = pe.aluno_id";

$params = [':id_turma' => $idTurma];

if ($cargoUtilizador === 'Administrador' || $cargoUtilizador === 'Professor') {
    $sqlAlunos .= " WHERE a.turma_id = :id_turma";
} 
elseif ($cargoUtilizador === 'Empresa') {
    $sqlAlunos .= " INNER JOIN empresa e ON pe.empresa_id = e.id_empresa 
                    WHERE a.turma_id = :id_turma AND e.utilizador_id = :id_u";
    $params[':id_u'] = $idUtilizador;
} 
elseif ($cargoUtilizador === 'Aluno') {
    $sqlAlunos .= " WHERE a.turma_id = :id_turma AND a.utilizador_id = :id_u";
    $params[':id_u'] = $idUtilizador;
}

$sqlAlunos .= " ORDER BY a.nome ASC";
$stmtAlunos = $conexao->prepare($sqlAlunos);
$stmtAlunos->execute($params);
$alunos = $stmtAlunos->fetchAll(PDO::FETCH_ASSOC);

// Funções Auxiliares
function obterClasseEstado($estado) {
    if (empty($estado)) return 'sem';
    $estado = mb_strtolower($estado, 'UTF-8');
    if (strpos($estado, 'aceite') !== false || strpos($estado, 'aprovado') !== false) return 'aceite';
    if (strpos($estado, 'aguarda') !== false || strpos($estado, 'pendente') !== false) return 'aguardando';
    return 'sem';
}
function obterTextoEstado($estado) {
    return !empty($estado) ? $estado : 'Sem estágio';
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>GEU — <?= htmlspecialchars($turma['nome']) ?></title>
    <link rel="stylesheet" href="css/turma.css" />
    <link rel="stylesheet" href="css/index.css" />
</head>
<body>

    <header id="header">
        <div class="header-logo">
            <a href="index.php"><img src="img/Logo.png" alt="GEU"></a>
        </div>

        <nav class="nav-menu">
            <?php if ($cargoUtilizador === 'Administrador'): ?>
                <a href="administradores/index.php" class="nav-link">Administradores</a>
            <?php endif; ?>

            <?php if ($cargoUtilizador === 'Administrador' || $cargoUtilizador === 'Professor'): ?>
                <a href="empresas/index.php" class="nav-link">Empresas</a>
            <?php endif; ?>

            <?php if ($cargoUtilizador === 'Administrador'): ?>
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
        <div class="page-head">
            <h1 class="turma-titulo">
                <?= htmlspecialchars($turma['nome']) ?> 
                (<?= htmlspecialchars($turma['ano_inicio']) ?>–<?= htmlspecialchars($turma['ano_fim']) ?>)
            </h1>
            
            <?php if ($cargoUtilizador === 'Administrador'): ?>
                <button class="btn-editar" onclick="abrirModal()">Editar Turma</button>
            <?php endif; ?>
        </div>

        <section class="alunos-grid">
            <?php if (count($alunos) > 0): ?>
                <?php foreach ($alunos as $aluno): ?>
                    <?php 
                        $classeCss = obterClasseEstado($aluno['estado_pedido']);
                        $textoEstado = obterTextoEstado($aluno['estado_pedido']);
                    ?>
                    <a href="aluno.php?id_aluno=<?= $aluno['id_aluno'] ?>">
                        <article class="aluno-card">
                            <h3 class="aluno-nome">
                                <?= htmlspecialchars($aluno['nome']) ?> - <?= htmlspecialchars($aluno['id_aluno']) ?>
                            </h3>
                            <p class="aluno-label">Estado do estágio</p>
                            <p class="aluno-estado <?= $classeCss ?>">
                                <?= htmlspecialchars($textoEstado) ?>
                            </p>
                        </article>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="grid-column: 1/-1; color: #666; padding: 20px;">Não existem alunos visíveis nesta turma.</p>
            <?php endif; ?>
        </section>
    </main>

    <?php if ($cargoUtilizador === 'Administrador'): ?>
    <div id="modal-editar-turma" class="modal-overlay" style="display:none;">
        <div class="modal-content-box">
            <div class="modal-flex">
                <form class="modal-form" action="editarTurma.php" method="POST">
                    <input type="hidden" name="id_turma" value="<?= $idTurma ?>">
                    <label>Código:</label>
                    <input type="text" name="codigo" value="<?= htmlspecialchars($turma['codigo']) ?>">
                    <label>Turma:</label>
                    <input type="text" name="nome" value="<?= htmlspecialchars($turma['nome']) ?>">
                    <div class="modal-buttons" style="position: static; margin-top: 20px;">
                        <button type="submit" class="modal-btn guardar">Guardar</button>
                        <button type="button" class="modal-btn voltar" onclick="fecharModal()">Voltar</button>
                    </div>
                </form>
                <img class="modal-img" src="img/img_editar_turma.png" alt="Editar">
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
                <div class="perfil-role"><?= htmlspecialchars($cargoUtilizador) ?></div>
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
                <button type="button" class="perfil-voltar-btn" onclick="document.getElementById('perfil-overlay').classList.remove('show')">Voltar</button>
            </div>
        </div>
    </div>

    <footer id="footer">
        <div class="contactos">
            <h3>Contactos</h3>
            <p><img src="img/img_email.png" alt="Email" style="width:26px;"> <strong>Email:</strong> geral@ipsantarem.pt</p>
            <p><img src="img/img_telemovel.png" alt="Telefone" style="width:26px;"> <strong>Telefone:</strong> +351 243 309 520</p>
            <p><img src="img/img_localizacao.png" alt="Endereço" style="width:26px;"> <strong>Endereço:</strong> Complexo Andaluz, Santarém</p>
        </div>
        <div class="logos">
            <img src="img/Logo.png" alt="GEU">
            <img src="img/img_confinanciado.png" alt="Confinanciado">
        </div>
    </footer>

    <script src="js/index.js"></script>
    <script>
        function abrirModal() { document.getElementById('modal-editar-turma').style.display = 'flex'; }
        function fecharModal() { document.getElementById('modal-editar-turma').style.display = 'none'; }
    </script>
</body>
</html>