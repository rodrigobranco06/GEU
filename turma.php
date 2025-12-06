<?php
include 'db.php';
include 'utils.php';

$conexao = estabelecerConexao();

// Lógica de Pesquisa
$termoPesquisa = isset($_GET['pesquisa']) ? trim($_GET['pesquisa']) : '';
$params = [];

// Query para buscar todas as turmas + nome do curso + nome do professor
$sql = "SELECT 
            t.id_turma,
            t.codigo,
            t.nome,
            t.ano_inicio,
            t.ano_fim,
            c.curso_desc,
            p.nome as nome_professor
        FROM turma t
        LEFT JOIN curso c ON t.curso_id = c.id_curso
        LEFT JOIN professor p ON t.professor_id = p.id_professor";

if (!empty($termoPesquisa)) {
    $sql .= " WHERE t.nome LIKE :termo OR t.codigo LIKE :termo";
    $params[':termo'] = '%' . $termoPesquisa . '%';
}

$sql .= " ORDER BY t.ano_inicio DESC, t.nome ASC";

$stmt = $conexao->prepare($sql);
$stmt->execute($params);
$turmas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>GEU — Ver Turmas</title>
    <link rel="stylesheet" href="css/turma.css" /> 
    <style>
        /* Estilos rápidos para a tabela se não tiveres CSS específico */
        .tabela-turmas { width: 100%; border-collapse: collapse; margin-top: 20px; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .tabela-turmas th { background: #f9fafb; padding: 15px; text-align: left; font-weight: 600; color: #374151; border-bottom: 2px solid #e5e7eb; }
        .tabela-turmas td { padding: 15px; border-bottom: 1px solid #e5e7eb; color: #4b5563; }
        .tabela-turmas tr:hover { background-color: #f3f4f6; cursor: pointer; }
        .search-area { margin-bottom: 20px; display: flex; align-items: center; background: #fff; padding: 10px 15px; border-radius: 8px; border: 1px solid #e5e7eb; }
        .search-area input { border: none; outline: none; width: 100%; font-size: 1rem; margin-left: 10px; }
        .badge-curso { background: #e0f2fe; color: #0369a1; padding: 4px 8px; border-radius: 12px; font-size: 0.85em; font-weight: 500; }
    </style>
</head>
<body>

    <header id="header">
        <div class="header-logo">
            <a href="index.php">
                <img src="img/Logo.png" alt="Gestão de Estágios Universitários">
            </a>
        </div>

        <nav class="nav-menu">
            <a href="alunos/index.php" class="nav-link">Alunos</a>
            <a href="professores/index.php" class="nav-link">Professores</a>
            <a href="empresas/index.php" class="nav-link">Empresas</a>
            <a href="verTurmas.php" class="nav-link active">Turmas</a> <button id="btn-conta" class="btn-conta">
                <img src="img/img_conta.png" alt="Conta">
            </button>
            <a href="login.php" class="btn-sair">Sair</a>
        </nav>
    </header>

    <main id="main-content">
        
        <div class="page-head" style="margin-bottom: 20px;">
            <h1 class="turma-titulo">Listagem de Turmas</h1>
            <button class="btn-editar" onclick="alert('Funcionalidade de criar turma ainda não implementada')">Nova Turma</button>
        </div>

        <section class="search-area">
            <form action="verTurmas.php" method="GET" style="display: contents;">
                <span>🔍</span>
                <input 
                    type="text" 
                    name="pesquisa" 
                    placeholder="Procurar por turma (Nome ou Código)" 
                    value="<?= htmlspecialchars($termoPesquisa) ?>"
                >
            </form>
        </section>

        <section>
            <table class="tabela-turmas">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nome</th>
                        <th>Curso</th>
                        <th>Ano Letivo</th>
                        <th>Professor Orientador</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($turmas) > 0): ?>
                        <?php foreach ($turmas as $t): ?>
                            <tr onclick="window.location.href='turma.php?id_turma=<?= $t['id_turma'] ?>'">
                                
                                <td style="font-weight: bold; color: #2563eb;">
                                    <?= htmlspecialchars($t['codigo']) ?>
                                </td>
                                
                                <td><?= htmlspecialchars($t['nome']) ?></td>
                                
                                <td>
                                    <span class="badge-curso">
                                        <?= htmlspecialchars($t['curso_desc'] ?? 'N/A') ?>
                                    </span>
                                </td>
                                
                                <td>
                                    <?= htmlspecialchars($t['ano_inicio']) ?> – <?= htmlspecialchars($t['ano_fim']) ?>
                                </td>
                                
                                <td>
                                    <?= htmlspecialchars($t['nome_professor'] ?? 'Sem professor') ?>
                                </td>

                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 30px;">
                                Nenhuma turma encontrada.
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
            <p><strong>Email:</strong> geral@ipsantarem.pt</p>
            <p><strong>Telefone:</strong> +351 243 309 520</p>
            <p><strong>Endereço:</strong> Complexo Andaluz, Santarém</p>
        </div>
        <div class="logos">
            <img src="img/Logo.png" alt="GEU">
            <img src="img/img_confinanciado.png" alt="Confinanciado">
        </div>
    </footer>

</body>
</html>