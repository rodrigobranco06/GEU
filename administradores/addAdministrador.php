<?php
// administradores/addAdministrador.php

session_start();

include 'modelsAdministradores.php';

$erros = [];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: registarAdministrador.php');
    exit;
}

// Ler dados do formulário
$password           = trim($_POST['password'] ?? '');
$nome               = trim($_POST['nome'] ?? '');
$emailInstitucional = trim($_POST['emailInstitucional'] ?? '');

// Validações
if ($password === '') {
    $erros[] = 'A password é obrigatória.';
}

if ($nome === '') {
    $erros[] = 'O nome é obrigatório.';
}

if ($emailInstitucional === '') {
    $erros[] = 'O email institucional é obrigatório.';
}

if ($emailInstitucional !== '') {
    if (verificarEmailExisteAdmin($emailInstitucional)) {
        $erros[] = 'Este email já está registado no sistema.';
    }
}

// Se houver erros -> guardar e redirecionar
if (!empty($erros)) {
    $_SESSION['erros_admin'] = $erros;
    $_SESSION['old_admin'] = [
        'nome'               => $nome,
        'emailInstitucional' => $emailInstitucional,
    ];

    header('Location: registarAdministrador.php');
    exit;
}

try {
    $con = estabelecerConexao();
    $con->beginTransaction();

    // username = email institucional
    $username = $emailInstitucional;

    $utilizadorId = criarUtilizadorAdministrador($username, $password);

    // id_admin é AUTO_INCREMENT -> não enviar id_admin
    $idAdminNovo = criarAdministrador([
        'nome'                => $nome,
        'email_institucional' => $emailInstitucional,
    ], $utilizadorId);

    $con->commit();

    header('Location: index.php');
    exit;

} catch (PDOException $e) {
    if (isset($con) && $con->inTransaction()) {
        $con->rollBack();
    }

    $_SESSION['erros_admin'] = ['Erro ao criar administrador: ' . $e->getMessage()];
    $_SESSION['old_admin'] = [
        'nome'               => $nome,
        'emailInstitucional' => $emailInstitucional,
    ];

    header('Location: registarAdministrador.php');
    exit;
}
