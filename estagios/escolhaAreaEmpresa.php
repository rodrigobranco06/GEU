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

// Procurar dados do Pedido, Aluno, Professor e o estado da fase_area
$sql = "SELECT p.*, a.nome as aluno_nome, a.id_aluno, 
               prof.id_professor, prof.nome as prof_nome,
               fa.cidade as area_cidade, fa.data_inicio_prevista, fa.data_fim_prevista, 
               fa.estado_definicao_area, fa.data_definicao_area, fa.area_cientifica_id,
               e.nome as empresa_nome, e.cidade as empresa_cidade_master
        FROM pedido_estagio p
        JOIN aluno a ON p.aluno_id = a.id_aluno
        LEFT JOIN professor prof ON p.professor_id = prof.id_professor
        LEFT JOIN fase_area fa ON p.id_pedido_estagio = fa.id_pedido_estagio
        LEFT JOIN empresa e ON p.empresa_id = e.id_empresa
        WHERE p.id_pedido_estagio = :id";

$stmt = $conexao->prepare($sql);
$stmt->execute([':id' => $id_pedido]);
$dados = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$dados) die("Pedido não encontrado.");

// Procurar Áreas e Empresas para os Selects
$areas = $conexao->query("SELECT * FROM area_cientifica ORDER BY area_cientifica_desc ASC")->fetchAll(PDO::FETCH_ASSOC);
$empresas = $conexao->query("SELECT id_empresa, nome, cidade FROM empresa ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);

// Lógica do Perfil Dinâmico
$nome_exibicao = "Utilizador";
$email_exibicao = "Email não disponível";
try {
    if ($cargoLogado === 'Aluno') { $stmtP = $conexao->prepare("SELECT nome, email_institucional FROM aluno WHERE utilizador_id = ?"); }
    elseif ($cargoLogado === 'Professor') { $stmtP = $conexao->prepare("SELECT nome, email_institucional FROM professor WHERE utilizador_id = ?"); }
    elseif ($cargoLogado === 'Administrador') { $stmtP = $conexao->prepare("SELECT nome, email_institucional FROM administrador WHERE utilizador_id = ?"); }
    elseif ($cargoLogado === 'Empresa') { $stmtP = $conexao->prepare("SELECT nome, email FROM empresa WHERE utilizador_id = ?"); }
    if (isset($stmtP)) {
        $stmtP->execute([$idUserLogado]);
        $resPerfil = $stmtP->fetch(PDO::FETCH_ASSOC);
        if ($resPerfil) {
            $nome_exibicao = $resPerfil['nome'];
            $email_exibicao = $resPerfil['email_institucional'] ?? $resPerfil['email'];
        }
    }
} catch (PDOException $e) { error_log($e->getMessage()); }
?>

<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>GEU — Pedido de estágio: escolha área e empresa</title>
  <link rel="stylesheet" href="css/escolhaAreaEmpresa.css" />
  <link rel="stylesheet" href="../css/index.css" />
</head>
<body>

  <header id="header">
        <div class="header-logo">
            <a href="../index.php">
                <img src="../img/Logo.png" alt="GEU">
            </a>
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
      <a class="step active" href="#">Escolha de área e empresa</a>
      <a class="step" href="envioEmail.php?id_pedido_estagio=<?= $id_pedido ?>">Envio de email</a>
      <a class="step" href="respostaEmail.php?id_pedido_estagio=<?= $id_pedido ?>">Resposta ao email</a>
      <a class="step" href="planoEstagio.php?id_pedido_estagio=<?= $id_pedido ?>">Plano estágio</a>
      <a class="step" href="avaliacao.php?id_pedido_estagio=<?= $id_pedido ?>">Avaliação</a>
    </nav>

    <form action="processarEscolhaAreaEmpresa.php" method="POST">
      <input type="hidden" name="id_pedido_estagio" value="<?= $id_pedido ?>">

      <div class="page-head">
        <h1 class="titulo"><?= htmlspecialchars($dados['aluno_nome']) ?> - <?= htmlspecialchars($dados['id_aluno']) ?></h1>
        <div class="acoes">
          <a class="btn-outline" href="confirmarDados.php?id_pedido_estagio=<?= $id_pedido ?>">Voltar</a>
          <?php if (in_array($cargoLogado, ['Professor', 'Administrador']) && $dados['estado_definicao_area'] !== 'Definido'): ?>
            <button class="btn-primary" type="button" id="btn-confirmar">Definir área e empresa</button>
          <?php endif; ?>
        </div>
      </div>

      <section class="card">
        <ul class="kv">
          <li><span class="k">Número de pedido de estágio:</span><input class="v" value="<?= $id_pedido ?>" readonly></li>

          <li>
            <span class="k">Área científica</span>
            <span class="select-wrap">
              <select name="area_cientifica_id" class="v select" <?= ($dados['estado_definicao_area'] === 'Definido') ? 'disabled' : '' ?> required>
                <option value="">Selecionar área...</option>
                <?php foreach ($areas as $a): ?>
                    <option value="<?= $a['id_area_cientifica'] ?>" <?= ($dados['area_cientifica_id'] == $a['id_area_cientifica']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($a['area_cientifica_desc']) ?>
                    </option>
                <?php endforeach; ?>
              </select>
              <span class="chev" aria-hidden="true">▾</span>
            </span>
          </li>

          <li><span class="k">Código professor orientador:</span><input class="v" value="<?= $dados['professor_id'] ?? 'Não atribuído' ?>" readonly></li>
          <li><span class="k">Nome professor orientador:</span><input class="v" value="<?= htmlspecialchars($dados['prof_nome'] ?? '---') ?>" readonly></li>
          
          <li>
            <span class="k">Empresa:</span>
            <span class="select-wrap">
              <select name="empresa_id" id="select-empresa" class="v select" <?= ($dados['estado_definicao_area'] === 'Definido') ? 'disabled' : '' ?> required>
                <option value="">Selecionar Empresa...</option>
                <?php foreach ($empresas as $emp): ?>
                  <option value="<?= $emp['id_empresa'] ?>" data-cidade="<?= htmlspecialchars($emp['cidade']) ?>" <?= ($dados['empresa_id'] == $emp['id_empresa']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($emp['nome']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <span class="chev" aria-hidden="true">▾</span>
            </span>
          </li>

          <li><span class="k">Código empresa:</span><input id="input-codigo-empresa" class="v" value="<?= $dados['empresa_id'] ?? '---' ?>" readonly></li>
          <li><span class="k">Cidade:</span><input name="cidade" id="input-cidade" class="v" value="<?= htmlspecialchars($dados['area_cidade'] ?? $dados['empresa_cidade_master'] ?? '---') ?>" readonly></li>
          <li><span class="k">Estado de definição:</span><input class="v" value="<?= $dados['estado_definicao_area'] ?? 'Pendente' ?>" readonly></li>
          <li><span class="k">Data de definição:</span><input class="v" value="<?= $dados['data_definicao_area'] ? date('d/m/Y', strtotime($dados['data_definicao_area'])) : '---' ?>" readonly></li>
        </ul>
      </section>

      <div id="popup-salvar" class="popup-overlay" style="display:none;">
        <div class="popup-box">
          <p class="popup-text">Deseja salvar as informações?</p>
          <div class="popup-actions">
            <button type="button" class="popup-btn popup-cancel" id="btn-cancelar">Cancelar</button>
            <button type="submit" class="popup-btn popup-confirm">Sim</button>
          </div>
        </div>
      </div>
    </form>
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
    <div class="logos"><img src="../img/Logo.png" alt="GEU"><img src="../img/img_confinanciado.png" alt="Confinanciado"></div>
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
    const btnAbrir = document.getElementById('btn-confirmar');
    const btnFechar = document.getElementById('btn-cancelar');
    const popup = document.getElementById('popup-salvar');
    const selectEmpresa = document.getElementById('select-empresa');
    const inputCidade = document.getElementById('input-cidade');
    const inputCodigo = document.getElementById('input-codigo-empresa');

    if(btnAbrir) btnAbrir.onclick = () => popup.style.display = 'flex';
    if(btnFechar) btnFechar.onclick = () => popup.style.display = 'none';

    // Lógica para preencher Código e Cidade ao selecionar empresa
    selectEmpresa.onchange = function() {
        const selectedOption = this.options[this.selectedIndex];
        inputCidade.value = selectedOption.getAttribute('data-cidade') || '---';
        inputCodigo.value = this.value || '---';
    };
  </script>
</body>
</html>