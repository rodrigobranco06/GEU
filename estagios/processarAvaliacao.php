<?php
session_start();
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['logged_in'])) {
    header("Location: ../index.php");
    exit();
}

$conexao = estabelecerConexao();
$id_pedido = (int)$_POST['id_pedido_estagio'];
$acao = $_POST['acao'] ?? 'guardar_avaliacao';

try {
    $conexao->beginTransaction();

    if ($acao === 'upload_relatorio') {
        if (isset($_FILES['relatorio_file']) && $_FILES['relatorio_file']['error'] === UPLOAD_ERR_OK) {
            
            $stmtDados = $conexao->prepare("SELECT a.id_aluno, a.nome, fa.relatorio FROM pedido_estagio p JOIN aluno a ON p.aluno_id = a.id_aluno LEFT JOIN fase_avaliacao fa ON p.id_pedido_estagio = fa.id_pedido_estagio WHERE p.id_pedido_estagio = ?");
            $stmtDados->execute([$id_pedido]);
            $res = $stmtDados->fetch(PDO::FETCH_ASSOC);

            $diretorio = "../uploads/relatorios/";
            if (!is_dir($diretorio)) mkdir($diretorio, 0777, true);

            // Eliminar ficheiro antigo se existir
            if (!empty($res['relatorio']) && file_exists($diretorio . $res['relatorio'])) {
                unlink($diretorio . $res['relatorio']);
            }

            $nomeLimpo = str_replace(' ', '_', preg_replace('/[^A-Za-z0-9 ]/', '', $res['nome']));
            $novoNome = "relatorio_" . $res['id_aluno'] . "_" . $nomeLimpo . "_" . time() . ".pdf";

            if (move_uploaded_file($_FILES['relatorio_file']['tmp_name'], $diretorio . $novoNome)) {
                $stmtCheck = $conexao->prepare("SELECT id_pedido_estagio FROM fase_avaliacao WHERE id_pedido_estagio = ?");
                $stmtCheck->execute([$id_pedido]);
                
                if ($stmtCheck->fetch()) {
                    $conexao->prepare("UPDATE fase_avaliacao SET relatorio = ? WHERE id_pedido_estagio = ?")->execute([$novoNome, $id_pedido]);
                } else {
                    $conexao->prepare("INSERT INTO fase_avaliacao (id_pedido_estagio, relatorio) VALUES (?, ?)")->execute([$id_pedido, $novoNome]);
                }
            }
        }
    } else {
        $nota = $_POST['nota_final'];
        $obs = $_POST['observacoes'];

        $stmtCheck = $conexao->prepare("SELECT id_pedido_estagio FROM fase_avaliacao WHERE id_pedido_estagio = ?");
        $stmtCheck->execute([$id_pedido]);
        
        if ($stmtCheck->fetch()) {
            $conexao->prepare("UPDATE fase_avaliacao SET nota_final = ?, observacoes = ? WHERE id_pedido_estagio = ?")->execute([$nota, $obs, $id_pedido]);
        } else {
            $conexao->prepare("INSERT INTO fase_avaliacao (id_pedido_estagio, nota_final, observacoes) VALUES (?, ?, ?)")->execute([$id_pedido, $nota, $obs]);
        }

        // Marcar o estágio como Concluído
        $conexao->prepare("UPDATE pedido_estagio SET estado_pedido = 'Concluído', fase_atual = 'Concluído', data_ultima_atualizacao = NOW() WHERE id_pedido_estagio = ?")->execute([$id_pedido]);
    }

    $conexao->commit();
    header("Location: avaliacao.php?id_pedido_estagio=$id_pedido&sucesso=1");
    exit();

} catch (Exception $e) {
    if ($conexao->inTransaction()) $conexao->rollBack();
    error_log($e->getMessage());
    die("Erro ao processar avaliação.");
}