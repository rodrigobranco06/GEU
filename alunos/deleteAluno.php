<?php
// alunos/deleteAluno.php

include 'modelsAlunos.php';

if (!isset($_GET['id_aluno']) || !ctype_digit($_GET['id_aluno'])) {
    die('ID de aluno inválido.');
}

$idAluno = (int) $_GET['id_aluno'];

// ir buscar aluno para saber o utilizador associado
$aluno = getAlunoById($idAluno);

if (!$aluno) {
    die('Aluno não encontrado.');
}

// pode não ter utilizador associado (segurança)
$idUtilizador = !empty($aluno['utilizador_id'])
    ? (int)$aluno['utilizador_id']
    : null;

try {
    $con = estabelecerConexao();
    $con->beginTransaction();

    // apagar aluno
    $stmt = $con->prepare('DELETE FROM aluno WHERE id_aluno = :id');
    $stmt->execute(['id' => $idAluno]);

    // apagar utilizador (se existir)
    if ($idUtilizador) {
        $stmt = $con->prepare('DELETE FROM utilizador WHERE id_utilizador = :id');
        $stmt->execute(['id' => $idUtilizador]);
    }

    $con->commit();

    // voltar à listagem
    header('Location: index.php');
    exit;

} catch (PDOException $e) {
    if (isset($con) && $con->inTransaction()) {
        $con->rollBack();
    }

    die('Erro ao eliminar aluno: ' . $e->getMessage());
}
