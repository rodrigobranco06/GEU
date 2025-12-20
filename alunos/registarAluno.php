<?php
// alunos/registarAluno.php

session_start();

// 1. Verificação de segurança: Logado + Cargo de Administrador
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['cargo'] !== 'Administrador') {
    // Se não for admin, redireciona para a página principal ou login
    header("Location: ../index.php"); 
    exit();
}

include 'modelsAlunos.php';

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

// $erros pode vir do addAluno.php (include em caso de erro)
$erros = $erros ?? [];

$nacionalidades = listarNacionalidades();
$cursos         = listarCursos();
$turmas         = listarTurmas();
$escolas        = listarEscolas();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>GEU — Registar novo aluno</title>
    <link rel="stylesheet" href="css/registarAluno.css" />
</head>
<body>

    <!-- ======= CABEÇALHO ======= -->
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
            <a href="../index.php" class="nav-link">Administradores</a>

            <button class="btn-conta" id="btn-conta">
                <img src="../img/img_conta.png" alt="Conta">
            </button>
            <a href="../login.php" class="btn-sair">Sair</a>
        </nav>
    </header>

    <!-- ======= CONTEÚDO PRINCIPAL ======= -->
    <main id="main-content">

        <!-- Subtabs -->
        <nav class="subtabs">
            <a href="index.php" class="subtab-link">Ver Alunos</a>
            <a href="registarAluno.php" class="subtab-link active">Registar novo aluno</a>
        </nav>

        <section class="content-grid">
            <!-- Coluna esquerda: formulário -->
            <form
                class="form-aluno"
                method="post"
                action="addAluno.php"
                id="formRegistoAluno"
                enctype="multipart/form-data"
            >

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
                    <label for="codigoAluno">Código Aluno</label>
                    <input
                        id="codigoAluno"
                        name="codigoAluno"
                        type="text"
                        value="<?= htmlspecialchars($_POST['codigoAluno'] ?? '') ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-inline"> 
                        <input
                            id="password"
                            name="password"
                            type="password"
                            value="<?= htmlspecialchars($_POST['password'] ?? '') ?>"
                        >
                        <label class="toggle-password-inline">
                            <input type="checkbox" id="togglePassword">
                            <span>Mostrar password</span>
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label for="nomeAluno">Nome Aluno</label>
                    <input
                        id="nomeAluno"
                        name="nomeAluno"
                        type="text"
                        value="<?= htmlspecialchars($_POST['nomeAluno'] ?? '') ?>"
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
                    <label for="curso">Curso</label>
                    <div class="select-wrapper">
                        <select id="curso" name="curso_id">
                            <option value="">Selecione um curso</option>
                            <?php foreach ($cursos as $curso): ?>
                                <option
                                    value="<?= $curso['id_curso'] ?>"
                                    <?= (($_POST['curso_id'] ?? '') == $curso['id_curso']) ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars($curso['curso_desc']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <span class="chevron">▾</span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="turmaId">Turma</label>
                    <div class="select-wrapper">
                        <select id="turmaId" name="turma_id" disabled>
                            <option value="">
                                <?= isset($_POST['curso_id']) && $_POST['curso_id'] !== '' ? 'Selecione uma Turma' : 'Selecione um curso primeiro' ?>
                            </option>
                            <?php foreach ($turmas as $turma): ?>
                                <option
                                    value="<?= $turma['id_turma'] ?>"
                                    data-curso-id="<?= $turma['curso_id'] ?>"
                                    <?= (($_POST['turma_id'] ?? '') == $turma['id_turma']) ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars($turma['codigo'] ?? $turma['nome'] ?? $turma['id_turma']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <span class="chevron">▾</span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="anoCurricular">Ano curricular</label>
                    <input
                        id="anoCurricular"
                        type="number"
                        min="1"
                        value="<?= htmlspecialchars($_POST['anoCurricular'] ?? '') ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="situacaoAcademica">Situação académica</label>
                    <div class="select-wrapper">
                        <select id="situacaoAcademica" name="situacao_academica">
                            <option value="">Selecione uma situação académica</option>
                            <option value="Ativo"    <?= (($_POST['situacao_academica'] ?? '') === 'Ativo')    ? 'selected' : '' ?>>Ativo</option>
                            <option value="Suspenso" <?= (($_POST['situacao_academica'] ?? '') === 'Suspenso') ? 'selected' : '' ?>>Suspenso</option>
                        </select>
                        <span class="chevron">▾</span>
                    </div>
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
                    <label for="emailPessoal">Email pessoal</label>
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

                <div class="form-group">
                    <label for="idEstagio">ID estágio</label>
                    <input id="idEstagio" type="text">
                </div>

                <div class="form-group">
                    <label for="profOrientador">Professor orientador</label>
                    <input id="profOrientador" type="text">
                </div>

                <div class="form-group">
                    <label for="idEmpresa">ID empresa</label>
                    <input id="idEmpresa" type="text">
                </div>

                <div class="form-group">
                    <label for="nomeEmpresa">Nome empresa</label>
                    <input id="nomeEmpresa" type="text">
                </div>

                <div class="form-group">
                    <label for="estadoEstagio">Estado estágio</label>
                    <input id="estadoEstagio" type="text">
                </div>

                <div class="form-group">
                    <label for="cv">CV</label>
                    <input
                        id="cv"
                        name="cv"
                        type="file"
                        accept=".pdf,.doc,.docx"
                    >
                </div>

                <div class="form-group">
                    <label for="linkedin">LinkedIn</label>
                    <input
                        id="linkedin"
                        name="linkedin"
                        type="url"
                        value="<?= htmlspecialchars($_POST['linkedin'] ?? '') ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="portfolio">Portefólio (GitHub)</label>
                    <input
                        id="portfolio"
                        name="github"
                        type="url"
                        value="<?= htmlspecialchars($_POST['github'] ?? '') ?>"
                    >
                </div>

                <div class="side-top side-top-inside-form">
                    <button class="btn-salvar" type="submit">
                        Salvar
                    </button>
                </div>

            </form>

            <!-- Coluna direita: botão + imagem -->
            <aside class="side-panel">
                <div class="side-top">
                    <button class="btn-salvar" type="submit" form="formRegistoAluno">
                        Salvar
                    </button>
                </div>

                <div class="side-image-wrapper">
                    <img src="../img/img_registarAluno.png" alt="Ilustração registar aluno">
                </div>
            </aside>
        </section>
    </main>

    <!-- ======= RODAPÉ ======= -->
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


    <script src="js/registarAluno.js"></script>

</body>
</html>
