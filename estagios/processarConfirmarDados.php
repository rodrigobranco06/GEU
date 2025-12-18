<?php
session_start();
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['logged_in'])) {
    header("Location: ../index.php");
    exit();
}

if (!in_array($_SESSION['cargo'], ['Professor', 'Administrador'])) {
    die("Acesso negado.");
}

$conexao = estabelecerConexao();
$id_pedido = isset($_POST['id_pedido_estagio']) ? (int)$_POST['id_pedido_estagio'] : 0;
$ucs_atraso = isset($_POST['ucs_atraso']) ? (int)$_POST['ucs_atraso'] : 0;

if ($id_pedido <= 0) die("Erro: ID de pedido inválido.");

try {
    $conexao->beginTransaction();

    // 1. Atualizar ou Inserir na tabela fase_confirmacao (Guarda aqui as UC's deste estágio)
    $stmtCheck = $conexao->prepare("SELECT id_pedido_estagio FROM fase_confirmacao WHERE id_pedido_estagio = ?");
    $stmtCheck->execute([$id_pedido]);
    
    if ($stmtCheck->fetch()) {
        $sqlFC = "UPDATE fase_confirmacao 
                  SET estado_confirmacao = 'Confirmado', 
                      data_confirmacao = NOW(), 
                      numero_ucs_atraso = ? 
                  WHERE id_pedido_estagio = ?";
        $stmtFC = $conexao->prepare($sqlFC);
        $stmtFC->execute([$ucs_atraso, $id_pedido]);
    } else {
        $sqlFC = "INSERT INTO fase_confirmacao (id_pedido_estagio, numero_ucs_atraso, estado_confirmacao, data_confirmacao) 
                  VALUES (?, ?, 'Confirmado', NOW())";
        $stmtFC = $conexao->prepare($sqlFC);
        $stmtFC->execute([$id_pedido, $ucs_atraso]);
    }

    // 2. Atualizar o pedido para a próxima fase
    $sqlPedido = "UPDATE pedido_estagio 
                  SET fase_atual = 'Escolha de área e empresa', 
                      data_ultima_atualizacao = NOW(),
                      estado_pedido = 'Em processamento'
                  WHERE id_pedido_estagio = ?";
    $stmtPedido = $conexao->prepare($sqlPedido);
    $stmtPedido->execute([$id_pedido]);

    $conexao->commit();
    
    header("Location: confirmarDados.php?id_pedido_estagio=$id_pedido&status=sucesso");
    exit();

} catch (Exception $e) {
    if ($conexao->inTransaction()) $conexao->rollBack();
    error_log($e->getMessage());
    die("Erro ao processar confirmação.");
}