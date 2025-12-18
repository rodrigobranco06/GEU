<?php
session_start();
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['logged_in'])) {
    header("Location: ../index.php");
    exit();
}

$conexao = estabelecerConexao();
$id_pedido = (int)$_POST['id_pedido_estagio'];
$resposta = $_POST['resposta_empresa'];

try {
    $conexao->beginTransaction();

    // 1. Atualizar ou Inserir na tabela fase_resposta (campos: resposta_empresa, data_resposta)
    $stmtCheck = $conexao->prepare("SELECT id_pedido_estagio FROM fase_resposta WHERE id_pedido_estagio = ?");
    $stmtCheck->execute([$id_pedido]);
    
    if ($stmtCheck->fetch()) {
        $sql = "UPDATE fase_resposta SET resposta_empresa = ?, data_resposta = NOW() WHERE id_pedido_estagio = ?";
        $conexao->prepare($sql)->execute([$resposta, $id_pedido]);
    } else {
        $sql = "INSERT INTO fase_resposta (id_pedido_estagio, resposta_empresa, data_resposta) VALUES (?, ?, NOW())";
        $conexao->prepare($sql)->execute([$id_pedido, $resposta]);
    }

    // 2. Lógica de fluxo do pedido principal
    if ($resposta === 'Aceite') {
        $proxima_fase = 'Plano estágio';
    } elseif ($resposta === 'Recusado') {
        // Se recusado, o fluxo volta para a escolha de empresa
        $proxima_fase = 'Escolha de área e empresa';
        
        // Limpar dados de envio anteriores para permitir novo ciclo
        $conexao->prepare("DELETE FROM fase_email WHERE id_pedido_estagio = ?")->execute([$id_pedido]);
    } else {
        $proxima_fase = 'Resposta ao email';
    }

    $stmtPedido = $conexao->prepare("UPDATE pedido_estagio SET fase_atual = ?, data_ultima_atualizacao = NOW() WHERE id_pedido_estagio = ?");
    $stmtPedido->execute([$proxima_fase, $id_pedido]);

    $conexao->commit();
    header("Location: respostaEmail.php?id_pedido_estagio=$id_pedido&status=sucesso");
    exit();

} catch (Exception $e) {
    if ($conexao->inTransaction()) $conexao->rollBack();
    error_log($e->getMessage());
    die("Erro ao processar resposta.");
}