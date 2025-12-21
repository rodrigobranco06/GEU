<?php
// alunos/updateAluno.php

include 'modelsAlunos.php';

$erros = [];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$idAluno = isset($_POST['id_aluno']) && ctype_digit($_POST['id_aluno']) ? (int)$_POST['id_aluno'] : 0;
if ($idAluno <= 0) {
    header('Location: index.php');
    exit;
}

$alunoAtual = getAlunoById($idAluno);
if (!$alunoAtual) {
    header('Location: index.php');
    exit;
}

// Ler dados do formulário
$novaPassword      = trim($_POST['novaPassword'] ?? '');
$nome              = trim($_POST['nomeAluno'] ?? '');
$dataNascimento    = $_POST['data_nascimento'] ?? '';
$sexo              = trim($_POST['sexo'] ?? '');
$nacionalidadeId   = $_POST['nacionalidade_id'] ?? '';
$nif               = trim($_POST['nif'] ?? '');
$numeroCc          = trim($_POST['cc'] ?? '');
$cursoId           = $_POST['curso_id'] ?? '';
$turmaId           = $_POST['turma_id'] ?? '';
$situacaoAcademica = trim($_POST['situacao_academica'] ?? '');
$escolaId          = $_POST['escola_id'] ?? '';
$emailInst         = trim($_POST['emailInstitucional'] ?? '');
$emailPessoal      = trim($_POST['emailPessoal'] ?? '');
$morada            = trim($_POST['morada'] ?? '');
$codigoPostal      = trim($_POST['cp'] ?? '');
$cidade            = trim($_POST['cidade'] ?? '');
$linkedin          = trim($_POST['linkedin'] ?? '');
$github            = trim($_POST['github'] ?? '');

if ($nome === '') {
    $erros[] = 'O nome do aluno é obrigatório.';
}
if ($emailInst === '') {
    $erros[] = 'O email institucional é obrigatório.';
}

$cvPath = $alunoAtual['cv'] ?? null;

if (!empty($_FILES['cv']['name'])) {
    $uploadDir = __DIR__ . '/../uploads/cv/';
    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0777, true);
    }

    $ext = strtolower(pathinfo($_FILES['cv']['name'], PATHINFO_EXTENSION));
    $permitidas = ['pdf','doc','docx'];

    if (!in_array($ext, $permitidas, true)) {
        $erros[] = 'O CV tem de ser um ficheiro PDF, DOC ou DOCX.';
    } else {
        $nomeLimpo = $nome !== '' ? $nome : ($alunoAtual['nome'] ?? '');
        $nomeLimpo = iconv('UTF-8', 'ASCII//TRANSLIT', $nomeLimpo);
        $nomeLimpo = preg_replace('/[^a-zA-Z0-9]/', '_', $nomeLimpo);
        $nomeLimpo = preg_replace('/_+/', '_', $nomeLimpo);
        $nomeLimpo = trim($nomeLimpo, '_');

        $novoNome = 'cv_' . $idAluno . '_' . strtolower($nomeLimpo) . '.' . $ext;
        $destino  = $uploadDir . $novoNome;

        if (move_uploaded_file($_FILES['cv']['tmp_name'], $destino)) {
            $cvPath = 'uploads/cv/' . $novoNome; 
        } else {
            $erros[] = 'Falha ao fazer upload do CV.';
        }
    }
}

if (!empty($erros)) {
    include 'editarAluno.php';
    exit;
}

try {
    $con = estabelecerConexao();
    $con->beginTransaction();

    if ($novaPassword !== '') {
        updatePasswordUtilizadorAluno((int)$alunoAtual['id_utilizador'], $novaPassword);
    }

    $dados = [
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
        'situacao_academica'  => $situacaoAcademica,
        'cv'                  => $cvPath, 
        'linkedin'            => $linkedin,
        'github'              => $github,
        'nacionalidade_id'    => $nacionalidadeId !== '' ? (int)$nacionalidadeId : null,
        'curso_id'            => $cursoId         !== '' ? (int)$cursoId         : null,
        'escola_id'           => $escolaId        !== '' ? (int)$escolaId        : null,
        'turma_id'            => $turmaId         !== '' ? (int)$turmaId         : null,
    ];

    updateAluno($idAluno, $dados);

    $con->commit();

    header('Location: verAluno.php?id_aluno=' . $idAluno);
    exit;

} catch (PDOException $e) {
    if (isset($con) && $con->inTransaction()) {
        $con->rollBack();
    }
    $erros[] = 'Erro ao atualizar aluno: ' . $e->getMessage();
    include 'editarAluno.php';
    exit;
}
