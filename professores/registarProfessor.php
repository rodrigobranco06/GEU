<?php
// professores/registarProfessor.php

session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['cargo'] !== 'Administrador') {
    header("Location: ../index.php"); 
    exit();
}

include 'modelsProfessores.php';

$user_id_logado = $_SESSION['id_utilizador'];
$cargo          = $_SESSION['cargo']; 
$nome_exibicao  = "Administrador";    
$email_exibicao = "Email não disponível";

try {
    $db = estabelecerConexao();
    
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


$erros = $erros ?? [];

$nacionalidades   = listarNacionalidades();
$escolas          = listarEscolas();
$especializacoes  = listarEspecializacoes();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>GEU — Registar novo professor</title>
    <link rel="stylesheet" href="css/registarProfessor.css" />
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
                <a href="../empresas/index.php" class="nav-link">Empresas</a>
            <?php endif; ?>

            <?php if ($cargo === 'Administrador'): ?>
                <a href="../professores/index.php" class="nav-link active">Professores</a>
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
        <a href="index.php" class="subtab-link">Ver Professores</a>
        <a href="registarProfessor.php" class="subtab-link active">Registar novo professor</a>
    </nav>

    <section class="content-grid">
        <form class="form-professor" method="post" action="addProfessor.php" id="formRegistoProf">

            <?php if (!empty($erros)): ?>
                <div class="erros">
                    <ul>
                        <?php foreach ($erros as $erro): ?>
                            <li><?= htmlspecialchars($erro) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="codigoProf">Código Professor</label>
                <input
                    id="codigoProf"
                    name="codigoProf"
                    type="text"
                    value="<?= htmlspecialchars($_POST['codigoProf'] ?? '') ?>"
                >
            </div>

            <div class="form-group">
                <label for="password">Password</label>

                <div class="password-wrapper">
                    <input
                        id="password"
                        name="password"
                        type="password"
                        value="<?= htmlspecialchars($_POST['password'] ?? '') ?>"
                    >

                    <label class="toggle-password">
                        <input type="checkbox" id="togglePassword">
                        Mostrar password
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label for="nomeProf">Nome Professor</label>
                <input
                    id="nomeProf"
                    name="nomeProf"
                    type="text"
                    value="<?= htmlspecialchars($_POST['nomeProf'] ?? '') ?>"
                >
            </div>

            <div class="form-group">
                <label for="dataNascimento">Data nascimento</label>
                <input
                    id="dataNascimento"
                    name="data_nascimento"
                    type="date"
                    value="<?= htmlspecialchars($_POST['data_nascimento'] ?? '') ?>"
                >
            </div>

            <div class="form-group">
                <label for="sexo">Sexo</label>
                <div class="select-wrapper">
                    <select id="sexo" name="sexo">
                        <option value="">Selecione um sexo</option>
                        <option value="Masculino" <?= (($_POST['sexo'] ?? '') === 'Masculino') ? 'selected' : '' ?>>Masculino</option>
                        <option value="Feminino" <?= (($_POST['sexo'] ?? '') === 'Feminino') ? 'selected' : '' ?>>Feminino</option>
                        <option value="Outro"     <?= (($_POST['sexo'] ?? '') === 'Outro')     ? 'selected' : '' ?>>Outro</option>
                    </select>
                    <span class="chevron">▾</span>
                </div>
            </div>

            <div class="form-group">
                <label for="nacionalidade">Nacionalidade</label>
                <div class="select-wrapper">
                    <select id="nacionalidade" name="nacionalidade_id">
                        <option value="">Selecione uma nacionalidade</option>
                        <?php foreach ($nacionalidades as $nac): ?>
                            <option
                                value="<?= $nac['id_nacionalidade'] ?>"
                                <?= (($_POST['nacionalidade_id'] ?? '') == $nac['id_nacionalidade']) ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars($nac['nacionalidade_desc']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <span class="chevron">▾</span>
                </div>
            </div>

            <div class="form-group">
                <label for="especializacao">Especialização</label>
                <div class="select-wrapper">
                    <select id="especializacao" name="especializacao_id">
                        <option value="">Selecione uma especialização</option>
                        <?php foreach ($especializacoes as $esp): ?>
                            <option
                                value="<?= $esp['id_especializacao'] ?>"
                                <?= (($_POST['especializacao_id'] ?? '') == $esp['id_especializacao']) ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars($esp['especializacao_desc']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <span class="chevron">▾</span>
                </div>
            </div>

            <div class="form-group">
                <label for="nif">NIF</label>
                <input
                    id="nif"
                    name="nif"
                    type="text"
                    value="<?= htmlspecialchars($_POST['nif'] ?? '') ?>"
                >
            </div>

            <div class="form-group">
                <label for="cc">Número CC</label>
                <input
                    id="cc"
                    name="cc"
                    type="text"
                    value="<?= htmlspecialchars($_POST['cc'] ?? '') ?>"
                >
            </div>

            <div class="form-group">
                <label for="escola">Escola</label>
                <div class="select-wrapper">
                    <select id="escola" name="escola_id">
                        <option value="">Selecione uma escola</option>
                        <?php foreach ($escolas as $esc): ?>
                            <option
                                value="<?= $esc['id_escola'] ?>"
                                <?= (($_POST['escola_id'] ?? '') == $esc['id_escola']) ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars($esc['escola_desc']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <span class="chevron">▾</span>
                </div>
            </div>

            <div class="form-group">
                <label for="emailInstitucional">Email institucional</label>
                <input
                    id="emailInstitucional"
                    name="emailInstitucional"
                    type="email"
                    value="<?= htmlspecialchars($_POST['emailInstitucional'] ?? '') ?>"
                >
            </div>

            <div class="form-group">
                <label for="emailPessoal">Email pessoal <span class="opcional">(opcional)</span></label>
                <input
                    id="emailPessoal"
                    name="emailPessoal"
                    type="email"
                    value="<?= htmlspecialchars($_POST['emailPessoal'] ?? '') ?>"
                >
            </div>

            <div class="form-group">
                <label for="morada">Morada</label>
                <input
                    id="morada"
                    name="morada"
                    type="text"
                    value="<?= htmlspecialchars($_POST['morada'] ?? '') ?>"
                >
            </div>

            <div class="form-group">
                <label for="cp">Código-Postal</label>
                <input
                    id="cp"
                    name="cp"
                    type="text"
                    value="<?= htmlspecialchars($_POST['cp'] ?? '') ?>"
                >
            </div>

            <div class="form-group">
                <label for="cidade">Cidade</label>
                <input
                    id="cidade"
                    name="cidade"
                    type="text"
                    value="<?= htmlspecialchars($_POST['cidade'] ?? '') ?>"
                >
            </div>

            <div class="side-top">
                <button class="btn-salvar" type="submit">
                    Salvar
                </button>
            </div>

        </form>

        <aside class="side-panel">
            <div class="side-top">
                <button class="btn-salvar" type="submit" form="formRegistoProf">
                    Salvar
                </button>
            </div>
        </aside>
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


<script src="js/registarProfessor.js"></script>

</body>
</html>
