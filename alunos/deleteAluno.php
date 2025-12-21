<?php
// alunos/deleteAluno.php

include 'modelsAlunos.php';

if (!isset($_GET['id_aluno']) || !ctype_digit($_GET['id_aluno'])) {
    die('ID de aluno inválido.');
}

$idAluno = (int) $_GET['id_aluno'];

$aluno = getAlunoById($idAluno);

if (!$aluno) {
    die('Aluno não encontrado.');
}

$idUtilizador = !empty($aluno['utilizador_id'])
    ? (int)$aluno['utilizador_id']
    : null;

try {
    $con = estabelecerConexao();
    $con->beginTransaction();

    $stmt = $con->prepare('DELETE FROM aluno WHERE id_aluno = :id');
    $stmt->execute(['id' => $idAluno]);

    if ($idUtilizador) {
        $stmt = $con->prepare('DELETE FROM utilizador WHERE id_utilizador = :id');
        $stmt->execute(['id' => $idUtilizador]);
    }

    $con->commit();

    header('Location: index.php');
    exit;

} catch (PDOException $e) {
    if (isset($con) && $con->inTransaction()) {
        $con->rollBack();
    }

    die('Erro ao eliminar aluno: ' . $e->getMessage());
}
