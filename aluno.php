<?php
session_start();
include 'db.php';
include 'modelsAluno.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$conexao = estabelecerConexao();
$cargoLogado = $_SESSION['cargo'];
$idUserLogado = $_SESSION['id_utilizador'];

$idAluno = isset($_GET['id_aluno']) ? (int)$_GET['id_aluno'] : 0;
if ($idAluno <= 0) die('ID de aluno inválido.');

// Validação de segurança via Model
if ($cargoLogado === 'Aluno') {
    if (!verificarAcessoAluno($conexao, $idAluno, $idUserLogado)) {
        die("Acesso negado.");
    }
}

// Carregamento de dados via Model
$dadosAluno = getDadosAluno($conexao, $idAluno);
if (!$dadosAluno) die("Aluno não encontrado.");

$pedidos = getPedidosEstagio($conexao, $idAluno, $cargoLogado, $idUserLogado);
$perfilLogado = getPerfilLogado($conexao, $idUserLogado, $cargoLogado);

// Links e UI
$linkVoltar = ($dadosAluno['turma_id']) ? "turma.php?id_turma=" . $dadosAluno['turma_id'] : "index.php";

function getEstadoClass($estado) {
    $estado = mb_strtolower($estado, 'UTF-8');
    if (strpos($estado, 'aguardar') !== false || strpos($estado, 'pendente') !== false) return 'aguardando';
    if (strpos($estado, 'concluído') !== false || strpos($estado, 'aprovado') !== false || strpos($estado, 'aceite') !== false) return 'aceite';
    return 'sem';
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
</head>
<body>

  <header id="header">
    <div class="header-logo"><a href="index.php"><img src="img/Logo.png" alt="GEU"></a></div>
    <nav class="nav-menu">
        <?php if ($cargoLogado === 'Administrador'): ?> 
            <a href="administradores/index.php" class="nav-link">Administradores</a> 
        <?php endif; ?>
        <?php if ($cargoLogado === 'Administrador' || $cargoLogado === 'Professor'): ?> 
            <a href="empresas/index.php" class="nav-link">Empresas</a> 
        <?php endif; ?>
        <?php if ($cargoLogado === 'Administrador'): ?> 
            <a href="professores/index.php" class="nav-link">Professores</a> 
            <a href="alunos/index.php" class="nav-link">Alunos</a> 
        <?php endif; ?>
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
        <a href="<?= $linkVoltar ?>" class="btn-outline">Voltar</a>
        <?php if ($cargoLogado === 'Administrador' || $cargoLogado === 'Professor'): ?>
            <form action="processarPedidoEstagio.php" method="POST" style="display:inline;">
                <input type="hidden" name="id_aluno" value="<?= $idAluno ?>">
                <input type="hidden" name="acao" value="criar_pedido">
                <button type="submit" class="btn-criar">Novo Pedido de estágio</button>
            </form>
        <?php endif; ?>
      </div>
    </div>

    <section class="pedidos-container">
        <?php if (count($pedidos) > 0): ?>
            <?php $total = count($pedidos); foreach ($pedidos as $index => $pedido): ?>
                <div class="pedido-wrapper">
                    <article class="pedido-card">
                        <a href="estagios/confirmarDados.php?id_pedido_estagio=<?= $pedido['id_pedido_estagio'] ?>" class="card-content">
                            <h3 class="pedido-titulo">Pedido de estágio - <?= $total - $index ?></h3>
                            <p class="pedido-label">Fase Atual: <strong><?= htmlspecialchars($pedido['fase_atual'] ?: 'Inicial') ?></strong></p>
                            <p class="pedido-label">Estado do estágio</p>
                            <p class="pedido-estado <?= getEstadoClass($pedido['estado_pedido']) ?>">
                                <?= htmlspecialchars($pedido['estado_pedido'] ?: 'Pendente') ?>
                            </p>
                            <p class="pedido-data">Criado em: <?= date('d/m/Y', strtotime($pedido['data_criacao'])) ?></p>
                        </a>

                        <?php if ($cargoLogado === 'Administrador' || $cargoLogado === 'Professor'): ?>
                            <form action="processarPedidoEstagio.php" method="POST" onsubmit="return confirm('Deseja realmente eliminar este pedido?');" class="form-eliminar">
                                <input type="hidden" name="id_aluno" value="<?= $idAluno ?>">
                                <input type="hidden" name="id_pedido" value="<?= $pedido['id_pedido_estagio'] ?>">
                                <input type="hidden" name="acao" value="apagar_pedido">
                                <button type="submit" class="btn-delete-card">Eliminar Pedido</button>
                            </form>
                        <?php endif; ?>
                    </article>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="sem-pedidos"><p>Ainda não existem pedidos de estágio registados para este aluno.</p></div>
        <?php endif; ?>
    </section>
  </main>

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

  <div id="perfil-overlay" class="perfil-overlay">
    <div class="perfil-card">
        <div class="perfil-banner"></div>
        <div class="perfil-avatar"><img src="img/img_conta.png" alt="Avatar" class="perfil-avatar-img"></div>
        <div class="perfil-content">
            <div class="perfil-role"><?= htmlspecialchars($cargoLogado) ?></div>
            <div class="perfil-name"><?= htmlspecialchars($perfilLogado['nome']) ?></div>
            <div class="perfil-row">
                <img src="img/img_email.png" alt="Email" class="perfil-row-img">
                <span class="perfil-row-text"><?= htmlspecialchars($perfilLogado['email']) ?></span>
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

  <script>
    const btnConta = document.getElementById('btn-conta');
    const overlay = document.getElementById('perfil-overlay');
    btnConta.onclick = () => overlay.classList.add('show');
    window.onclick = (e) => { if (e.target === overlay) overlay.classList.remove('show'); };
  </script>
</body>
</html>