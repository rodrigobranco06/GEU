<?php
session_start();
include '../db.php'; // Preferência por include mantida

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['logged_in'])) {
    header("Location: ../index.php");
    exit();
}

if (!in_array($_SESSION['cargo'], ['Professor', 'Administrador'])) {
    die("Acesso negado.");
}

$conexao = estabelecerConexao();
$id_pedido = (int)$_POST['id_pedido_estagio'];
$id_empresa = (int)$_POST['empresa_id'];
$id_area = (int)$_POST['area_cientifica_id'];
$cidade = $_POST['cidade'];

try {
    $conexao->beginTransaction();

    // 1. Atualizar empresa_id na tabela principal PEDIDO_ESTAGIO
    $stmtP = $conexao->prepare("UPDATE pedido_estagio SET empresa_id = ?, fase_atual = 'Envio de email', data_ultima_atualizacao = NOW() WHERE id_pedido_estagio = ?");
    $stmtP->execute([$id_empresa, $id_pedido]);

    // 2. Gravar os dados na tabela FASE_AREA seguindo rigorosamente o teu SQL
    $stmtCheck = $conexao->prepare("SELECT id_pedido_estagio FROM fase_area WHERE id_pedido_estagio = ?");
    $stmtCheck->execute([$id_pedido]);
    
    if ($stmtCheck->fetch()) {
        $sql = "UPDATE fase_area SET cidade = ?, area_cientifica_id = ?, estado_definicao_area = 'Definido', data_definicao_area = CURDATE() WHERE id_pedido_estagio = ?";
        $stmt = $conexao->prepare($sql);
        $stmt->execute([$cidade, $id_area, $id_pedido]);
    } else {
        $sql = "INSERT INTO fase_area (id_pedido_estagio, cidade, area_cientifica_id, estado_definicao_area, data_definicao_area) VALUES (?, ?, ?, 'Definido', CURDATE())";
        $stmt = $conexao->prepare($sql);
        $stmt->execute([$id_pedido, $cidade, $id_area]);
    }

    $conexao->commit();
    header("Location: escolhaAreaEmpresa.php?id_pedido_estagio=$id_pedido&status=sucesso");
    exit();

} catch (Exception $e) {
    if ($conexao->inTransaction()) $conexao->rollBack();
    error_log($e->getMessage());
    die("Erro ao processar a definição.");
}