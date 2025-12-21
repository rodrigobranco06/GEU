<?php
// administradores/updateAdministrador.php

include 'modelsAdministradores.php';

$erros = [];

$idAdmin = 0;
if (isset($_GET['id_admin']) && ctype_digit($_GET['id_admin'])) {
    $idAdmin = (int) $_GET['id_admin'];
} elseif (isset($_POST['id_admin']) && ctype_digit($_POST['id_admin'])) {
    $idAdmin = (int) $_POST['id_admin'];
}

if ($idAdmin <= 0) {
    header('Location: index.php');
    exit;
}

$adminAtual = getAdministradorById($idAdmin);
if (!$adminAtual) {
    header('Location: index.php');
    exit;
}

$nome               = trim($_POST['nome'] ?? '');
$emailInstitucional = trim($_POST['emailInstitucional'] ?? '');
$emailPessoal       = trim($_POST['emailPessoal'] ?? '');
$novaPassword       = trim($_POST['nova_password'] ?? '');

if ($nome === '') $erros[] = 'O nome é obrigatório.';
if ($emailInstitucional === '') $erros[] = 'O email institucional é obrigatório.';

if (!empty($erros)) {
    $id_admin = $idAdmin;
    include 'editarAdministrador.php';
    exit;
}

try {
    $con = estabelecerConexao();
    $con->beginTransaction();

    updateAdministrador($idAdmin, [
        'nome' => $nome,
        'email_institucional' => $emailInstitucional,
        'email_pessoal' => $emailPessoal,
    ]);

    if (!empty($adminAtual['id_utilizador'])) {
        updatePasswordUtilizadorAdministrador((int)$adminAtual['id_utilizador'], $novaPassword);
    }

    $con->commit();

    header('Location: verAdministrador.php?id_admin=' . $idAdmin);
    exit;

} catch (PDOException $e) {
    if (isset($con) && $con->inTransaction()) $con->rollBack();
    $erros[] = 'Erro ao atualizar administrador: ' . $e->getMessage();
    include 'editarAdministrador.php';
    exit;
}
