<?php
// empresas/index.php
include 'modelsEmpresas.php';
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
                    <td><?= htmlspecialchars($emp['numero_estagios'] ?? '0') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</main>
</body>
</html>