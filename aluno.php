<?php
include '../db.php';
include '../utils.php';

$conexao = estabelecerConexao();

// 1. Obter ID do aluno (via GET para admin/professor ver, ou via Sessão se fosse o próprio aluno)
$idAluno = isset($_GET['id_aluno']) ? (int)$_GET['id_aluno'] : 0;

if ($idAluno <= 0) {
    die('ID de aluno inválido.');
}

// 2. Lógica para CRIAR NOVO PEDIDO (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'criar_pedido') {
    try {
        // Cria um novo pedido com estado inicial
        $sqlInsert = "INSERT INTO pedido_estagio (estado_pedido, fase_atual, aluno_id, data_criacao) 
                      VALUES ('Aguardar confirmação', 'Confirmação de Dados', :id, NOW())";
        $stmtInsert = $conexao->prepare($sqlInsert);
        $stmtInsert->execute([':id' => $idAluno]);
        
        // Refresh para mostrar o novo pedido
        header("Location: verEstagios.php?id_aluno=" . $idAluno);
        exit;
    } catch (PDOException $e) {
        $erro = "Erro ao criar pedido: " . $e->getMessage();
    }
}

// 3. Buscar dados do Aluno
$sqlAluno = "SELECT nome, email_institucional, 
             SUBSTRING_INDEX(email_institucional, '@', 1) as numero_aluno 
             FROM aluno WHERE id_aluno = :id";
$stmtA = $conexao->prepare($sqlAluno);
$stmtA->execute([':id' => $idAluno]);
$dadosAluno = $stmtA->fetch(PDO::FETCH_ASSOC);

if (!$dadosAluno) {
    die("Aluno não encontrado.");
}

// 4. Buscar Pedidos de Estágio do Aluno
$sqlPedidos = "SELECT * FROM pedido_estagio 
               WHERE aluno_id = :id 
               ORDER BY data_criacao DESC"; // Mais recentes primeiro
$stmtP = $conexao->prepare($sqlPedidos);
$stmtP->execute([':id' => $idAluno]);
$pedidos = $stmtP->fetchAll(PDO::FETCH_ASSOC);

// Helper para classes CSS baseadas no estado
function getEstadoClass($estado) {
    $estado = mb_strtolower($estado, 'UTF-8');
    if (strpos($estado, 'aguardar') !== false) return 'aguardando'; // Laranja/Amarelo
    if (strpos($estado, 'concluído') !== false || strpos($estado, 'aprovado') !== false) return 'sucesso'; // Verde
    if (strpos($estado, 'rejeitado') !== false || strpos($estado, 'cancelado') !== false) return 'erro'; // Vermelho
    return 'aguardando'; // Default
}
?>

<!DOCTYPE html>
<html lang="pt">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>GEU — Estágios do aluno</title>
  <link rel="stylesheet" href="css/aluno.css" />
  <style>
      /* Pequeno ajuste para classes de estado se não existirem no CSS */
      .pedido-estado.aguardando { color: #d97706; background: #fef3c7; padding: 5px 10px; border-radius: 15px; display: inline-block; font-size: 0.9em; }
      .pedido-estado.sucesso { color: #059669; background: #d1fae5; padding: 5px 10px; border-radius: 15px; display: inline-block; font-size: 0.9em; }
      .pedido-estado.erro { color: #dc2626; background: #fee2e2; padding: 5px 10px; border-radius: 15px; display: inline-block; font-size: 0.9em; }
  </style>
</head>

<body>

  <header id="header">
    <div class="header-logo">
      <a href="../index.php">
        <img src="../img/Logo.png" alt="Gestão de Estágios Universitários">
      </a>
    </div>

    <nav class="nav-menu">
      <a href="index.php" class="nav-link active">Alunos</a>
      <a href="../professores/index.php" class="nav-link">Professores</a>
      <a href="../empresas/index.php" class="nav-link">Empresas</a>
      <a href="../index.php" class="nav-link">Turmas</a>

      <button id="btn-conta" class="btn-conta">
        <img src="../img/img_conta.png" alt="Conta">
      </button>

      <a href="../login.php" class="btn-sair">Sair</a>
    </nav>
  </header>

  <main id="main-content">
    <div class="main-header">
      <h2 class="titulo-pagina">
          <?= htmlspecialchars($dadosAluno['nome']) ?> - <?= htmlspecialchars($dadosAluno['numero_aluno']) ?>
      </h2>

      <div class="acoes">
        <a href="verAluno.php?id_aluno=<?= $idAluno ?>" class="btn-outline">Voltar</a>
        
        <form method="POST" action="verEstagios.php?id_aluno=<?= $idAluno ?>" style="display:inline;">
            <input type="hidden" name="acao" value="criar_pedido">
            <button type="submit" class="btn-criar">Novo Pedido de estágio</button>
        </form>
      </div>
    </div>

    <section class="pedidos-container">
      
      <?php if (count($pedidos) > 0): ?>
          <?php 
            // Contador inverso para numeração visual (ex: Pedido 2, Pedido 1)
            $total = count($pedidos); 
          ?>
          <?php foreach ($pedidos as $index => $pedido): ?>
              <a href="../estagios/verEstagio.php?id_pedido=<?= $pedido['id_pedido_estagio'] ?>" style="text-decoration: none; color: inherit;">
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
              <p>Este aluno ainda não tem pedidos de estágio registados.</p>
              <p>Clique em "Novo Pedido de estágio" para começar.</p>
          </div>
      <?php endif; ?>

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

  <div id="perfil-overlay" class="perfil-overlay">
    <div class="perfil-card">
        <div class="perfil-banner"></div>

        <div class="perfil-avatar">
            <img src="../img/img_conta.png" alt="Avatar" class="perfil-avatar-img">
        </div>

        <div class="perfil-content">
            <div class="perfil-role">Aluno</div>
            <div class="perfil-name"><?= htmlspecialchars($dadosAluno['nome']) ?></div>

            <div class="perfil-row">
                <img src="../img/img_email.png" alt="Email" class="perfil-row-img">
                <span class="perfil-row-text"><?= htmlspecialchars($dadosAluno['email_institucional']) ?></span>
            </div>

            <a href="verPerfil.php" class="perfil-row">
                <img src="../img/img_definicoes.png" alt="Definições" class="perfil-row-img">
                <span class="perfil-row-text">Definições de conta</span>
            </a>

            <a href="../login.php" class="perfil-logout-row">
                <img src="../img/img_sair.png" alt="Sair" class="perfil-back-img">
                <span class="perfil-logout-text">Log out</span>
            </a>

            <button type="button" class="perfil-voltar-btn">
                Voltar
            </button>
        </div>
    </div>
</div>

<script src="js/aluno.js"></script>
</body>

</html>