<?php
session_start();
include '../db.php'; 

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../login.php");
    exit();
}

$conexao = estabelecerConexao();
$cargoLogado = $_SESSION['cargo'];
$idUserLogado = $_SESSION['id_utilizador'];

$id_pedido = isset($_GET['id_pedido_estagio']) ? (int)$_GET['id_pedido_estagio'] : 0;
if ($id_pedido <= 0) die("Pedido de estágio inválido.");

$sql = "SELECT p.*, a.nome as aluno_nome, a.id_aluno, 
               e.id_empresa, e.nome as empresa_nome, e.email as empresa_email,
               fp.plano_estagio, fp.data_inicio, fp.data_fim
        FROM pedido_estagio p
        JOIN aluno a ON p.aluno_id = a.id_aluno
        LEFT JOIN empresa e ON p.empresa_id = e.id_empresa
        LEFT JOIN fase_plano fp ON p.id_pedido_estagio = fp.id_pedido_estagio
        WHERE p.id_pedido_estagio = :id";

$stmt = $conexao->prepare($sql);
$stmt->execute([':id' => $id_pedido]);
$dados = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$dados) die("Pedido não encontrado.");

// Perfil para o Modal
$nome_exibicao = "Utilizador";
$email_exibicao = "Email não disponível";
try {
    $tabela = strtolower($cargoLogado);
    $campoEmail = ($cargoLogado === 'Empresa') ? 'email' : 'email_institucional';
    $stmtP = $conexao->prepare("SELECT nome, $campoEmail FROM $tabela WHERE utilizador_id = ?");
    $stmtP->execute([$idUserLogado]);
    $res = $stmtP->fetch(PDO::FETCH_ASSOC);
    if ($res) {
        $nome_exibicao = $res['nome'];
        $email_exibicao = $res[$campoEmail];
    }
} catch (PDOException $e) { error_log($e->getMessage()); }
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>GEU — Pedido de estágio: Plano Estágio</title>
    <link rel="stylesheet" href="css/planoEstagio.css" />
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
                <a href="../alunos/index.php" class="nav-link">Alunos</a>
            <?php endif; ?>

            <a href="../index.php" class="nav-link active">Turmas</a>

            <button class="btn-conta" id="btn-conta">
                <img src="../img/img_conta.png" alt="Conta">
            </button>
            <a href="../logout.php" class="btn-sair">Sair</a>
        </nav>
    </header>

    <main id="main-content">
        <nav class="steps">
            <a class="step" href="confirmarDados.php?id_pedido_estagio=<?= $id_pedido ?>">Confirmar Dados</a>
            <a class="step" href="escolhaAreaEmpresa.php?id_pedido_estagio=<?= $id_pedido ?>">Escolha de área e empresa</a>
            <a class="step" href="envioEmail.php?id_pedido_estagio=<?= $id_pedido ?>">Envio de email</a>
            <a class="step" href="respostaEmail.php?id_pedido_estagio=<?= $id_pedido ?>">Resposta ao email</a>
            <a class="step active" href="#">Plano estágio</a>
            <a class="step" href="avaliacao.php?id_pedido_estagio=<?= $id_pedido ?>">Avaliação</a>
        </nav>

        <form action="processarPlanoEstagio.php" method="POST">
            <input type="hidden" name="id_pedido_estagio" value="<?= $id_pedido ?>">
            
            <div class="page-head">
                <h1 class="titulo"><?= htmlspecialchars($dados['aluno_nome']) ?> - <?= htmlspecialchars($dados['id_aluno']) ?></h1>
                <div class="acoes">
                    <a class="btn-outline" href="respostaEmail.php?id_pedido_estagio=<?= $id_pedido ?>">Voltar</a>
                    <?php if ($cargoLogado === 'Professor' || $cargoLogado === 'Administrador'): ?>
                        <button class="btn-primary" type="button" id="btn-confirmar">Guardar plano de estágio</button>
                    <?php endif; ?>
                </div>
            </div>

            <section class="card">
                <ul class="kv">
                    <li><span class="k">Número de pedido:</span><input class="v" value="<?= $id_pedido ?>" readonly></li>
                    <li><span class="k">Código empresa:</span><input class="v" value="<?= $dados['id_empresa'] ?? '---' ?>" readonly></li>
                    <li><span class="k">Nome empresa:</span><input class="v" value="<?= htmlspecialchars($dados['empresa_nome'] ?? '---') ?>" readonly></li>
                    <li><span class="k">Email:</span><input class="v" value="<?= htmlspecialchars($dados['empresa_email'] ?? '---') ?>" readonly></li>
                    
                    <li>
                        <span class="k">Plano Estágio:</span>
                        <div style="display: flex; gap: 10px; width: 100%;">
                            <?php if ($dados['plano_estagio']): ?>
                                <a href="../uploads/planos/<?= $dados['plano_estagio'] ?>" target="_blank" class="upload-btn" style="text-decoration:none; flex: 1; text-align: center; display: flex; align-items:center; justify-content:center;">
                                    Ver documento (<?= htmlspecialchars($dados['plano_estagio']) ?>)
                                </a>
                                <?php if ($cargoLogado === 'Professor' || $cargoLogado === 'Administrador'): ?>
                                    <button class="upload-btn" type="button" onclick="openModal()" style="flex: 1;">Substituir documento</button>
                                <?php endif; ?>
                            <?php else: ?>
                                <?php if ($cargoLogado === 'Professor' || $cargoLogado === 'Administrador'): ?>
                                    <button class="upload-btn" type="button" onclick="openModal()" style="width:100%;">Inserir documento</button>
                                <?php else: ?>
                                    <input class="v" value="Nenhum documento inserido" readonly>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </li>

                    <li><span class="k">Data de início:</span><input type="date" name="data_inicio" class="v" value="<?= $dados['data_inicio'] ?>" <?= ($cargoLogado !== 'Professor' && $cargoLogado !== 'Administrador') ? 'readonly' : 'required' ?>></li>
                    <li><span class="k">Data de fim:</span><input type="date" name="data_fim" class="v" value="<?= $dados['data_fim'] ?>" <?= ($cargoLogado !== 'Professor' && $cargoLogado !== 'Administrador') ? 'readonly' : 'required' ?>></li>
                </ul>
            </section>

            <div id="popup-salvar" class="popup-overlay" style="display:none;">
                <div class="popup-box">
                    <p class="popup-text">Deseja guardar as informações do plano?</p>
                    <div class="popup-actions">
                        <button type="button" class="popup-btn popup-cancel" onclick="this.closest('.popup-overlay').style.display='none'">Cancelar</button>
                        <button type="submit" class="popup-btn popup-confirm">Sim</button>
                    </div>
                </div>
            </div>
        </form>

        <div id="uploadModal" class="modal" style="display:none;">
            <div class="upload-card">
                <form action="processarPlanoEstagio.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id_pedido_estagio" value="<?= $id_pedido ?>">
                    <input type="hidden" name="acao" value="upload_plano">
                    <div class="upload-header">
                        <button class="upload-back" type="button" onclick="closeModal()">&#x2039;</button>
                        <h2>Upload</h2>
                    </div>
                    <label for="fileInput" class="upload-panel">
                        <div class="upload-cloud">☁</div>
                        <p class="upload-text" id="fileNameDisplay">Insira o documento que deseja enviar</p>
                        <button class="upload-plus" type="button" onclick="document.getElementById('fileInput').click()">+</button>
                    </label>
                    <input type="file" id="fileInput" name="plano_file" hidden accept=".pdf" onchange="displayFileName()">
                    <button type="submit" class="btn-primary" style="width:100%; margin-top:15px; border-radius: 8px;">Guardar Ficheiro</button>
                </form>
            </div>
        </div>
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
            <img src="../img/Logo.png" alt="GEU">
            <img src="../img/img_confinanciado.png" alt="Confinanciado">
        </div>
    </footer>

    <div id="perfil-overlay" class="perfil-overlay">
        <div class="perfil-card">
            <div class="perfil-banner"></div>
            <div class="perfil-avatar"><img src="../img/img_conta.png" alt="Avatar" class="perfil-avatar-img"></div>
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

    <script src="../js/index.js"></script>
    <script>
        function openModal() { document.getElementById('uploadModal').style.display = 'flex'; }
        function closeModal() { document.getElementById('uploadModal').style.display = 'none'; }
        function displayFileName() {
            const input = document.getElementById('fileInput');
            document.getElementById('fileNameDisplay').innerText = input.files[0] ? input.files[0].name : "Insira o documento que deseja enviar";
        }
        const btnConf = document.getElementById('btn-confirmar');
        if(btnConf) btnConf.onclick = () => document.getElementById('popup-salvar').style.display = 'flex';
        const btnConta = document.getElementById('btn-conta');
        if(btnConta) btnConta.onclick = () => document.getElementById('perfil-overlay').classList.add('show');
    </script>
</body>
</html>