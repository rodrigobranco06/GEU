<?php
include '../db.php';
include '../utils.php';

// Ligação à Base de Dados
$conexao = estabelecerConexao();

// Lógica de Pesquisa
$termoPesquisa = isset($_GET['pesquisa']) ? trim($_GET['pesquisa']) : '';
$params = [];

/**
 * CONSTRUÇÃO DA QUERY
 * 1. Extraímos o número de aluno do email (antes do @).
 * 2. Juntamos com curso para ver o nome do curso.
 * 3. Juntamos com pedido_estagio para ver se tem estágio.
 * 4. Juntamos com empresa para ver o nome da empresa associada ao pedido.
 */
$sql = "SELECT 
            a.id_aluno,
            a.nome,
            a.email_institucional,
            -- Extrair o número de aluno do email (ex: 240001087 de 240001087@esg...)
            SUBSTRING_INDEX(a.email_institucional, '@', 1) as numero_aluno,
            c.curso_desc,
            e.nome AS nome_empresa,
            pe.estado_pedido
        FROM aluno a
        LEFT JOIN curso c 
            ON a.curso_id = c.id_curso
        LEFT JOIN pedido_estagio pe 
            ON pe.aluno_id = a.id_aluno
        LEFT JOIN empresa e 
            ON pe.empresa_id = e.id_empresa";

// Adicionar filtros de pesquisa
if (!empty($termoPesquisa)) {
    // Pesquisa pelo nome ou pelo número (extraído do email)
    $sql .= " WHERE a.nome LIKE :termo OR a.email_institucional LIKE :termo";
    $params[':termo'] = '%' . $termoPesquisa . '%';
}

$sql .= " ORDER BY a.nome ASC";

$alunos = [];

try {
    $stmt = $conexao->prepare($sql);
    $stmt->execute($params);
    $alunos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro na base de dados: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>GEU — Ver Alunos</title>
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
            <a href="index.php" class="nav-link active">Alunos</a>
            <a href="../professores/index.php" class="nav-link">Professores</a>
            <a href="../empresas/index.php" class="nav-link">Empresas</a>
            <a href="../index.php" class="nav-link">Turmas</a>

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

        <section class="search-area">
            <form action="index.php" method="GET" style="display: contents;">
                <div class="search-wrapper">
                    <span class="search-icon">🔍</span>
                    <input 
                        type="text" 
                        name="pesquisa"
                        placeholder="Procurar por aluno (Nome ou Número)" 
                        aria-label="Procurar por aluno"
                        value="<?= htmlspecialchars($termoPesquisa) ?>"
                    >
                </div>
            </form>
        </section>

        <section class="tabela-alunos">
            <table>
                <thead>
                    <tr>
                        <th>Número de aluno</th>
                        <th>Nome do aluno</th>
                        <th>Curso</th>
                        <th>Empresa</th>
                        <th>Estado do estágio</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($alunos) > 0): ?>
                        <?php foreach ($alunos as $aluno): ?>
                            <tr onclick="window.location.href='verAluno.php?id_aluno=<?= $aluno['id_aluno'] ?>'" style="cursor: pointer;">
                                
                                <td><?= htmlspecialchars($aluno['numero_aluno'] ?? '') ?></td>
                                
                                <td><?= htmlspecialchars($aluno['nome'] ?? '') ?></td>
                                
                                <td><?= htmlspecialchars($aluno['curso_desc'] ?? 'Sem curso') ?></td>
                                
                                <td>
                                    <?php 
                                        if (!empty($aluno['nome_empresa'])) {
                                            echo htmlspecialchars($aluno['nome_empresa']);
                                        } else {
                                            echo '<span style="color: #999;">Sem empresa</span>';
                                        }
                                    ?>
                                </td>
                                
                                <td>
                                    <?php 
                                        // Mapeamento simples de cores/texto se necessário
                                        $estado = $aluno['estado_pedido'] ?? 'Sem estágio';
                                        echo htmlspecialchars($estado);
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 20px; color: #666;">
                                Nenhum aluno encontrado.
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