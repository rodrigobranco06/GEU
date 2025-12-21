<?php
session_start();
include 'db.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

if (!in_array($_SESSION['cargo'], ['Administrador', 'Professor'])) {
    die("Acesso negado.");
}

$conexao = estabelecerConexao();
$acao = $_POST['acao'] ?? '';
$idAluno = (int)($_POST['id_aluno'] ?? 0);

if ($idAluno <= 0) die("ID de aluno inválido.");

try {
    $conexao->beginTransaction();

    if ($acao === 'criar_pedido') {
        $stmtT = $conexao->prepare("SELECT professor_id FROM turma WHERE id_turma = (SELECT turma_id FROM aluno WHERE id_aluno = ?)");
        $stmtT->execute([$idAluno]);
        $resTurma = $stmtT->fetch(PDO::FETCH_ASSOC);
        $professorOrientador = $resTurma['professor_id'] ?? null;

        $sqlInsert = "INSERT INTO pedido_estagio (estado_pedido, fase_atual, aluno_id, professor_id, data_criacao) 
                      VALUES ('Aguardar confirmação', 'Confirmar Dados', :aluno, :prof, NOW())";
        $stmtInsert = $conexao->prepare($sqlInsert);
        $stmtInsert->execute([':aluno' => $idAluno, ':prof' => $professorOrientador]);
        
        $idNovoPedido = $conexao->lastInsertId();

        $sqlFase = "INSERT INTO fase_confirmacao (id_pedido_estagio, numero_ucs_atraso, estado_confirmacao) 
                    VALUES (?, '0', 'Pendente')";
        $conexao->prepare($sqlFase)->execute([$idNovoPedido]);

    } elseif ($acao === 'apagar_pedido') {
        $idPedido = (int)($_POST['id_pedido'] ?? 0);
        
        if ($idPedido > 0) {
            $conexao->prepare("DELETE FROM fase_confirmacao WHERE id_pedido_estagio = ?")->execute([$idPedido]);
            $conexao->prepare("DELETE FROM fase_area WHERE id_pedido_estagio = ?")->execute([$idPedido]);
            $conexao->prepare("DELETE FROM fase_email WHERE id_pedido_estagio = ?")->execute([$idPedido]);
            $conexao->prepare("DELETE FROM fase_resposta WHERE id_pedido_estagio = ?")->execute([$idPedido]);
            $conexao->prepare("DELETE FROM fase_plano WHERE id_pedido_estagio = ?")->execute([$idPedido]);
            $conexao->prepare("DELETE FROM fase_avaliacao WHERE id_pedido_estagio = ?")->execute([$idPedido]);

            $conexao->prepare("DELETE FROM pedido_estagio WHERE id_pedido_estagio = ?")->execute([$idPedido]);
        }
    }

    $conexao->commit();
    header("Location: aluno.php?id_aluno=" . $idAluno);
    exit();

} catch (PDOException $e) {
    if ($conexao->inTransaction()) $conexao->rollBack();
    die("Erro no processamento: " . $e->getMessage());
}