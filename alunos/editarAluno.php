<?php
// alunos/editarAluno.php

session_start();

// 1. Verificação de segurança (Apenas Administradores)
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['cargo'] !== 'Administrador') {
    header("Location: ../index.php"); 
    exit();
}

// 2. Carregar o modelo de alunos
// NOTA: Como não queres include_once nem mexer no db.php, 
// removemos o include '../db.php' aqui porque o modelsAlunos.php já o inclui.
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

// --- LÓGICA DO ALUNO A EDITAR ---
if (!isset($_GET['id_aluno']) || !ctype_digit($_GET['id_aluno'])) {
    header('Location: index.php');
    exit;
}

$idAluno = (int) $_GET['id_aluno'];
$erros = $erros ?? [];

// Obter dados do aluno via modelo
$aluno = getAlunoById($idAluno);
if (!$aluno) {
    header('Location: index.php');
    exit;
}

// Carregar listas para os selects do formulário
$nacionalidades = listarNacionalidades();
$cursos         = listarCursos();
$turmas         = listarTurmas();
$escolas        = listarEscolas();

$cvPath  = $aluno['cv'] ?? null;
$cvLabel = $cvPath ? basename($cvPath) : null;
?>


<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>GEU — Editar aluno</title>
    <link rel="stylesheet" href="css/editarAluno.css" />
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
        <a href="../index.php" class="nav-link">Administradores</a>

        <button class="btn-conta" id="btn-conta">
            <img src="../img/img_conta.png" alt="Conta">
        </button>
        <a href="../login.php" class="btn-sair">Sair</a>
    </nav>
</header>

