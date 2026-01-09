<?php
// empresas/index.php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !in_array($_SESSION['cargo'], ['Administrador', 'Professor'])) {
    header("Location: ../index.php"); 
    exit();
}

include 'modelsEmpresas.php';

$user_id_logado = $_SESSION['id_utilizador'];
$cargo          = $_SESSION['cargo']; 

$nome_exibicao  = "Utilizador";    
$email_exibicao = "Email não disponível";

try {
    $db = estabelecerConexao();
    $dados = null;

    if ($cargo === 'Administrador') {
        $stmt = $db->prepare("SELECT nome, email_institucional FROM administrador WHERE utilizador_id = ?");
        $stmt->execute([$user_id_logado]);
        $dados = $stmt->fetch(PDO::FETCH_ASSOC);
    } 
    elseif ($cargo === 'Professor') {
        $stmt = $db->prepare("SELECT nome, email_institucional FROM professor WHERE utilizador_id = ?");
        $stmt->execute([$user_id_logado]);
        $dados = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    if ($dados) {
        $nome_exibicao = $dados['nome'];
        $email_exibicao = $dados['email_institucional'];
    }

} catch (PDOException $e) {
    error_log("Erro ao carregar dados do perfil: " . $e->getMessage());
}


$empresas = getTodasEmpresas();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GEU — Ver Empresas</title>
    <link rel="stylesheet" href="../professores/css/index.css">
    </head>
<body>

<header id="header">
        <div class="header-logo">
            <a href="index.php"><img src="../img/Logo.png" alt="GEU"></a>
        </div>

        <nav class="nav-menu">
            <?php if ($cargo === 'Administrador'): ?>
                <a href="../administradores/index.php" class="nav-link">Administradores</a>
            <?php endif; ?>

            <?php if ($cargo === 'Administrador' || $cargo === 'Professor'): ?>
                <a href="../empresas/index.php" class="nav-link active">Empresas</a>
            <?php endif; ?>

            <?php if ($cargo === 'Administrador'): ?>
                <a href="../professores/index.php" class="nav-link">Professores</a>
                <a href="../alunos/index.php" class="nav-link">Alunos</a>
            <?php endif; ?>

            <a href="../index.php" class="nav-link">Turmas</a>

            <button id="btn-conta" class="btn-conta">
                <img src="../img/img_conta.png" alt="Conta">
            </button>
            <a href="../logout.php" class="btn-sair">Sair</a>
        </nav>
</header>

<main id="main-content">
    <nav class="subtabs">
        <a href="index.php" class="subtab-link active">Ver Empresas</a>
        <a href="registarEmpresa.php" class="subtab-link">Registar nova empresa</a>
    </nav>

    <section class="search-area">
        <div class="search-wrapper">
            <span class="search-icon">🔍</span>
            <input type="text" placeholder="Procurar por empresa">
        </div>
    </section>

    <section class="table-wrapper">
        <table class="professores-table"> <thead>
                <tr>
                    <th>Nº Empresa</th>
                    <th>Nome</th>
                    <th>Ramo de Atividade</th>
                    <th>Email Geral</th>
                    <th>Estágios</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($empresas as $emp): ?>
                <tr onclick="window.location.href='verEmpresa.php?id_empresa=<?= $emp['id_empresa'] ?>'" class="linha-click">
                    <td><?= htmlspecialchars($emp['id_empresa']) ?></td>
                    <td><?= htmlspecialchars($emp['nome']) ?></td>
                    <td><?= htmlspecialchars($emp['ramo_atividade_desc'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($emp['email']) ?></td>
                    <td><?= htmlspecialchars($emp['numero_estagios']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>
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

    <script src="js/index.js"></script>
</body>
</html>