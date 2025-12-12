<?php
// empresas/verEmpresa.php
include 'modelsEmpresas.php';
$idEmpresa = isset($_GET['id_empresa']) ? (int)$_GET['id_empresa'] : 0;
$empresa = getEmpresaById($idEmpresa);
if (!$empresa) die('Empresa não encontrada.');
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>GEU — Ver Empresa</title>
    <link rel="stylesheet" href="../professores/css/verProfessor.css">
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
        <button class="btn-conta"><img src="../img/img_conta.png" alt="Conta"></button>
        <a href="../login.php" class="btn-sair">Sair</a>
    </nav>
</header>
<main id="main-content">
    <nav class="subtabs">
        <a href="index.php" class="subtab-link active">Ver Empresas</a>
        <a href="registarEmpresa.php" class="subtab-link">Registar nova empresa</a>
    </nav>
    <section class="content-grid">
        <form class="form-professor">
            <div class="form-group"><label>Nº Empresa</label><input type="text" value="<?= $empresa['id_empresa'] ?>" readonly></div>
            <div class="form-group"><label>Nome</label><input type="text" value="<?= htmlspecialchars($empresa['nome']) ?>" readonly></div>
            <div class="form-group"><label>Ramo</label><input type="text" value="<?= htmlspecialchars($empresa['ramo_atividade_desc'] ?? '') ?>" readonly></div>
            <div class="form-group"><label>NIF</label><input type="text" value="<?= htmlspecialchars($empresa['nif']) ?>" readonly></div>
            <div class="form-group"><label>Email</label><input type="text" value="<?= htmlspecialchars($empresa['email']) ?>" readonly></div>
            <div class="form-group"><label>Telefone</label><input type="text" value="<?= htmlspecialchars($empresa['telefone'] ?? '') ?>" readonly></div>
            <div class="form-group"><label>Cidade</label><input type="text" value="<?= htmlspecialchars($empresa['cidade'] ?? '') ?>" readonly></div>
            <div class="form-group"><label>País</label><input type="text" value="<?= htmlspecialchars($empresa['pais_desc'] ?? '') ?>" readonly></div>
            <div class="form-group"><label>Website</label><input type="text" value="<?= htmlspecialchars($empresa['website'] ?? '') ?>" readonly></div>
            
            <h3>Responsável</h3>
            <div class="form-group"><label>Nome</label><input type="text" value="<?= htmlspecialchars($empresa['nome_responsavel'] ?? '') ?>" readonly></div>
            <div class="form-group"><label>Email</label><input type="text" value="<?= htmlspecialchars($empresa['email_responsavel'] ?? '') ?>" readonly></div>
        </form>

        <aside class="side-panel">
            <div class="side-top">
                <a href="editarEmpresa.php?id_empresa=<?= $idEmpresa ?>" class="btn-editar">Editar</a>
                <a class="btn-voltar" href="index.php">Voltar</a>
            </div>
            <div class="side-image-wrapper"><img src="../img/img_registarAluno.png" alt="Ilustração"></div>
        </aside>
    </section>
</main>
</body>
</html>