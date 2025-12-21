<?php
// tabelasSecundarias.php
session_start();
include '../db.php';
include 'modelsTabelasSecundarias.php';

// Segurança: Apenas Administradores devem aceder
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['cargo'] !== 'Administrador') {
    // Se não for admin, redireciona para a página principal ou login
    header("Location: ../index.php"); 
    exit();
}

// --- LÓGICA PARA O MODAL (Dados do Admin logado) ---
$user_id_logado = $_SESSION['id_utilizador'];
$cargo          = $_SESSION['cargo']; 
$nome_exibicao  = "Administrador";    
$email_exibicao = "Email não disponível";

try {
    // A função estabelecerConexao() já está disponível via modelsAlunos.php
    $db = estabelecerConexao();
    
    // Procurar os dados do Administrador que está a usar o sistema para o modal
    $stmtLogado = $db->prepare("SELECT nome, email_institucional FROM administrador WHERE utilizador_id = ?");
    $stmtLogado->execute([$user_id_logado]);
    $dadosLogado = $stmtLogado->fetch(PDO::FETCH_ASSOC);

    if ($dadosLogado) {
        $nome_exibicao = $dadosLogado['nome'];
        $email_exibicao = $dadosLogado['email_institucional'];
    }
} catch (PDOException $e) {
    error_log("Erro ao carregar dados do modal: " . $e->getMessage());
}


$conexao = estabelecerConexao();
$erros   = [];
$sucesso = '';

// Definição das tabelas e respetivas colunas para facilitar a manutenção
$config = [
    'escola'          => ['id' => 'id_escola',          'desc' => 'escola_desc'],
    'nacionalidade'   => ['id' => 'id_nacionalidade',   'desc' => 'nacionalidade_desc'],
    'especializacao'  => ['id' => 'id_especializacao',  'desc' => 'especializacao_desc'],
    'curso'           => ['id' => 'id_curso',           'desc' => 'curso_desc'],
    'area_cientifica' => ['id' => 'id_area_cientifica', 'desc' => 'area_cientifica_desc'],
    'ramo_atividade'  => ['id' => 'id_ramo_atividade',  'desc' => 'ramo_atividade_desc'],
    'pais'  => ['id' => 'id_pais',  'desc' => 'pais_desc']
];

// ========== PROCESSAMENTO DE FORMULÁRIOS ==========
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao   = $_POST['acao']   ?? '';
    $tabela = $_POST['tabela'] ?? '';

    if (array_key_exists($tabela, $config)) {
        $cfg = $config[$tabela];

        if ($acao === 'adicionar') {
            $descricao = trim($_POST['descricao'] ?? '');
            if ($descricao === '') {
                $erros[] = 'A descrição não pode estar vazia.';
            } else {
                if (adicionarRegisto($conexao, $tabela, $cfg['desc'], $descricao)) {
                    $sucesso = 'Registo adicionado com sucesso.';
                }
            }
        } elseif ($acao === 'apagar') {
            $id = (int)($_POST['id'] ?? 0);
            try {
                if (apagarRegisto($conexao, $tabela, $cfg['id'], $id)) {
                    $sucesso = 'Registo eliminado com sucesso.';
                }
            } catch (PDOException $e) {
                // Erro de Integridade Referencial (FK) tratado aqui
                $erros[] = 'Não foi possível apagar: este item está a ser utilizado por outros registos no sistema.';
            }
        }
    }
}

