<?php
// empresas/registarEmpresa.php

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
        $stmt = $db->prepare("SELECT nome, email_institucional as email FROM administrador WHERE utilizador_id = ?");
        $stmt->execute([$user_id_logado]);
        $dados = $stmt->fetch(PDO::FETCH_ASSOC);
    } 
    elseif ($cargo === 'Professor') {
        $stmt = $db->prepare("SELECT nome, email FROM professores WHERE utilizador_id = ?");
        $stmt->execute([$user_id_logado]);
        $dados = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    if ($dados) {
        $nome_exibicao = $dados['nome'];
        $email_exibicao = $dados['email'];
    }

} catch (PDOException $e) {
    error_log("Erro ao carregar dados do perfil: " . $e->getMessage());
}


$erros = $erros ?? [];
$ramos  = listarRamosAtividade();
$paises = listarPaises();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>GEU — Registar Empresa</title>
    <link rel="stylesheet" href="../professores/css/registarProfessor.css">
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
        <a href="index.php" class="subtab-link">Ver Empresas</a>
        <a href="registarEmpresa.php" class="subtab-link active">Registar nova empresa</a>
    </nav>

    <section class="content-grid">
        <form class="form-professor" method="post" action="addEmpresa.php">
            <?php if (!empty($erros)): ?>
                <div class="erros"><ul><?php foreach($erros as $e) echo "<li>$e</li>"; ?></ul></div>
            <?php endif; ?>

            <h3>Dados da Empresa</h3>
            
            <div class="form-group">
                <label>Nome da Empresa *</label>
                <input name="nome" type="text" value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>Ramo de Atividade</label>
                <div class="select-wrapper">
                    <select name="ramo_id">
                        <option value="">Selecione...</option>
                        <?php foreach($ramos as $r): ?>
                            <option value="<?= $r['id_ramo_atividade'] ?>"><?= $r['ramo_atividade_desc'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>NIF *</label>
                <input name="nif" type="text" value="<?= htmlspecialchars($_POST['nif'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>Email Geral (Login) *</label>
                <input name="email" type="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>Password *</label>
                <input name="password" type="password">
            </div>
            
            <div class="form-group">
                <label>Telefone</label>
                <input name="telefone" type="text" value="<?= htmlspecialchars($_POST['telefone'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>Website</label>
                <input name="website" type="text" value="<?= htmlspecialchars($_POST['website'] ?? '') ?>">
            </div>
            
            <div class="form-group">
                <label>Linkedin</label>
                <input name="linkedin" type="text" value="<?= htmlspecialchars($_POST['linkedin'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>País</label>
                <div class="select-wrapper">
                    <select name="pais_id">
                        <option value="">Selecione...</option>
                        <?php foreach($paises as $p): ?>
                            <option value="<?= $p['id_pais'] ?>"><?= $p['pais_desc'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Morada</label>
                <input name="morada" type="text" value="<?= htmlspecialchars($_POST['morada'] ?? '') ?>">
            </div>
             <div class="form-group">
                <label>Código Postal</label>
                <input name="cp" type="text" value="<?= htmlspecialchars($_POST['cp'] ?? '') ?>">
            </div>
             <div class="form-group">
                <label>Cidade</label>
                <input name="cidade" type="text" value="<?= htmlspecialchars($_POST['cidade'] ?? '') ?>">
            </div>

            <h3 style="margin-top:20px;">Dados do Responsável</h3>
            
            <div class="form-group">
                <label>Nome Responsável</label>
                <input name="nome_responsavel" type="text" value="<?= htmlspecialchars($_POST['nome_responsavel'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Cargo</label>
                <input name="cargo_responsavel" type="text" value="<?= htmlspecialchars($_POST['cargo_responsavel'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Email Responsável</label>
                <input name="email_responsavel" type="email" value="<?= htmlspecialchars($_POST['email_responsavel'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Telefone Responsável</label>
                <input name="telefone_responsavel" type="text" value="<?= htmlspecialchars($_POST['telefone_responsavel'] ?? '') ?>">
            </div>

            <div class="side-top">
                <button class="btn-salvar" type="submit">Salvar</button>
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

    
<script src="js/registarEmpresa.js"></script>
</body>
</html>