<main id="main-content">

    <nav class="subtabs">
        <a href="index.php" class="subtab-link active">Ver Alunos</a>
        <a href="registarAluno.php" class="subtab-link">Registar novo aluno</a>
    </nav>

    <section class="content-grid">
        <form
            class="form-aluno"
            method="post"
            action="updateAluno.php"
            id="formEditarAluno"
            enctype="multipart/form-data"
        >
            <input type="hidden" name="id_aluno" value="<?= (int)$aluno['id_aluno'] ?>">

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
                <label>Código Aluno</label>
                <input type="text" value="<?= htmlspecialchars($aluno['id_aluno']) ?>" readonly>
            </div>

            <!-- ✅ NOVA PASSWORD (placeholder + checkbox alinhada) -->
            <div class="form-group">
                <label for="novaPassword">Nova Password <span class="opcional">(opcional)</span></label>

                <div class="password-inline">
                    <input
                        id="novaPassword"
                        name="novaPassword"
                        type="password"
                        value=""
                        placeholder="Se deixares vazio, a password atual mantém-se."
                        autocomplete="new-password"
                    >

                    <label class="toggle-password-inline">
                        <input type="checkbox" id="togglePassword">
                        <span>Mostrar password</span>
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label for="nomeAluno">Nome Aluno</label>
                <input id="nomeAluno" name="nomeAluno" type="text" value="<?= htmlspecialchars($aluno['nome'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="dataNascimento">Data nascimento</label>
                <input id="dataNascimento" name="data_nascimento" type="date" value="<?= htmlspecialchars($aluno['data_nascimento'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="sexo">Sexo</label>
                <div class="select-wrapper">
                    <select id="sexo" name="sexo">
                        <option value="">Selecione um sexo</option>
                        <?php $sexo = $aluno['sexo'] ?? ''; ?>
                        <option value="Masculino" <?= $sexo === 'Masculino' ? 'selected' : '' ?>>Masculino</option>
                        <option value="Feminino"  <?= $sexo === 'Feminino'  ? 'selected' : '' ?>>Feminino</option>
                        <option value="Outro"     <?= $sexo === 'Outro'     ? 'selected' : '' ?>>Outro</option>
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
                            <option value="<?= (int)$nac['id_nacionalidade'] ?>"
                                <?= ((int)($aluno['nacionalidade_id'] ?? 0) === (int)$nac['id_nacionalidade']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($nac['nacionalidade_desc']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <span class="chevron">▾</span>
                </div>
            </div>

            <div class="form-group">
                <label for="nif">NIF</label>
                <input id="nif" name="nif" type="text" value="<?= htmlspecialchars($aluno['nif'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="cc">Número CC</label>
                <input id="cc" name="cc" type="text" value="<?= htmlspecialchars($aluno['numero_cc'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="curso">Curso</label>
                <div class="select-wrapper">
                    <select id="curso" name="curso_id">
                        <option value="">Selecione um curso</option>
                        <?php foreach ($cursos as $curso): ?>
                            <option value="<?= (int)$curso['id_curso'] ?>"
                                <?= ((int)($aluno['curso_id'] ?? 0) === (int)$curso['id_curso']) ? 'selected' : '' ?>>
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
                    <select id="turmaId" name="turma_id">
                        <option value="">Selecione uma Turma</option>
                        <?php foreach ($turmas as $turma): ?>
                            <option
                                value="<?= (int)$turma['id_turma'] ?>"
                                data-curso-id="<?= (int)$turma['curso_id'] ?>"
                                <?= ((int)($aluno['turma_id'] ?? 0) === (int)$turma['id_turma']) ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars($turma['codigo'] ?? $turma['nome'] ?? $turma['id_turma']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <span class="chevron">▾</span>
                </div>
            </div>

            <div class="form-group">
                <label for="situacaoAcademica">Situação académica</label>
                <div class="select-wrapper">
                    <select id="situacaoAcademica" name="situacao_academica">
                        <option value="">Selecione uma situação académica</option>
                        <?php $sit = $aluno['situacao_academica'] ?? ''; ?>
                        <option value="Ativo"    <?= $sit === 'Ativo'    ? 'selected' : '' ?>>Ativo</option>
                        <option value="Suspenso" <?= $sit === 'Suspenso' ? 'selected' : '' ?>>Suspenso</option>
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
                            <option value="<?= (int)$esc['id_escola'] ?>"
                                <?= ((int)($aluno['escola_id'] ?? 0) === (int)$esc['id_escola']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($esc['escola_desc']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <span class="chevron">▾</span>
                </div>
            </div>

            <div class="form-group">
                <label for="emailInstitucional">Email institucional</label>
                <input id="emailInstitucional" name="emailInstitucional" type="email" value="<?= htmlspecialchars($aluno['email_institucional'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="emailPessoal">Email pessoal</label>
                <input id="emailPessoal" name="emailPessoal" type="email" value="<?= htmlspecialchars($aluno['email_pessoal'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="morada">Morada</label>
                <input id="morada" name="morada" type="text" value="<?= htmlspecialchars($aluno['morada'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="cp">Código-Postal</label>
                <input id="cp" name="cp" type="text" value="<?= htmlspecialchars($aluno['codigo_postal'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="cidade">Cidade</label>
                <input id="cidade" name="cidade" type="text" value="<?= htmlspecialchars($aluno['cidade'] ?? '') ?>">
            </div>

            <!-- ✅ CV (bonito + link) -->
            <div class="form-group">
                <label for="cv">CV <span class="opcional">(opcional)</span></label>

                <div class="cv-inline">
                    <input id="cv" name="cv" type="file" accept=".pdf,.doc,.docx">

                    <div class="cv-atual">
                        <span class="cv-atual-label">Atual:</span>

                        <?php if ($cvPath): ?>
                            <a class="cv-atual-link" href="../<?= htmlspecialchars($cvPath) ?>" download>
                                <?= htmlspecialchars($cvLabel) ?>
                            </a>
                        <?php else: ?>
                            <span class="cv-atual-none">Sem CV</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="linkedin">LinkedIn</label>
                <input id="linkedin" name="linkedin" type="url" value="<?= htmlspecialchars($aluno['linkedin'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="github">Portefólio (GitHub)</label>
                <input id="github" name="github" type="url" value="<?= htmlspecialchars($aluno['github'] ?? '') ?>">
            </div>

            <div class="side-top side-top-inside-form">
                <button class="btn-salvar" type="submit">
                    Salvar
                </button>

                <button
                    class="btn-eliminar"
                    type="submit"
                    formaction="deleteAluno.php?id_aluno=<?= urlencode((int)$aluno['id_aluno']) ?>"
                    onclick="return confirm('Tem a certeza que pretende eliminar este aluno? Esta ação é irreversível.');"
                >
                    Eliminar
                </button>

                <a
                    class="btn-voltar"
                    href="verAluno.php?id_aluno=<?= urlencode((int)$aluno['id_aluno']) ?>"
                >
                    Voltar
                </a>
            </div>


        </form>

        <aside class="side-panel">
            

            <div class="side-image-wrapper">
                <img src="../img/img_registarAluno.png" alt="Ilustração aluno">
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

<!-- Mantive o teu script exatamente como estava, só funciona com as classes novas -->
<script>
  // mostrar/ocultar password
  const toggle = document.getElementById("togglePassword");
  const input  = document.getElementById("novaPassword");
  if (toggle && input) {
    toggle.addEventListener("change", () => {
      input.type = toggle.checked ? "text" : "password";
    });
  }

  // filtrar turmas por curso
  const cursoSelect = document.getElementById("curso");
  const turmaSelect = document.getElementById("turmaId");

  function filtrarTurmas() {
    const cursoId = cursoSelect.value;
    const opts = turmaSelect.querySelectorAll("option[data-curso-id]");

    opts.forEach(opt => {
      const ok = cursoId && opt.dataset.cursoId === cursoId;
      opt.hidden = !ok;
    });

    if (!cursoId) {
      opts.forEach(opt => opt.hidden = true);
    }

    const selected = turmaSelect.selectedOptions[0];
    if (selected && selected.dataset && selected.dataset.cursoId && selected.dataset.cursoId !== cursoId) {
      turmaSelect.value = "";
    }
  }

  if (cursoSelect && turmaSelect) {
    filtrarTurmas();
    cursoSelect.addEventListener("change", filtrarTurmas);
  }
</script>

<script src="js/editarAluno.js"></script>

</body>
</html>
