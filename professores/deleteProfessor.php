<?php
// professores/deleteProfessor.php

include 'modelsProfessores.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$idProfessor = (int) ($_GET['id_professor'] ?? $_POST['id_professor'] ?? 0);
if ($idProfessor <= 0) {
    die('ID de professor inválido.');
}

$professor = getProfessorById($idProfessor);
if (!$professor) {
    die('Professor não encontrado.');
}

$idUtilizador = isset($professor['id_utilizador']) ? (int)$professor['id_utilizador'] : null;

deleteProfessorEUtilizador($idProfessor, $idUtilizador);

header('Location: index.php');
exit;
