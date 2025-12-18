<?php
include 'db.php';
include 'utils.php';

$conexao = estabelecerConexao();

// 1. Buscar Cursos (para o Select do Modal de criar turma)
$stmtCursos = $conexao->query("SELECT id_curso, curso_desc FROM curso ORDER BY curso_desc");
$cursos = $stmtCursos->fetchAll(PDO::FETCH_ASSOC);

// 2. Buscar Professores (para o Select do Modal de criar turma)
$stmtProfs = $conexao->query("SELECT id_professor, nome FROM professor ORDER BY nome");
$professores = $stmtProfs->fetchAll(PDO::FETCH_ASSOC);

// 3. Buscar Todas as Turmas para a listagem
// JOIN com professor para mostrar o nome do orientador no card
$sql = "SELECT 
            t.id_turma,
            t.nome AS turma_nome,
            t.codigo,
            t.ano_inicio,
            t.ano_fim,
            p.nome AS professor_nome
        FROM turma t
        LEFT JOIN professor p ON t.professor_id = p.id_professor
        ORDER BY t.ano_inicio DESC, t.nome ASC";

$stmtTurmas = $conexao->query($sql);
$turmas = $stmtTurmas->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Estágios Universitários - Turmas</title>
    <link rel="stylesheet" href="css/index.css">
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
            <a href="index.php" class="nav-link active">Turmas</a>
            <a href="administradores/index.php" class="nav-link">Administradores</a>

            <button id="btn-conta" class="btn-conta">
                <img src="img/img_conta.png" alt="Conta">
            </button>
            <a href="login.php" class="btn-sair">Sair</a>
        </nav>
    </header>

    <main id="main-content">
        <div class="main-header">
            <h2 class="titulo-pagina">Turmas</h2>
            <button class="btn-criar-turma" onclick="abrirModal()">Criar Nova Turma</button>
        </div>

        <div class="turmas-container">
            <?php if (count($turmas) > 0): ?>
                <?php foreach ($turmas as $t): ?>
                    
                    <a href="turma.php?id_turma=<?= $t['id_turma'] ?>" class="turma-link">
                        <div class="turma-card">
                            
                            <h3 class="turma-nome">
                                <?= htmlspecialchars($t['turma_nome']) ?> 
                            </h3>
                            
                            <p class="turma-professor-label">Professor orientador</p>
                            
                            <p class="turma-professor-nome <?= empty($t['professor_nome']) ? 'indefinido' : '' ?>">
                                <?= htmlspecialchars($t['professor_nome'] ?: 'Indefinido') ?>
                            </p>

                        </div>
                    </a>

                <?php endforeach; ?>
            <?php else: ?>
                <p style="padding: 20px; color: #666;">Ainda não existem turmas criadas.</p>
            <?php endif; ?>
        </div>

    </main>

    <div id="modal-criar-turma" class="modal-overlay" style="display:none;">
        <div class="modal-content-box">

            <div class="modal-flex">

                <form class="modal-form" action="adicionarTurma.php" method="POST">
                    
                    <label>Código Turma:</label>
                    <input type="text" name="codigo" required placeholder="Ex: TPSI-24">

                    <label>Nome:</label>
                    <input type="text" name="nome" required placeholder="Ex: TPSI - 2º Ano">

                    <label>Ano Início:</label>
                    <input type="number" name="ano_inicio" required placeholder="2024">
                    
                    <label>Ano Fim:</label>
                    <input type="number" name="ano_fim" placeholder="2025">

                    <label>Ano Curricular:</label>
                    <input type="number" name="ano_curricular" placeholder="Ex: 2">

                    <label>Curso:</label>
                    <select name="curso_id" required>
                        <option value="" disabled selected>Selecione o curso...</option>
                        <?php foreach ($cursos as $c): ?>
                            <option value="<?= $c['id_curso'] ?>">
                                <?= htmlspecialchars($c['curso_desc']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label>Professor Orientador:</label>
                    <select name="professor_id">
                        <option value="">Indefinido</option>
                        <?php foreach ($professores as $p): ?>
                            <option value="<?= $p['id_professor'] ?>">
                                <?= htmlspecialchars($p['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <div class="modal-buttons" style="margin-top: 20px;">
                        <button class="modal-btn criar" type="submit">Criar</button>
                        <button class="modal-btn voltar" type="button" onclick="fecharModal()">Voltar</button>
                    </div>

                </form>

                <img class="modal-img" src="img/img_editar_turma.png" alt="Ilustração">
            </div>

        </div>
    </div>

    <hr>

    <footer id="footer">
        <div class="contactos">
            <h3>Contactos</h3>
            <p>
                <img src="img/img_email.png" alt="Email" style="width:20px; vertical-align:middle;">
                <strong>Email:</strong> geral@ipsantarem.pt
            </p>
            <p>
                <img src="img/img_telemovel.png" alt="Telefone" style="width:20px; vertical-align:middle;">
                <strong>Telefone:</strong> +351 243 309 520
            </p>
            <p>
                <img src="img/img_localizacao.png" alt="Endereço" style="width:20px; vertical-align:middle;">
                <strong>Endereço:</strong> Complexo Andaluz, Santarém
            </p>
        </div>

        <div class="logos">
            <img src="img/Logo.png" alt="Gestão de Estágios Universitários">
            <img src="img/img_confinanciado.png" alt="Confinanciado">
        </div>
    </footer>

    <script>
        function abrirModal() {
            document.getElementById('modal-criar-turma').style.display = 'flex';
        }

        function fecharModal() {
            document.getElementById('modal-criar-turma').style.display = 'none';
        }

        // Fechar ao clicar fora do modal
        window.onclick = function(event) {
            const modal = document.getElementById('modal-criar-turma');
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
    
    <script src="js/index.js"></script>

</body>
</html>