<?php
// professores/editarProfessor.php

include 'modelsProfessores.php';

// Se vier de updateProfessor.php, $idProfessor já está definido
if (!isset($idProfessor)) {
    $idProfessor = (int) ($_GET['id_professor'] ?? 0);
}

if ($idProfessor <= 0) {
    die('ID de professor inválido.');
}

$professor = getProfessorById($idProfessor);

if (!$professor) {
    die('Professor não encontrado.');
}

// listas para selects
$nacionalidades   = listarNacionalidades();
$escolas          = listarEscolas();
$especializacoes  = listarEspecializacoes();

$sexoDB = $professor['sexo'] ?? '';
$erros  = $erros ?? [];
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>GEU — Editar professor</title>
    <link rel="stylesheet" href="css/editarProfessor.css" />
</head>
<body>

<header id="header">
    <div class="header-logo">
        <a href="index.php">
            <img src="../img/Logo.png" alt="Gestão de Estágios Universitários">
        </a>
    </div>

    <nav class="nav-menu">
        <a href="../alunos/index.php" class="nav-link">Alunos</a>
        <a href="index.php" class="nav-link active">Professores</a>
        <a href="../empresas/index.php" class="nav-link">Empresas</a>
        <a href="../index.php" class="nav-link">Turmas</a>
        <a href="../administradores/index.php" class="nav-link">Administradores</a>

        <button class="btn-conta" id="btn-conta">
            <img src="../img/img_conta.png" alt="Conta">
        </button>
        <a href="../login.php" class="btn-sair">Sair</a>
    </nav>
</header>

<main id="main-content">

    <nav class="subtabs">
        <a href="index.php" class="subtab-link active">Ver Professores</a>
        <a href="registarProfessor.php" class="subtab-link">Registar novo professor</a>
    </nav>

    <section class="content-grid">
        <form class="form-professor"
              method="post"
              action="updateProfessor.php?id_professor=<?= urlencode($idProfessor) ?>">

            <input type="hidden" name="id_professor" value="<?= htmlspecialchars($idProfessor) ?>">

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
                    value="<?= htmlspecialchars($professor['id_professor'] ?? '') ?>"
                    readonly
                >
            </div>

            <div class="form-group">
                <label for="novaPassword">Nova password</label>

                <div class="password-wrapper">
                    <input
                        id="novaPassword"
                        name="nova_password"
                        type="password"
                        placeholder="Deixe vazio para manter a mesma"
                    >

                    <label class="toggle-password-inline">
                        <input type="checkbox" id="toggleNovaPassword">
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
                    value="<?= htmlspecialchars($professor['nome'] ?? '') ?>"
                >
            </div>

            <div class="form-group">
                <label for="dataNascimento">Data nascimento</label>
                <input
                    id="dataNascimento"
                    name="data_nascimento"
                    type="date"
                    value="<?= htmlspecialchars($professor['data_nascimento'] ?? '') ?>"
                >
            </div>

            <div class="form-group">
                <label for="sexo">Sexo</label>
                <div class="select-wrapper">
                    <select id="sexo" name="sexo">
                        <option value="">Selecione um sexo</option>
                        <option value="Masculino" <?= ($sexoDB === 'Masculino') ? 'selected' : '' ?>>Masculino</option>
                        <option value="Feminino" <?= ($sexoDB === 'Feminino') ? 'selected' : '' ?>>Feminino</option>
                        <option value="Outro"     <?= ($sexoDB === 'Outro')     ? 'selected' : '' ?>>Outro</option>
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
                                <?= ($professor['nacionalidade_id'] == $nac['id_nacionalidade']) ? 'selected' : '' ?>
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
                                <?= ($professor['especializacao_id'] == $esp['id_especializacao']) ? 'selected' : '' ?>
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
                    value="<?= htmlspecialchars($professor['nif'] ?? '') ?>"
                >
            </div>

            <div class="form-group">
                <label for="cc">Número CC</label>
                <input
                    id="cc"
                    name="cc"
                    type="text"
                    value="<?= htmlspecialchars($professor['numero_cc'] ?? '') ?>"
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
                                <?= ($professor['escola_id'] == $esc['id_escola']) ? 'selected' : '' ?>
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
                    value="<?= htmlspecialchars($professor['email_institucional'] ?? '') ?>"
                >
            </div>

            <div class="form-group">
                <label for="emailPessoal">Email pessoal</label>
                <input
                    id="emailPessoal"
                    name="emailPessoal"
                    type="email"
                    value="<?= htmlspecialchars($professor['email_pessoal'] ?? '') ?>"
                >
            </div>

            <div class="form-group">
                <label for="morada">Morada</label>
                <input
                    id="morada"
                    name="morada"
                    type="text"
                    value="<?= htmlspecialchars($professor['morada'] ?? '') ?>"
                >
            </div>

            <div class="form-group">
                <label for="cp">Código-Postal</label>
                <input
                    id="cp"
                    name="cp"
                    type="text"
                    value="<?= htmlspecialchars($professor['codigo_postal'] ?? '') ?>"
                >
            </div>

            <div class="form-group">
                <label for="cidade">Cidade</label>
                <input
                    id="cidade"
                    name="cidade"
                    type="text"
                    value="<?= htmlspecialchars($professor['cidade'] ?? '') ?>"
                >
            </div>

            <div class="side-top side-top-inside-form">
                <button
                    class="btn-salvar"
                    type="submit">
                    Salvar
                </button>

                <button
                    class="btn-eliminar"
                    type="submit"
                    formaction="deleteProfessor.php?id_professor=<?= urlencode($idProfessor) ?>"
                    onclick="return confirm('Tem a certeza que pretende eliminar este professor? Esta ação é irreversível.');"
                >
                    Eliminar
                </button>

                <a
                    class="btn-voltar"
                    href="verProfessor.php?id_professor=<?= urlencode($idProfessor) ?>"
                >
                    Voltar
                </a>
            </div>

        </form>

        <aside class="side-panel">
            <div class="side-image-wrapper">
                <img src="../img/img_registarAluno.png" alt="Ilustração professor">
            </div>
        </aside>
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
            <div class="perfil-name">Rodrigo Branco</div>

            <div class="perfil-row">
                <img src="../img/img_email.png" alt="Email" class="perfil-row-img">
                <span class="perfil-row-text">240001087@esg.ipsantarem.pt</span>
            </div>

            <a href="../verPerfil.php" class="perfil-row">
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

<script src="js/editarProfessor.js"></script>
</body>
</html>
