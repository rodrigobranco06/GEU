<?php
// empresas/registarEmpresa.php
include 'modelsEmpresas.php';

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
        <a href="../index.php"><img src="../img/Logo.png" alt="GEU"></a>
    </div>
    <nav class="nav-menu">
        <a href="../alunos/index.php" class="nav-link">Alunos</a>
        <a href="../professores/index.php" class="nav-link">Professores</a>
        <a href="index.php" class="nav-link active">Empresas</a>
        <a href="../index.php" class="nav-link">Turmas</a>
        <a href="../administradores/index.php" class="nav-link">Administradores</a>

        <button class="btn-conta"><img src="../img/img_conta.png" alt="Conta"></button>
        <a href="../login.php" class="btn-sair">Sair</a>
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

        <aside class="side-panel">
            <div class="side-image-wrapper">
                <img src="../img/img_registarAluno.png" alt="Ilustração">
            </div>
        </aside>
    </section>
</main>
</body>
</html>