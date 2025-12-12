<?php
// empresas/updateEmpresa.php

include 'modelsEmpresas.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$idEmpresa = (int)($_GET['id_empresa'] ?? $_POST['id_empresa'] ?? 0);
$empresa = getEmpresaById($idEmpresa);

if (!$empresa) die('Empresa não encontrada.');

// Ler dados
$nome       = trim($_POST['nome'] ?? '');
$nif        = trim($_POST['nif'] ?? '');
$email      = trim($_POST['email'] ?? '');
$telefone   = trim($_POST['telefone'] ?? '');
$ramoId     = $_POST['ramo_id'] ?? '';
$paisId     = $_POST['pais_id'] ?? '';
$morada     = trim($_POST['morada'] ?? '');
$cp         = trim($_POST['cp'] ?? '');
$cidade     = trim($_POST['cidade'] ?? '');
$website    = trim($_POST['website'] ?? '');
$linkedin   = trim($_POST['linkedin'] ?? '');

$nomeResp   = trim($_POST['nome_responsavel'] ?? '');
$cargoResp  = trim($_POST['cargo_responsavel'] ?? '');
$emailResp  = trim($_POST['email_responsavel'] ?? '');
$telResp    = trim($_POST['telefone_responsavel'] ?? '');

$novaPass   = trim($_POST['nova_password'] ?? '');

if ($nome === '') die('Nome obrigatório');

$dadosUpdate = [
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

updateEmpresa($idEmpresa, $dadosUpdate);

if ($novaPass !== '' && !empty($empresa['utilizador_id'])) {
    updatePasswordUtilizador((int)$empresa['utilizador_id'], $novaPass);
}

header('Location: verEmpresa.php?id_empresa=' . $idEmpresa);
exit;
?>