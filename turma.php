<?php
session_start();
include 'db.php';
include 'modelsTurma.php'; 

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$conexao = estabelecerConexao();
$idTurma = isset($_GET['id_turma']) ? (int)$_GET['id_turma'] : 0;

// Ajuste aqui: nomes das variáveis para coincidir com o que as funções esperam
$idUtilizador = $_SESSION['id_utilizador']; 
$cargoUtilizador = $_SESSION['cargo']; 

// --- LÓGICA DO PERFIL DINÂMICO ---
$nome_exibicao = "Utilizador";
$email_exibicao = "Email não disponível";

try {
    if ($cargoUtilizador === 'Aluno') {
        $stmt = $conexao->prepare("SELECT nome, email_institucional FROM aluno WHERE utilizador_id = ?");
    } elseif ($cargoUtilizador === 'Professor') {
        $stmt = $conexao->prepare("SELECT nome, email_institucional FROM professor WHERE utilizador_id = ?");
    } elseif ($cargoUtilizador === 'Administrador') {
        $stmt = $conexao->prepare("SELECT nome, email_institucional FROM administrador WHERE utilizador_id = ?");
    } elseif ($cargoUtilizador === 'Empresa') {
        $stmt = $conexao->prepare("SELECT nome, email FROM empresa WHERE utilizador_id = ?");
    }

    if (isset($stmt)) {
        $stmt->execute([$idUtilizador]);
        $perfilDados = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($perfilDados) {
            $nome_exibicao = $perfilDados['nome'];
            $email_exibicao = $perfilDados['email_institucional'] ?? $perfilDados['email'];
        }
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
}

if ($idTurma <= 0) { header('Location: index.php'); exit; }

// Agora as variáveis $idUtilizador e $cargoUtilizador já existem e têm valor
$perfil = getPerfilUtilizador($conexao, $idUtilizador, $cargoUtilizador);
$turma  = getDadosTurma($conexao, $idTurma, $cargoUtilizador, $idUtilizador);

if (!$turma) { die("Acesso negado ou turma inexistente."); }

$alunos = getAlunosTurma($conexao, $idTurma, $cargoUtilizador, $idUtilizador);

// Dados para o modal de edição (Apenas Admin)
$cursos = [];
$professores = [];
if ($cargoUtilizador === 'Administrador') {
    $cursos = $conexao->query("SELECT id_curso, curso_desc FROM curso ORDER BY curso_desc ASC")->fetchAll(PDO::FETCH_ASSOC);
    $professores = $conexao->query("SELECT id_professor, nome FROM professor ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);
}

function obterClasseEstado($estado) {
    if (empty($estado)) return 'sem';
    $estado = mb_strtolower($estado, 'UTF-8');
    return (strpos($estado, 'concluído') !== false || strpos($estado, 'aceite') !== false) ? 'aceite' : 
           ((strpos($estado, 'aguarda') !== false || strpos($estado, 'pendente') !== false) ? 'aguardando' : 'sem');
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
        <div class="header-logo"><a href="index.php"><img src="img/Logo.png" alt="GEU"></a></div>
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
            <button id="btn-conta" class="btn-conta"><img src="img/img_conta.png" alt="Conta"></button>
            <a href="logout.php" class="btn-sair">Sair</a>
        </nav>
    </header>

    <main id="main-content">
        <div class="page-head">
            <h1 class="turma-titulo"><?= htmlspecialchars($turma['nome']) ?></h1>
            <div class="acoes">
                <a href="index.php" class="btn-outline">Voltar</a>
                <?php if ($cargoUtilizador === 'Administrador'): ?>
                    <button class="btn-editar" onclick="abrirModal()">Editar Turma</button>
                <?php endif; ?>
            </div>
        </div>

        <section class="alunos-grid">
            <?php if (count($alunos) > 0): ?>
                <?php foreach ($alunos as $aluno): ?>
                    <a href="aluno.php?id_aluno=<?= $aluno['id_aluno'] ?>">
                        <article class="aluno-card">
                            <h3 class="aluno-nome"><?= htmlspecialchars($aluno['nome']) ?> - <?= htmlspecialchars($aluno['id_aluno']) ?></h3>
                            <p class="aluno-label">Estado do estágio</p>
                            <p class="aluno-estado <?= obterClasseEstado($aluno['estado_pedido']) ?>">
                                <?= htmlspecialchars($aluno['estado_pedido'] ?: 'Sem estágio') ?>
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
                <form class="modal-form" action="processarTurma.php" method="POST" id="form-editar-turma">
                    <input type="hidden" name="id_turma" value="<?= $idTurma ?>">
                    <input type="hidden" name="acao" id="form-acao" value="editar">

                    <label>Curso:</label>
                    <select name="curso_id" id="curso" required>
                        <?php foreach ($cursos as $c): ?>
                            <option value="<?= $c['id_curso'] ?>" data-sigla="<?= htmlspecialchars($c['curso_desc']) ?>" <?= $c['id_curso'] == $turma['curso_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['curso_desc']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label>Ano Curricular:</label>
                    <input type="number" name="ano_curricular" id="ano-curricular" value="<?= $turma['ano_curricular'] ?>" required>

                    <div style="display: flex; gap: 10px;">
                        <div style="flex: 1;">
                            <label>Ano Início:</label>
                            <input type="number" name="ano_inicio" id="ano-inicio" value="<?= $turma['ano_inicio'] ?>" required>
                        </div>
                        <div style="flex: 1;">
                            <label>Ano Fim:</label>
                            <input type="number" name="ano_fim" id="ano-fim" value="<?= $turma['ano_fim'] ?>">
                        </div>
                    </div>

                    <label>Professor Orientador:</label>
                    <select name="professor_id">
                        <option value="">Indefinido</option>
                        <?php foreach ($professores as $p): ?>
                            <option value="<?= $p['id_professor'] ?>" <?= $p['id_professor'] == $turma['professor_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label>Nome da Turma (Automático):</label>
                    <input type="text" name="nome" id="nome-turma" value="<?= htmlspecialchars($turma['nome']) ?>" readonly>

                    <div class="modal-buttons" style="margin-top: 20px; display: flex; flex-direction: column; gap: 10px;">
                        <div style="display: flex; gap: 10px; width: 100%;">
                            <button type="submit" class="modal-btn guardar" style="flex: 1;">Guardar</button>
                            <button type="button" class="modal-btn voltar" onclick="fecharModal()" style="flex: 1;">Cancelar</button>
                        </div>
                        <button type="button" class="modal-btn" onclick="confirmarEliminar()" style="background-color: #dc2626; color: white; border: none; padding: 10px; border-radius: 8px; cursor: pointer;">
                            Eliminar Turma
                        </button>
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
            <div class="perfil-avatar"><img src="img/img_conta.png" alt="Avatar" class="perfil-avatar-img"></div>
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
        <div class="logos"><img src="img/Logo.png" alt="GEU"><img src="img/img_confinanciado.png" alt="Confinanciado"></div>
    </footer>

    <script>
        function abrirModal() { document.getElementById('modal-editar-turma').style.display = 'flex'; }
        function fecharModal() { document.getElementById('modal-editar-turma').style.display = 'none'; }
        
        function confirmarEliminar() {
            if (confirm("ATENÇÃO: Deseja eliminar esta turma permanentemente?")) {
                document.getElementById('form-acao').value = 'eliminar';
                document.getElementById('form-editar-turma').submit();
            }
        }

        // Geração automática do nome no modal
        const inputCurso = document.getElementById('curso');
        const inputAnoC = document.getElementById('ano-curricular');
        const inputAnoI = document.getElementById('ano-inicio');
        const inputAnoF = document.getElementById('ano-fim');
        const inputNome = document.getElementById('nome-turma');

        function atualizarNome() {
            const sigla = inputCurso.options[inputCurso.selectedIndex].getAttribute('data-sigla');
            if (sigla && inputAnoC.value && inputAnoI.value && inputAnoF.value) {
                inputNome.value = sigla + " - " + inputAnoC.value + " (" + inputAnoI.value + "/" + inputAnoF.value + ")";
            }
        }
        [inputCurso, inputAnoC, inputAnoI, inputAnoF].forEach(el => el?.addEventListener('input', atualizarNome));

        document.getElementById('btn-conta').onclick = () => document.getElementById('perfil-overlay').classList.add('show');
    </script>
</body>
</html>