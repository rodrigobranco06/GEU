<?php
session_start();
include 'db.php';

// 1. Redirecionamento de segurança: Verificar login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$conexao = estabelecerConexao();
$cargoLogado = $_SESSION['cargo'];
$idUserLogado = $_SESSION['id_utilizador'];

// 2. Obter ID do aluno
$idAluno = isset($_GET['id_aluno']) ? (int)$_GET['id_aluno'] : 0;
if ($idAluno <= 0) {
    die('ID de aluno inválido.');
}

// Segurança: Se for Aluno, só pode ver o seu próprio ID
if ($cargoLogado === 'Aluno') {
    $stmtCheck = $conexao->prepare("SELECT id_aluno FROM aluno WHERE id_aluno = ? AND utilizador_id = ?");
    $stmtCheck->execute([$idAluno, $idUserLogado]);
    if (!$stmtCheck->fetch()) {
        die("Acesso negado.");
    }
}

// 3. Lógica para CRIAR NOVO PEDIDO (Executada por Admin/Professor)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'criar_pedido') {
    if ($cargoLogado === 'Administrador' || $cargoLogado === 'Professor') {
        try {
            $conexao->beginTransaction();

            // Inserir o pedido base
            $sqlInsert = "INSERT INTO pedido_estagio (estado_pedido, fase_atual, aluno_id, data_criacao) 
                          VALUES ('Aguardar confirmação', 'Confirmar Dados', :id, NOW())";
            $stmtInsert = $conexao->prepare($sqlInsert);
            $stmtInsert->execute([':id' => $idAluno]);
            
            $idNovoPedido = $conexao->lastInsertId();

            // Criar a entrada inicial na tabela fase_confirmacao
            $sqlFase = "INSERT INTO fase_confirmacao (id_pedido_estagio, numero_ucs_atraso, estado_confirmacao) 
                        VALUES (?, '0', 'Pendente')";
            $conexao->prepare($sqlFase)->execute([$idNovoPedido]);

            $conexao->commit();

            // REFRESH: Redireciona para o próprio aluno.php para mostrar o novo card na lista
            header("Location: aluno.php?id_aluno=" . $idAluno . "&sucesso=1");
            exit;
        } catch (PDOException $e) {
            $conexao->rollBack();
            $erro = "Erro ao criar pedido: " . $e->getMessage();
        }
    }
}

// 4. Buscar dados do Aluno para o Perfil e Cabeçalho
$sqlAluno = "SELECT nome, email_institucional, 
             SUBSTRING_INDEX(email_institucional, '@', 1) as numero_aluno 
             FROM aluno WHERE id_aluno = :id";
$stmtA = $conexao->prepare($sqlAluno);
$stmtA->execute([':id' => $idAluno]);
$dadosAluno = $stmtA->fetch(PDO::FETCH_ASSOC);

if (!$dadosAluno) {
    die("Aluno não encontrado.");
}

// 5. Buscar Pedidos (Com filtro para Empresas verem apenas os estágios vinculados a elas)
$sqlPedidos = "SELECT p.* FROM pedido_estagio p";
$paramsP = [':id' => $idAluno];

if ($cargoLogado === 'Empresa') {
    $sqlPedidos .= " INNER JOIN empresa e ON p.empresa_id = e.id_empresa 
                     WHERE p.aluno_id = :id AND e.utilizador_id = :id_u";
    $paramsP[':id_u'] = $idUserLogado;
} else {
    $sqlPedidos .= " WHERE p.aluno_id = :id";
}

$sqlPedidos .= " ORDER BY p.data_criacao DESC";
$stmtP = $conexao->prepare($sqlPedidos);
$stmtP->execute($paramsP);
$pedidos = $stmtP->fetchAll(PDO::FETCH_ASSOC);

