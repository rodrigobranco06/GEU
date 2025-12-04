<?php
// professores/updateProfessor.php

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

$erros = [];

// Ler dados do formulário
$nome             = trim($_POST['nomeProf'] ?? '');
$dataNascimentoDb = $_POST['data_nascimento'] ?? null;
$sexo             = trim($_POST['sexo'] ?? '');
$nif              = trim($_POST['nif'] ?? '');
$numeroCc         = trim($_POST['cc'] ?? '');
$emailInst        = trim($_POST['emailInstitucional'] ?? '');
$emailPessoal     = trim($_POST['emailPessoal'] ?? '');
$morada           = trim($_POST['morada'] ?? '');
$codigoPostal     = trim($_POST['cp'] ?? '');
$cidade           = trim($_POST['cidade'] ?? '');
$novaPassword     = trim($_POST['nova_password'] ?? '');

$nacionalidadeId  = $_POST['nacionalidade_id']  ?? '';
$escolaId         = $_POST['escola_id']         ?? '';
$especializacaoId = $_POST['especializacao_id'] ?? '';

// Validações básicas (podes reforçar se quiseres)
if ($nome === '') {
    $erros[] = 'O nome do professor é obrigatório.';
}
if ($emailInst === '') {
    $erros[] = 'O email institucional é obrigatório.';
}

$dadosUpdate = [
    'nome'               => $nome,
    'data_nascimento'    => $dataNascimentoDb,
    'sexo'               => $sexo,
    'nif'                => $nif,
    'numero_cc'          => $numeroCc,
    'email_institucional'=> $emailInst,
    'email_pessoal'      => $emailPessoal,
    'morada'             => $morada,
    'codigo_postal'      => $codigoPostal,
    'cidade'             => $cidade,
    'nacionalidade_id'   => $nacionalidadeId   !== '' ? (int)$nacionalidadeId   : null,
    'escola_id'          => $escolaId          !== '' ? (int)$escolaId          : null,
    'especializacao_id'  => $especializacaoId  !== '' ? (int)$especializacaoId  : null,
];

if (!empty($erros)) {
    // Voltar ao formulário com erros
    include 'editarProfessor.php';
    exit;
}

updateProfessor($idProfessor, $dadosUpdate);

if ($novaPassword !== '') {
    updatePasswordUtilizador((int)$professor['id_utilizador'], $novaPassword);
}

header('Location: verProfessor.php?id_professor=' . urlencode($idProfessor));
exit;
