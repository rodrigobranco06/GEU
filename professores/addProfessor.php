<?php
// professores/addProfessor.php

include 'modelsProfessores.php';

$erros = [];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: registarProfessor.php');
    exit;
}

// Ler dados do formulário
$codigoProf      = trim($_POST['codigoProf'] ?? '');
$password        = trim($_POST['password'] ?? '');
$nome            = trim($_POST['nomeProf'] ?? '');
$dataNascimento  = $_POST['data_nascimento'] ?? null;
$sexo            = trim($_POST['sexo'] ?? '');
$nacionalidadeId = $_POST['nacionalidade_id'] ?? '';
$especializacaoId= $_POST['especializacao_id'] ?? '';
$nif             = trim($_POST['nif'] ?? '');
$numeroCc        = trim($_POST['cc'] ?? '');
$escolaId        = $_POST['escola_id'] ?? '';
$emailInst       = trim($_POST['emailInstitucional'] ?? '');
$emailPessoal    = trim($_POST['emailPessoal'] ?? '');
$morada          = trim($_POST['morada'] ?? '');
$codigoPostal    = trim($_POST['cp'] ?? '');
$cidade          = trim($_POST['cidade'] ?? '');

// Validações básicas
if ($codigoProf === '' || !ctype_digit($codigoProf)) {
    $erros[] = 'O código do professor é obrigatório e deve ser numérico.';
}

if ($nome === '') {
    $erros[] = 'O nome do professor é obrigatório.';
}

if (empty($dataNascimento)) {
    $erros[] = 'A data de nascimento é obrigatória.';
}

if ($emailInst === '') {
    $erros[] = 'O email institucional é obrigatório.';
}

if ($password === '') {
    $erros[] = 'A password é obrigatória.';
}

if ($codigoProf !== '' && ctype_digit($codigoProf)) {
    if (professorIdExiste((int)$codigoProf)) {
        $erros[] = 'Já existe um professor com esse código/ID.';
    }
}

if ($emailInst !== '') {
    if (verificarEmailExisteProfessor($emailInst)) {
        $erros[] = 'O email institucional já está registado no sistema.';
    }
}

if (!empty($erros)) {
    include 'registarProfessor.php';
    exit;
}

try {
    $conexao = estabelecerConexao();
    $conexao->beginTransaction();

    $username = $emailInst;

    $idUtilizador = criarUtilizadorProfessor($username, $password);

    $dadosProfessor = [
        'id_professor'        => (int)$codigoProf,
        'nome'                => $nome,
        'data_nascimento'     => $dataNascimento, 
        'sexo'                => $sexo,
        'nif'                 => $nif,
        'numero_cc'           => $numeroCc,
        'email_institucional' => $emailInst,
        'email_pessoal'       => $emailPessoal,
        'morada'              => $morada,
        'codigo_postal'       => $codigoPostal,
        'cidade'              => $cidade,
        'nacionalidade_id'    => $nacionalidadeId !== '' ? (int)$nacionalidadeId : null,
        'escola_id'           => $escolaId        !== '' ? (int)$escolaId        : null,
        'especializacao_id'   => $especializacaoId!== '' ? (int)$especializacaoId: null,
    ];

    criarProfessor($dadosProfessor, $idUtilizador);

    $conexao->commit();

    header('Location: index.php');
    exit;

} catch (PDOException $e) {
    if (isset($conexao) && $conexao->inTransaction()) {
        $conexao->rollBack();
    }
    $erros[] = 'Erro ao criar professor: ' . $e->getMessage();
    include 'registarProfessor.php';
    exit;
}