// Helper para classes CSS
function getEstadoClass($estado) {
    $estado = mb_strtolower($estado, 'UTF-8');
    if (strpos($estado, 'aguardar') !== false) return 'aguardando';
    if (strpos($estado, 'concluído') !== false || strpos($estado, 'aprovado') !== false) return 'sucesso';
    if (strpos($estado, 'rejeitado') !== false) return 'erro';
    return 'aguardando';
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>GEU — Estágios de <?= htmlspecialchars($dadosAluno['nome']) ?></title>
  <link rel="stylesheet" href="css/aluno.css" />
  <link rel="stylesheet" href="css/index.css" />
  <style>
      .pedido-estado.aguardando { color: #d97706; background: #fef3c7; padding: 5px 10px; border-radius: 15px; display: inline-block; font-size: 0.9em; font-weight: 600; }
      .pedido-estado.sucesso { color: #059669; background: #d1fae5; padding: 5px 10px; border-radius: 15px; display: inline-block; font-size: 0.9em; font-weight: 600; }
      .pedido-estado.erro { color: #dc2626; background: #fee2e2; padding: 5px 10px; border-radius: 15px; display: inline-block; font-size: 0.9em; font-weight: 600; }
  </style>
</head>
<body>

  <header id="header">
    <div class="header-logo">
      <a href="index.php"><img src="img/Logo.png" alt="GEU"></a>
    </div>

    <nav class="nav-menu">
        <?php if ($cargoLogado === 'Administrador'): ?> <a href="administradores/index.php" class="nav-link">Administradores</a> <?php endif; ?>
        <?php if ($cargoLogado === 'Administrador' || $cargoLogado === 'Professor'): ?> <a href="empresas/index.php" class="nav-link">Empresas</a> <?php endif; ?>
        <?php if ($cargoLogado === 'Administrador'): ?> <a href="professores/index.php" class="nav-link">Professores</a> <a href="alunos/index.php" class="nav-link">Alunos</a> <?php endif; ?>
        <a href="index.php" class="nav-link active">Turmas</a>
        <button id="btn-conta" class="btn-conta"><img src="img/img_conta.png" alt="Conta"></button>
        <a href="logout.php" class="btn-sair">Sair</a>
    </nav>
  </header>

  <main id="main-content">
    <div class="main-header">
      <h2 class="titulo-pagina">
          <?= htmlspecialchars($dadosAluno['nome']) ?> - <?= htmlspecialchars($dadosAluno['numero_aluno']) ?>
      </h2>

      <div class="acoes">
        <a href="index.php" class="btn-outline">Voltar</a>
        
        <?php if ($cargoLogado === 'Administrador' || $cargoLogado === 'Professor'): ?>
            <form method="POST" style="display:inline;">
                <input type="hidden" name="acao" value="criar_pedido">
                <button type="submit" class="btn-criar">Novo Pedido de estágio</button>
            </form>
        <?php endif; ?>
      </div>
    </div>

    <section class="pedidos-container">
      <?php if (count($pedidos) > 0): ?>
          <?php $total = count($pedidos); foreach ($pedidos as $index => $pedido): ?>
              <a href="estagios/confirmarDados.php?id_pedido_estagio=<?= $pedido['id_pedido_estagio'] ?>" style="text-decoration: none; color: inherit;">
                <article class="pedido-card">
                  <h3 class="pedido-titulo">Pedido de estágio - <?= $total - $index ?></h3>
                  <div style="margin-top: 10px;">
                      <p class="pedido-label">Fase Atual: <strong><?= htmlspecialchars($pedido['fase_atual'] ?? 'Inicial') ?></strong></p>
                  </div>
                  <p class="pedido-label" style="margin-top: 10px;">Estado do estágio</p>
                  <p class="pedido-estado <?= getEstadoClass($pedido['estado_pedido']) ?>">
                      <?= htmlspecialchars($pedido['estado_pedido']) ?>
                  </p>
                  <p style="font-size: 0.8em; color: #666; margin-top: 10px;">
                      Criado em: <?= date('d/m/Y', strtotime($pedido['data_criacao'])) ?>
                  </p>
                </article>
              </a>
          <?php endforeach; ?>
      <?php else: ?>
          <div style="text-align: center; padding: 40px; color: #666; width: 100%;">
              <p>Ainda não existem pedidos de estágio registados.</p>
          </div>
      <?php endif; ?>
    </section>
  </main>

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

  <div id="perfil-overlay" class="perfil-overlay">
    <div class="perfil-card">
        <div class="perfil-banner"></div>
        <div class="perfil-avatar">
            <img src="img/img_conta.png" alt="Avatar" class="perfil-avatar-img">
        </div>
        <div class="perfil-content">
            <div class="perfil-role"><?= htmlspecialchars($cargoLogado) ?></div>
            <div class="perfil-name"><?= htmlspecialchars($dadosAluno['nome']) ?></div>
            <div class="perfil-row">
                <img src="img/img_email.png" alt="Email" class="perfil-row-img">
                <span class="perfil-row-text"><?= htmlspecialchars($dadosAluno['email_institucional']) ?></span>
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

  <script src="js/index.js"></script>
</body>
</html>