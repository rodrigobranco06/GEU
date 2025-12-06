<?php
include '../db.php';
include '../utils.php';

// Ligação à Base de Dados
$conexao = estabelecerConexao();

// Lógica de Pesquisa
$termoPesquisa = isset($_GET['pesquisa']) ? trim($_GET['pesquisa']) : '';
$params = [];

/**
 * CONSTRUÇÃO DA QUERY CORRIGIDA (Baseada no ScriptCriacaoTabelas_GEU.sql)
 * * 1. Selecionamos os dados da 'empresa' (e).
 * 2. Fazemos JOIN com 'ramo_atividade' (ra) para obter o nome do ramo (ramo_atividade_desc).
 * 3. Fazemos JOIN com 'pedido_estagio' (pe) para contar quantos alunos estão associados.
 */
$sql = "SELECT 
            e.id_empresa,
            e.nome,
            e.email,
            e.numero_estagios AS vagas_totais,
            ra.ramo_atividade_desc,
            COUNT(pe.id_pedido_estagio) as total_alunos_colocados
        FROM empresa e
        LEFT JOIN ramo_atividade ra 
            ON e.ramo_atividade_id = ra.id_ramo_atividade
        LEFT JOIN pedido_estagio pe 
            ON e.id_empresa = pe.empresa_id";

// Adicionar filtros de pesquisa
if (!empty($termoPesquisa)) {
    $sql .= " WHERE e.nome LIKE :termo OR e.email LIKE :termo OR ra.ramo_atividade_desc LIKE :termo";
    $params[':termo'] = '%' . $termoPesquisa . '%';
}

// Agrupar por empresa (obrigatório por causa do COUNT) e ordenar
$sql .= " GROUP BY e.id_empresa ORDER BY e.nome ASC";

$empresas = [];

try {
    $stmt = $conexao->prepare($sql);
    $stmt->execute($params);
    $empresas = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Tratamento de erros
    die("Erro na base de dados: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>GEU — Ver Empresas</title>
    <link rel="stylesheet" href="css/index.css" />
</head>
<body>

    <header id="header">
        <div class="header-logo">
            <a href="../index.php">
                <img src="../img/Logo.png" alt="Gestão de Estágios Universitários">
            </a>
        </div>

        <nav class="nav-menu">
            <a href="../alunos/index.php" class="nav-link">Alunos</a>
            <a href="../professores/index.php" class="nav-link">Professores</a>
            <a href="index.php" class="nav-link active">Empresas</a>
            <a href="../index.php" class="nav-link">Turmas</a>

            <button class="btn-conta" id="btn-conta">
                <img src="../img/img_conta.png" alt="Conta">
            </button>
            <a href="../login.php" class="btn-sair">Sair</a>
        </nav>
    </header>

    <main id="main-content">

        <nav class="subtabs">
            <a href="index.php" class="subtab-link active">Ver Empresas</a>
            <a href="registarEmpresa.php" class="subtab-link">Registar nova empresa</a>
        </nav>

        <section class="search-area">
            <form action="index.php" method="GET" style="display: contents;">
                <div class="search-wrapper">
                    <span class="search-icon">🔍</span>
                    <input 
                        type="text" 
                        name="pesquisa" 
                        placeholder="Procurar por empresa" 
                        aria-label="Procurar por empresa"
                        value="<?= htmlspecialchars($termoPesquisa) ?>"
                    >
                </div>
            </form>
        </section>

        <section class="tabela-empresas">
            <table>
                <thead>
                    <tr>
                        <th>Número da Empresa</th>
                        <th>Nome da Empresa</th>
                        <th>Ramo de atividade</th>
                        <th>Email</th>
                        <th>Alunos Colocados</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($empresas) > 0): ?>
                        <?php foreach ($empresas as $empresa): ?>
                            <tr onclick="window.location.href='verEmpresa.php?id_empresa=<?= $empresa['id_empresa'] ?>'" style="cursor: pointer;">
                                <td><?= htmlspecialchars($empresa['id_empresa'] ?? '') ?></td>
                                <td><?= htmlspecialchars($empresa['nome'] ?? '') ?></td>
                                
                                <td><?= htmlspecialchars($empresa['ramo_atividade_desc'] ?? 'Não definido') ?></td>
                                
                                <td><?= htmlspecialchars($empresa['email'] ?? '') ?></td>
                                
                                <td style="text-align: center;">
                                    <?= htmlspecialchars($empresa['total_alunos_colocados'] ?? '0') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 20px; color: #666;">
                                Nenhuma empresa encontrada.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
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

    <script src="js/index.js"></script>

</body>
</html>