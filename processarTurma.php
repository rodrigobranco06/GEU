<?php
session_start();
include 'db.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['cargo'] !== 'Administrador') {
    header("Location: login.php");
    exit();
}

$conexao = estabelecerConexao();
$acao = $_POST['acao'] ?? '';
$idTurma = (int)($_POST['id_turma'] ?? 0);

try {
    if ($acao === 'editar' && $idTurma > 0) {
        $stmt = $conexao->prepare("UPDATE turma SET nome = ?, curso_id = ?, ano_curricular = ?, ano_inicio = ?, ano_fim = ?, professor_id = ? WHERE id_turma = ?");
        $stmt->execute([
            $_POST['nome'], 
            $_POST['curso_id'], 
            $_POST['ano_curricular'], 
            $_POST['ano_inicio'], 
            !empty($_POST['ano_fim']) ? $_POST['ano_fim'] : null, 
            !empty($_POST['professor_id']) ? $_POST['professor_id'] : null, 
            $idTurma
        ]);
        header("Location: turma.php?id_turma=$idTurma&status=editado");
    } 
    elseif ($acao === 'eliminar' && $idTurma > 0) {
        $check = $conexao->prepare("SELECT COUNT(*) FROM aluno WHERE turma_id = ?");
        $check->execute([$idTurma]);
        if ($check->fetchColumn() > 0) {
            die("Erro: Não pode eliminar uma turma com alunos.");
        }
        $conexao->prepare("DELETE FROM turma WHERE id_turma = ?")->execute([$idTurma]);
        header("Location: index.php?status=eliminado");
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    die("Erro ao processar pedido.");
}