<?php
include 'db.php';
include 'utils.php';

$conexao = estabelecerConexao();

// 1. Verificar se recebemos o ID da turma
if (!isset($_GET['id_turma'])) {
    // Se não houver ID, redireciona de volta para a lista geral
    header('Location: verTurmas.php'); 
    exit;
}

$idTurma = $_GET['id_turma'];

// 2. Buscar informações da Turma (Nome, Datas, Curso)
$sqlTurma = "SELECT t.*, c.curso_desc 
             FROM turma t 
             LEFT JOIN curso c ON t.curso_id = c.id_curso
             WHERE t.id_turma = :id";
$stmt = $conexao->prepare($sqlTurma);
$stmt->execute([':id' => $idTurma]);
$turma = $stmt->fetch(PDO::FETCH_ASSOC);

// Se a turma não existir na BD
if (!$turma) {
    die("Turma não encontrada.");
}

// 3. Buscar os Alunos da Turma e o Estado do Estágio
// Fazemos LEFT JOIN com pedido_estagio para saber o estado
$sqlAlunos = "SELECT 
                a.id_aluno, 
                a.nome, 
                pe.estado_pedido 
              FROM aluno a
              LEFT JOIN pedido_estagio pe ON a.id_aluno = pe.aluno_id
              WHERE a.turma_id = :id
              ORDER BY a.nome ASC";

$stmtAlunos = $conexao->prepare($sqlAlunos);
$stmtAlunos->execute([':id' => $idTurma]);
$alunos = $stmtAlunos->fetchAll(PDO::FETCH_ASSOC);

// Função auxiliar para definir a classe CSS baseada no estado da BD
function obterClasseEstado($estado) {
    if (empty($estado)) return 'sem'; // Cinzento
    
    $estado = mb_strtolower($estado, 'UTF-8');
    
    if (strpos($estado, 'aceite') !== false || strpos($estado, 'aprovado') !== false) {
        return 'aceite'; // Verde
    }
    if (strpos($estado, 'aguarda') !== false || strpos($estado, 'pendente') !== false) {
        return 'aguardando'; // Amarelo
    }
    
    return 'sem'; // Padrão
}

// Função para formatar o texto do estado
function obterTextoEstado($estado) {
    return !empty($estado) ? $estado : 'Sem estágio';
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>GEU — <?= htmlspecialchars($turma['nome']) ?></title>
    <link rel="stylesheet" href="css/turma.css" />
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
            <a href="index.php" class="nav-link active">Turmas</a> <button id="btn-conta" class="btn-conta">
                <img src="img/img_conta.png" alt="Conta">
            </button>
            <a href="login.php" class="btn-sair">Sair</a>
        </nav>
    </header>

    <main id="main-content">
        
        <div class="page-head">
            <h1 class="turma-titulo">
                <?= htmlspecialchars($turma['nome']) ?> 
                (<?= htmlspecialchars($turma['ano_inicio']) ?>–<?= htmlspecialchars($turma['ano_fim']) ?>)
            </h1>
            
            <button class="btn-editar" onclick="abrirModal()">Editar Turma</button>
        </div>

        <section class="alunos-grid">
            
            <?php if (count($alunos) > 0): ?>
                <?php foreach ($alunos as $aluno): ?>
                    <?php 
                        $classeCss = obterClasseEstado($aluno['estado_pedido']);
                        $textoEstado = obterTextoEstado($aluno['estado_pedido']);
                    ?>
                    
                    <a href="aluno.php?id=<?= $aluno['id_aluno'] ?>">
                        <article class="aluno-card">
                            <h3 class="aluno-nome">
                                <?= htmlspecialchars($aluno['nome']) ?> - <?= htmlspecialchars($aluno['id_aluno']) ?>
                            </h3>
                            <p class="aluno-label">Estado do estágio</p>
                            <p class="aluno-estado <?= $classeCss ?>">
                                <?= htmlspecialchars($textoEstado) ?>
                            </p>
                        </article>
                    </a>

                <?php endforeach; ?>
            <?php else: ?>
                <p style="grid-column: 1/-1; color: #666; padding: 20px;">
                    Não existem alunos registados nesta turma.
                </p>
            <?php endif; ?>

        </section>

    </main>

    <div id="modal-editar-turma" class="modal-overlay" style="display:none;"> <div class="modal-content-box"> <div class="modal-flex">

                <form class="modal-form">
                    <label>Código Turma:</label>
                    <input type="text" value="<?= htmlspecialchars($turma['codigo']) ?>">

                    <label>Turma:</label>
                    <input type="text" value="<?= htmlspecialchars($turma['nome']) ?>">

                    <label>Ano curricular:</label>
                    <input type="text" value="<?= htmlspecialchars($turma['ano_curricular'] ?? '') ?>">

                    <label>ID Professor:</label>
                    <input type="text" value="<?= htmlspecialchars($turma['professor_id'] ?? '') ?>">
                    
                    </form>

                <img class="modal-img" src="img/img_editar_turma.png" alt="Editar Turma">
            </div>

            <div class="modal-buttons">
                <button class="modal-btn guardar">Guardar</button>
                <button class="modal-btn voltar" onclick="fecharModal()">Voltar</button>
            </div>

        </div>
    </div>

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

    <script>
        function abrirModal() {
            // Nota: No teu CSS o ID é #modal-editar-turma e usa display flex para centrar
            const modal = document.getElementById('modal-editar-turma');
            modal.style.display = 'flex'; 
        }

        function fecharModal() {
            const modal = document.getElementById('modal-editar-turma');
            modal.style.display = 'none';
        }

        // Fechar ao clicar fora do modal
        window.onclick = function(event) {
            const modal = document.getElementById('modal-editar-turma');
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
</body>
</html>