<?php
session_start();
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['logged_in'])) {
    header("Location: ../index.php");
    exit();
}

$conexao = estabelecerConexao();
$id_pedido = (int)$_POST['id_pedido_estagio'];
$acao = $_POST['acao'] ?? 'guardar_dados';

try {
    $conexao->beginTransaction();

    if ($acao === 'upload_plano') {
        if (isset($_FILES['plano_file']) && $_FILES['plano_file']['error'] === UPLOAD_ERR_OK) {
            
            // 1. Obter dados do aluno e verificar se já existe um plano antigo
            $stmtDados = $conexao->prepare("
                SELECT a.id_aluno, a.nome, fp.plano_estagio 
                FROM pedido_estagio p 
                JOIN aluno a ON p.aluno_id = a.id_aluno 
                LEFT JOIN fase_plano fp ON p.id_pedido_estagio = fp.id_pedido_estagio
                WHERE p.id_pedido_estagio = ?
            ");
            $stmtDados->execute([$id_pedido]);
            $resultado = $stmtDados->fetch(PDO::FETCH_ASSOC);

            if (!$resultado) die("Erro: Dados não encontrados.");

            $diretorio = "../uploads/planos/";
            
            // 2. Se já existir um ficheiro físico, vamos removê-lo para garantir a substituição
            if (!empty($resultado['plano_estagio'])) {
                $ficheiroAntigo = $diretorio . $resultado['plano_estagio'];
                if (file_exists($ficheiroAntigo)) {
                    unlink($ficheiroAntigo); 
                }
            }

            // 3. Gerar novo nome (adicionamos o time() para evitar cache do browser)
            $nomeLimpo = str_replace(' ', '_', preg_replace('/[^A-Za-z0-9 ]/', '', $resultado['nome']));
            $novoNome = "plano_" . $resultado['id_aluno'] . "_" . $nomeLimpo . "_" . time() . ".pdf";

            if (!is_dir($diretorio)) mkdir($diretorio, 0777, true);

            // 4. Mover novo ficheiro
            if (move_uploaded_file($_FILES['plano_file']['tmp_name'], $diretorio . $novoNome)) {
                $stmtCheck = $conexao->prepare("SELECT id_pedido_estagio FROM fase_plano WHERE id_pedido_estagio = ?");
                $stmtCheck->execute([$id_pedido]);
                
                if ($stmtCheck->fetch()) {
                    $conexao->prepare("UPDATE fase_plano SET plano_estagio = ? WHERE id_pedido_estagio = ?")->execute([$novoNome, $id_pedido]);
                } else {
                    $conexao->prepare("INSERT INTO fase_plano (id_pedido_estagio, plano_estagio) VALUES (?, ?)")->execute([$id_pedido, $novoNome]);
                }
            }
        }
    } else {
        // Lógica de guardar datas (permanece igual)
        $data_inicio = $_POST['data_inicio'];
        $data_fim = $_POST['data_fim'];

        $stmtCheck = $conexao->prepare("SELECT id_pedido_estagio FROM fase_plano WHERE id_pedido_estagio = ?");
        $stmtCheck->execute([$id_pedido]);
        
        if ($stmtCheck->fetch()) {
            $conexao->prepare("UPDATE fase_plano SET data_inicio = ?, data_fim = ? WHERE id_pedido_estagio = ?")->execute([$data_inicio, $data_fim, $id_pedido]);
        } else {
            $conexao->prepare("INSERT INTO fase_plano (id_pedido_estagio, data_inicio, data_fim) VALUES (?, ?, ?)")->execute([$id_pedido, $data_inicio, $data_fim]);
        }

        $conexao->prepare("UPDATE pedido_estagio SET fase_atual = 'Avaliação', data_ultima_atualizacao = NOW() WHERE id_pedido_estagio = ?")->execute([$id_pedido]);
    }

    $conexao->commit();
    header("Location: planoEstagio.php?id_pedido_estagio=$id_pedido&sucesso=1");
    exit();

} catch (Exception $e) {
    if ($conexao->inTransaction()) $conexao->rollBack();
    error_log($e->getMessage());
    die("Erro ao processar: " . $e->getMessage());
}