// ========== CARREGAR DADOS ==========
$dados = [];
foreach ($config as $tabela => $cfg) {
    $dados[$tabela] = listarTabela($conexao, $tabela, $cfg['desc']);
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GEU — Gestão de Tabelas Secundárias</title>
    <link rel="stylesheet" href="tabelasSecundarias.css">
</head>
<body>

<header id="header">
        <div class="header-logo">
            <a href="index.php"><img src="../img/Logo.png" alt="GEU"></a>
        </div>

        <nav class="nav-menu">
            <?php if ($cargo === 'Administrador'): ?>
                <a href="../administradores/index.php" class="nav-link active">Administradores</a>
            <?php endif; ?>

            <?php if ($cargo === 'Administrador' || $cargo === 'Professor'): ?>
                <a href="../empresas/index.php" class="nav-link">Empresas</a>
            <?php endif; ?>

            <?php if ($cargo === 'Administrador'): ?>
                <a href="../professores/index.php" class="nav-link">Professores</a>
                <a href="../alunos/index.php" class="nav-link">Alunos</a>
            <?php endif; ?>

            <a href="../index.php" class="nav-link">Turmas</a>

            <button id="btn-conta" class="btn-conta">
                <img src="../img/img_conta.png" alt="Conta">
            </button>
            <a href="logout.php" class="btn-sair">Sair</a>
        </nav>
</header>

<main id="main-content">

    <nav class="subtabs">
        <a href="../administradores/index.php" class="subtab-link">Ver Administradores</a>
        <a href="../administradores/registarAdministrador.php" class="subtab-link">Registar novo administrador</a>
        <a href="tabelasSecundarias.php" class="subtab-link active">Tabelas Secundárias</a>
    </nav>

    <div class="header-admin">
        <h1>Painel Administrativo</h1>
        <p>Gestão de listas do sistema, áreas científicas e ramos de atividade.</p>
        <a href="../administradores/index.php" class="btn-voltar">Voltar ao Início</a>
    </div>

    <div class="mensagens">
        <?php foreach ($erros as $e): ?> <div class="alert erro"><?= htmlspecialchars($e) ?></div> <?php endforeach; ?>
        <?php if ($sucesso): ?> <div class="alert sucesso"><?= htmlspecialchars($sucesso) ?></div> <?php endif; ?>
    </div>

    <div class="painel-grid">
        <?php foreach ($config as $nomeTabela => $cfg): ?>
            <section class="card">
                <h2><?= ucfirst(str_replace('_', ' ', $nomeTabela)) ?></h2>

                <form method="post" class="inline-form">
                    <input type="hidden" name="tabela" value="<?= $nomeTabela ?>">
                    <input type="hidden" name="acao" value="adicionar">
                    <input type="text" name="descricao" placeholder="Novo item..." required>
                    <button class="btn btn-add" type="submit">Adicionar</button>
                </form>

                <div class="scroll-table">
                    <table class="lista">
                        <thead>
                            <tr>
                                <th class="col-id">ID</th>
                                <th>Descrição</th>
                                <th class="col-acoes"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($dados[$nomeTabela] as $item): ?>
                                <tr>
                                    <td><?= $item[$cfg['id']] ?></td>
                                    <td><?= htmlspecialchars($item[$cfg['desc']]) ?></td>
                                    <td class="td-acoes">
                                        <form method="post" onsubmit="return confirm('Eliminar permanentemente?');">
                                            <input type="hidden" name="tabela" value="<?= $nomeTabela ?>">
                                            <input type="hidden" name="acao" value="apagar">
                                            <input type="hidden" name="id" value="<?= $item[$cfg['id']] ?>">
                                            <button type="submit" class="btn-del">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        <?php endforeach; ?>
    </div>
</main>


<footer id="footer">
        <div class="contactos">
            <h3>Contactos</h3>
            <p><img src="../img/img_email.png" alt="Email"><strong>Email:</strong> geral@ipsantarem.pt</p>
            <p><img src="../img/img_telemovel.png" alt="Telefone"><strong>Telefone:</strong> +351 243 309 520</p>
            <p><img src="../img/img_localizacao.png" alt="Endereço"><strong>Endereço:</strong> Complexo Andaluz, Apartado 279, 2001-904 Santarém</p>
        </div>

        <div class="logos">
            <img src="../img/Logo.png" alt="Gestão de Estágios Universitários">
            <img src="../img/img_confinanciado.png" alt="Confinanciado">
        </div>
</footer>


<!-- ======= MODAL PERFIL / CONTA ======= -->
    <div id="perfil-overlay" class="perfil-overlay">
        <div class="perfil-card">
            <div class="perfil-banner"></div>

            <div class="perfil-avatar">
                <img src="../img/img_conta.png" alt="Avatar" class="perfil-avatar-img">
            </div>

            <div class="perfil-content">
                <div class="perfil-role"><?= htmlspecialchars($cargo) ?></div>
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

                <button type="button" class="perfil-voltar-btn">
                    Voltar
                </button>
            </div>
        </div>
    </div>

    <script src="tabelasSecundarias.js"></script>

</body>
</html>