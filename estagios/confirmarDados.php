<?php
session_start();
include '../db.php';

// 1. Verificação de segurança
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../login.php");
    exit();
}

$conexao = estabelecerConexao();
$cargoLogado = $_SESSION['cargo'];
$idUserLogado = $_SESSION['id_utilizador'];

// 2. Obter ID do pedido via URL
$id_pedido = isset($_GET['id_pedido_estagio']) ? (int)$_GET['id_pedido_estagio'] : 0;
if ($id_pedido <= 0) {
    die("Pedido de estágio inválido.");
}

// 3. Procurar dados completos
$sql = "SELECT p.id_pedido_estagio, a.id_aluno, a.nome as aluno_nome, a.email_institucional, 
               c.curso_desc, t.ano_curricular,
               fc.numero_ucs_atraso, fc.estado_confirmacao, fc.data_confirmacao
        FROM pedido_estagio p
        JOIN aluno a ON p.aluno_id = a.id_aluno
        JOIN curso c ON a.curso_id = c.id_curso
        JOIN turma t ON a.turma_id = t.id_turma
        LEFT JOIN fase_confirmacao fc ON p.id_pedido_estagio = fc.id_pedido_estagio
        WHERE p.id_pedido_estagio = :id_pedido";

$stmt = $conexao->prepare($sql);
$stmt->execute([':id_pedido' => $id_pedido]);
$dados = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$dados) {
    die("Pedido não encontrado.");
}

// 4. Lógica do Perfil Dinâmico (Necessária para preencher o Modal abaixo)
$nome_exibicao = "Utilizador";
$email_exibicao = "Email não disponível";

try {
    if ($cargoLogado === 'Aluno') {
        $stmtP = $conexao->prepare("SELECT nome, email_institucional FROM aluno WHERE utilizador_id = ?");
    } elseif ($cargoLogado === 'Professor') {
        $stmtP = $conexao->prepare("SELECT nome, email_institucional FROM professor WHERE utilizador_id = ?");
    } elseif ($cargoLogado === 'Administrador') {
        $stmtP = $conexao->prepare("SELECT nome, email_institucional FROM administrador WHERE utilizador_id = ?");
    } elseif ($cargoLogado === 'Empresa') {
        $stmtP = $conexao->prepare("SELECT nome, email FROM empresa WHERE utilizador_id = ?");
    }
    
    if (isset($stmtP)) {
        $stmtP->execute([$idUserLogado]);
        $dadosPerfil = $stmtP->fetch(PDO::FETCH_ASSOC);
        if ($dadosPerfil) {
            $nome_exibicao = $dadosPerfil['nome'];
            $email_exibicao = $dadosPerfil['email_institucional'] ?? $dadosPerfil['email'] ?? 'Email não disponível';
        }
    }
} catch (PDOException $e) { error_log($e->getMessage()); }
?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>GEU — Pedido de estágio: Confirmar dados</title>
    <link rel="stylesheet" href="css/confirmarDados.css" />
    <link rel="stylesheet" href="../css/index.css" />
</head>

