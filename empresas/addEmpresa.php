<?php
// empresas/addEmpresa.php

include 'modelsEmpresas.php';

$erros = [];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: registarEmpresa.php');
    exit;
}

// Ler dados
$nome       = trim($_POST['nome'] ?? '');
$password   = trim($_POST['password'] ?? '');
$email      = trim($_POST['email'] ?? ''); // Email será o username
$nif        = trim($_POST['nif'] ?? '');
$telefone   = trim($_POST['telefone'] ?? '');
$ramoId     = $_POST['ramo_id'] ?? '';
$paisId     = $_POST['pais_id'] ?? '';
$morada     = trim($_POST['morada'] ?? '');
$cp         = trim($_POST['cp'] ?? '');
$cidade     = trim($_POST['cidade'] ?? '');
$website    = trim($_POST['website'] ?? '');
$linkedin   = trim($_POST['linkedin'] ?? '');

// Dados do Responsável
$nomeResp   = trim($_POST['nome_responsavel'] ?? '');
$cargoResp  = trim($_POST['cargo_responsavel'] ?? '');
$emailResp  = trim($_POST['email_responsavel'] ?? '');
$telResp    = trim($_POST['telefone_responsavel'] ?? '');

// Validações Básicas
if ($nome === '') $erros[] = 'O nome da empresa é obrigatório.';
if ($email === '') $erros[] = 'O email da empresa é obrigatório.';
if ($password === '') $erros[] = 'A password é obrigatória.';
if ($nif === '') $erros[] = 'O NIF é obrigatório.';

if (!empty($erros)) {
    include 'registarEmpresa.php';
    exit;
}

try {
    $conexao = estabelecerConexao();
    $conexao->beginTransaction();

    // 1. Criar Utilizador
    $idUtilizador = criarUtilizadorEmpresa($email, $password);

    // 2. Criar Empresa
    $dadosEmpresa = [
        'nome' => $nome,
        'nif' => $nif,
        'email' => $email,
        'telefone' => $telefone,
        'morada' => $morada,
        'codigo_postal' => $cp,
        'cidade' => $cidade,
        'website' => $website,
        'linkedin' => $linkedin,
        'nome_responsavel' => $nomeResp,
        'cargo_responsavel' => $cargoResp,
        'email_responsavel' => $emailResp,
        'telefone_responsavel' => $telResp,
        'ramo_atividade_id' => ($ramoId !== '' ? (int)$ramoId : null),
        'pais_id' => ($paisId !== '' ? (int)$paisId : null),
    ];

    criarEmpresa($dadosEmpresa, $idUtilizador);

    $conexao->commit();
    header('Location: index.php');
    exit;

} catch (Exception $e) {
    if (isset($conexao) && $conexao->inTransaction()) {
        $conexao->rollBack();
    }
    $erros[] = 'Erro ao registar: ' . $e->getMessage();
    include 'registarEmpresa.php';
    exit;
}
?>