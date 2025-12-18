<?php
session_start();
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['logged_in'])) {
    header("Location: ../index.php");
    exit();
}

$conexao = estabelecerConexao();
$id_pedido = (int)$_POST['id_pedido_estagio'];
$acao = $_POST['acao'] ?? '';

try {
    $conexao->beginTransaction();

    if ($acao === 'upload_cv') {
        if (isset($_FILES['cv_file']) && $_FILES['cv_file']['error'] === UPLOAD_ERR_OK) {
            
            // 1. Ir buscar o código e nome do aluno para o nome do ficheiro
            $stmtAluno = $conexao->prepare("
                SELECT a.id_aluno, a.nome 
                FROM pedido_estagio p 
                JOIN aluno a ON p.aluno_id = a.id_aluno 
                WHERE p.id_pedido_estagio = ?
            ");
            $stmtAluno->execute([$id_pedido]);
            $aluno = $stmtAluno->fetch(PDO::FETCH_ASSOC);

            if (!$aluno) die("Erro: Aluno não encontrado.");

            // Limpar o nome do aluno para evitar caracteres estranhos no nome do ficheiro
            $nomeLimpo = str_replace(' ', '_', preg_replace('/[^A-Za-z0-9 ]/', '', $aluno['nome']));
            
            $extensao = strtolower(pathinfo($_FILES['cv_file']['name'], PATHINFO_EXTENSION));
            if ($extensao !== 'pdf') die("Apenas ficheiros PDF são permitidos.");

            // Novo padrão de nome: cv_codigoAluno_nomeAluno.pdf
            $novoNome = "cv_" . $aluno['id_aluno'] . "_" . $nomeLimpo . ".pdf";
            $diretorio = "../uploads/cv/";

            if (!is_dir($diretorio)) mkdir($diretorio, 0777, true);

            // 2. Mover o ficheiro (substitui se já existir um com o mesmo nome)
            if (move_uploaded_file($_FILES['cv_file']['tmp_name'], $diretorio . $novoNome)) {
                
                // 3. Atualizar a tabela fase_email
                // Primeiro verificamos se já existe um registo para este pedido
                $stmtCheck = $conexao->prepare("SELECT id_pedido_estagio FROM fase_email WHERE id_pedido_estagio = ?");
                $stmtCheck->execute([$id_pedido]);
                
                if ($stmtCheck->fetch()) {
                    $sql = "UPDATE fase_email SET cv = ?, estado_envio_email = 'Pendente' WHERE id_pedido_estagio = ?";
                    $conexao->prepare($sql)->execute([$novoNome, $id_pedido]);
                } else {
                    $sql = "INSERT INTO fase_email (id_pedido_estagio, cv, estado_envio_email) VALUES (?, ?, 'Pendente')";
                    $conexao->prepare($sql)->execute([$id_pedido, $novoNome]);
                }
            } else {
                die("Erro ao mover o ficheiro para a pasta de destino.");
            }
        }
    } 
    elseif ($acao === 'confirmar_envio') {
        $cvFinal = $_POST['cv_atual'] ?? '';

        // Marcar como enviado e avançar o pedido
        $stmtEmail = $conexao->prepare("
            UPDATE fase_email 
            SET estado_envio_email = 'Enviado', data_envio_email = NOW(), cv = ? 
            WHERE id_pedido_estagio = ?
        ");
        $stmtEmail->execute([$cvFinal, $id_pedido]);

        $stmtPedido = $conexao->prepare("UPDATE pedido_estagio SET fase_atual = 'Resposta ao email', data_ultima_atualizacao = NOW() WHERE id_pedido_estagio = ?");
        $stmtPedido->execute([$id_pedido]);
    }

    $conexao->commit();
    header("Location: envioEmail.php?id_pedido_estagio=$id_pedido");
    exit();

} catch (Exception $e) {
    if ($conexao->inTransaction()) $conexao->rollBack();
    error_log($e->getMessage());
    die("Erro no processamento: " . $e->getMessage());
}