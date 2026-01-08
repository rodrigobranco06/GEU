<?php
// empresas/editarEmpresa.php

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


$idEmpresa = isset($_GET['id_empresa']) ? (int)$_GET['id_empresa'] : 0;
$empresa = getEmpresaById($idEmpresa);
if (!$empresa) die('Empresa não encontrada.');

$ramos  = listarRamosAtividade();
$paises = listarPaises();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>GEU — Editar Empresa</title>
    <link rel="stylesheet" href="css/editarEmpresa.css">
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
            <a href="logout.php" class="btn-sair">Sair</a>
        </nav>
</header>

<main id="main-content">
    <nav class="subtabs">
        <a href="index.php" class="subtab-link active">Ver Empresas</a>
        <a href="registarEmpresa.php" class="subtab-link">Registar nova empresa</a>
    </nav>

    <section class="content-grid">
        <form class="form-professor" method="post" action="updateEmpresa.php?id_empresa=<?= $idEmpresa ?>">
            <input type="hidden" name="id_empresa" value="<?= $idEmpresa ?>">

            <h3>Dados Principais</h3>
            <div class="form-group">
                <label>Nº Empresa (Auto)</label>
                <input type="text" value="<?= $empresa['id_empresa'] ?>" readonly>
            </div>
            
            <div class="form-group">
                <label>Nova Password</label>
                <input name="nova_password" type="password" placeholder="Deixe vazio para manter">
            </div>

            <div class="form-group">
                <label>Nome Empresa</label>
                <input name="nome" type="text" value="<?= htmlspecialchars($empresa['nome']) ?>">
            </div>

            <div class="form-group">
                <label>Ramo</label>
                <div class="select-wrapper">
                    <select name="ramo_id">
                        <option value="">Selecione...</option>
                        <?php foreach($ramos as $r): ?>
                            <option value="<?= $r['id_ramo_atividade'] ?>" <?= ($empresa['ramo_atividade_id'] == $r['id_ramo_atividade']) ? 'selected' : '' ?>>
                                <?= $r['ramo_atividade_desc'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label>NIF</label>
                <input name="nif" type="text" value="<?= htmlspecialchars($empresa['nif']) ?>">
            </div>
            
            <div class="form-group">
                <label>Email</label>
                <input name="email" type="email" value="<?= htmlspecialchars($empresa['email']) ?>">
            </div>

            <div class="form-group">
                <label>Telefone</label>
                <input name="telefone" type="text" value="<?= htmlspecialchars($empresa['telefone'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>Website</label>
                <input name="website" type="text" value="<?= htmlspecialchars($empresa['website'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Linkedin</label>
                <input name="linkedin" type="text" value="<?= htmlspecialchars($empresa['linkedin'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>País</label>
                 <div class="select-wrapper">
                    <select name="pais_id">
                        <option value="">Selecione...</option>
                        <?php foreach($paises as $p): ?>
                            <option value="<?= $p['id_pais'] ?>" <?= ($empresa['pais_id'] == $p['id_pais']) ? 'selected' : '' ?>>
                                <?= $p['pais_desc'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Morada</label>
                <input name="morada" type="text" value="<?= htmlspecialchars($empresa['morada'] ?? '') ?>">
            </div>
             <div class="form-group">
                <label>Código Postal</label>
                <input name="cp" type="text" value="<?= htmlspecialchars($empresa['codigo_postal'] ?? '') ?>">
            </div>
             <div class="form-group">
                <label>Cidade</label>
                <input name="cidade" type="text" value="<?= htmlspecialchars($empresa['cidade'] ?? '') ?>">
            </div>

            <h3>Responsável</h3>
            <div class="form-group">
                <label>Nome Responsável</label>
                <input name="nome_responsavel" type="text" value="<?= htmlspecialchars($empresa['nome_responsavel'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Cargo</label>
                <input name="cargo_responsavel" type="text" value="<?= htmlspecialchars($empresa['cargo_responsavel'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Email Responsável</label>
                <input name="email_responsavel" type="email" value="<?= htmlspecialchars($empresa['email_responsavel'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Telefone Responsável</label>
                <input name="telefone_responsavel" type="text" value="<?= htmlspecialchars($empresa['telefone_responsavel'] ?? '') ?>">
            </div>

            <div class="side-top side-top-inside-form">
                <button class="btn-salvar" type="submit">Salvar</button>
                <button class="btn-eliminar" type="submit" formaction="deleteEmpresa.php?id_empresa=<?= $idEmpresa ?>" onclick="return confirm('Apagar empresa e utilizador?');">Eliminar</button>
                <a class="btn-voltar" href="verEmpresa.php?id_empresa=<?= $idEmpresa ?>">Voltar</a>
            </div>
        </form>
        
        
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

<script src="js/editarEmpresa.js"></script>
</body>
</html>