<body>

    <header id="header">
        <div class="header-logo">
            <a href="../index.php"><img src="../img/Logo.png" alt="GEU"></a>
        </div>

        <nav class="nav-menu">
            <?php if ($cargoLogado === 'Administrador'): ?>
                <a href="../administradores/index.php" class="nav-link">Administradores</a>
            <?php endif; ?>
            <?php if ($cargoLogado === 'Administrador' || $cargoLogado === 'Professor'): ?>
                <a href="../empresas/index.php" class="nav-link">Empresas</a>
            <?php endif; ?>
            <?php if ($cargoLogado === 'Administrador'): ?>
                <a href="../professores/index.php" class="nav-link">Professores</a>
                <a href="../alunos/index.php" class="nav-link active">Alunos</a>
            <?php endif; ?>
            <a href="../index.php" class="nav-link">Turmas</a>

            <button class="btn-conta" id="btn-conta">
                <img src="../img/img_conta.png" alt="Conta">
            </button>
            <a href="../logout.php" class="btn-sair">Sair</a>
        </nav>
    </header>

    <main id="main-content">
        <nav class="steps">
            <a class="step active" href="confirmarDados.php?id_pedido_estagio=<?= $id_pedido ?>">Confirmar Dados</a>
            <a class="step" href="escolhaAreaEmpresa.php?id_pedido_estagio=<?= $id_pedido ?>">Escolha de área e empresa</a>
            <a class="step" href="envioEmail.php?id_pedido_estagio=<?= $id_pedido ?>">Envio de email</a>
            <a class="step" href="respostaEmail.php?id_pedido_estagio=<?= $id_pedido ?>">Resposta ao email</a>
            <a class="step" href="planoEstagio.php?id_pedido_estagio=<?= $id_pedido ?>">Plano estágio</a>
            <a class="step" href="avaliacao.php?id_pedido_estagio=<?= $id_pedido ?>">Avaliação</a>
        </nav>

        <form action="processarConfirmarDados.php" method="POST" id="form-principal">
            <input type="hidden" name="id_pedido_estagio" value="<?= $id_pedido ?>">

            <div class="page-head">
                <h1 class="titulo"><?= htmlspecialchars($dados['aluno_nome']) ?> - <?= htmlspecialchars($dados['id_aluno']) ?></h1>
                <div class="acoes">
                    <a class="btn-outline" href="../aluno.php?id_aluno=<?= $dados['id_aluno'] ?>">Voltar</a>
                    <?php if (in_array($cargoLogado, ['Professor', 'Administrador']) && $dados['estado_confirmacao'] !== 'Confirmado'): ?>
                        <button class="btn-primary" type="button" id="btn-confirmar">Confirmar Dados</button>
                    <?php endif; ?>
                </div>
            </div>

            <section class="card">
                <ul class="kv">
                    <li><span class="k">Número de pedido:</span><input class="v" value="<?= $id_pedido ?>" readonly></li>
                    <li><span class="k">Código Aluno:</span><input class="v" value="<?= $dados['id_aluno'] ?>" readonly></li>
                    <li><span class="k">Nome Aluno:</span><input class="v" value="<?= htmlspecialchars($dados['aluno_nome']) ?>" readonly></li>
                    <li><span class="k">Curso:</span><input class="v" value="<?= htmlspecialchars($dados['curso_desc']) ?>" readonly></li>
                    <li><span class="k">Ano curricular:</span><input class="v" value="<?= $dados['ano_curricular'] ?>" readonly></li>
                    <li>
                        <span class="k">Número de UC’s em atraso:</span>
                        <?php if (in_array($cargoLogado, ['Professor', 'Administrador']) && $dados['estado_confirmacao'] !== 'Confirmado'): ?>
                            <input type="number" name="ucs_atraso" class="v" value="<?= $dados['numero_ucs_atraso'] ?? '0' ?>" min="0" required style="border: 1px solid #1aa179; background: #fff;">
                        <?php else: ?>
                            <input class="v" value="<?= $dados['numero_ucs_atraso'] ?? '0' ?>" readonly>
                        <?php endif; ?>
                    </li>
                    <li><span class="k">Email aluno:</span><input class="v" value="<?= htmlspecialchars($dados['email_institucional']) ?>" readonly></li>
                    <li><span class="k">Estado de confirmação:</span><input class="v" value="<?= $dados['estado_confirmacao'] ?? 'Pendente' ?>" readonly></li>
                    <li><span class="k">Data confirmação:</span><input class="v" value="<?= $dados['data_confirmacao'] ? date('d/m/Y', strtotime($dados['data_confirmacao'])) : '---' ?>" readonly></li>
                </ul>
            </section>

            <div id="popup-salvar" class="popup-overlay" style="display:none;">
                <div class="popup-box">
                    <p class="popup-text">Deseja confirmar as informações do aluno e avançar para a próxima fase?</p>
                    <div class="popup-actions">
                        <button type="button" class="popup-btn popup-cancel" id="btn-cancelar">Cancelar</button>
                        <button type="submit" class="popup-btn popup-confirm">Sim</button>
                    </div>
                </div>
            </div>
        </form>
    </main>

    <div id="perfil-overlay" class="perfil-overlay">
        <div class="perfil-card">
            <div class="perfil-banner"></div>
            <div class="perfil-avatar">
                <img src="../img/img_conta.png" alt="Avatar" class="perfil-avatar-img">
            </div>
            <div class="perfil-content">
                <div class="perfil-role"><?= htmlspecialchars($cargoLogado) ?></div>
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
                <button type="button" class="perfil-voltar-btn" onclick="document.getElementById('perfil-overlay').classList.remove('show')">Voltar</button>
            </div>
        </div>
    </div>

    <footer id="footer">
        <div class="contactos">
            <h3>Contactos</h3>
            <p><img src="../img/img_email.png" alt="Email" style="width:26px;"> geral@ipsantarem.pt</p>
            <p><img src="../img/img_telemovel.png" alt="Telefone" style="width:26px;"> +351 243 309 520</p>
        </div>
        <div class="logos">
            <img src="../img/Logo.png" alt="GEU">
            <img src="../img/img_confinanciado.png" alt="Confinanciado">
        </div>
    </footer>

    <script src="../js/index.js"></script>
    <script>
        const btnAbrir = document.getElementById('btn-confirmar');
        const btnFechar = document.getElementById('btn-cancelar');
        const popup = document.getElementById('popup-salvar');

        if (btnAbrir) btnAbrir.onclick = () => popup.style.display = 'flex';
        if (btnFechar) btnFechar.onclick = () => popup.style.display = 'none';
    </script>
</body>
</html>