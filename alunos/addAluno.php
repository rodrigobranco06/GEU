<?php
// alunos/addAluno.php

include 'modelsAlunos.php';

$erros = [];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: registarAluno.php');
    exit;
}

// Ler dados do formulário
$codigoAluno       = trim($_POST['codigoAluno'] ?? '');
$password          = trim($_POST['password'] ?? '');
$nome              = trim($_POST['nomeAluno'] ?? '');
$dataNascimento    = $_POST['data_nascimento'] ?? null;
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

// Upload do CV (opcional)
$cvPath = null;
if (!empty($_FILES['cv']['name'])) {
    $uploadDir  = __DIR__ . '/../uploads/cv/';
    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0777, true);
    }

    $nomeFicheiro = basename($_FILES['cv']['name']);
    $ext          = strtolower(pathinfo($nomeFicheiro, PATHINFO_EXTENSION));

    $permitidas = ['pdf','doc','docx'];

    if (!in_array($ext, $permitidas)) {
        $erros[] = 'O CV tem de ser um ficheiro PDF, DOC ou DOCX.';
    } else {
        $novoNome = 'cv_' . time() . '_' . preg_replace('/[^a-z0-9_\.-]/i', '_', $nomeFicheiro);
        $destino  = $uploadDir . $novoNome;

        if (move_uploaded_file($_FILES['cv']['tmp_name'], $destino)) {
            $cvPath = 'uploads/cv/' . $novoNome;
        } else {
            $erros[] = 'Falha ao fazer upload do CV.';
        }
    }
}

// Validações básicas
if ($codigoAluno === '' || !ctype_digit($codigoAluno)) {
    $erros[] = 'O código do aluno é obrigatório e deve ser numérico.';
}

if ($nome === '') {
    $erros[] = 'O nome do aluno é obrigatório.';
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

// Verificar se o id_aluno existe
if ($codigoAluno !== '' && ctype_digit($codigoAluno)) {
    if (alunoIdExiste((int)$codigoAluno)) {
        $erros[] = 'Já existe um aluno com esse código/ID.';
    }
}

if (!empty($erros)) {
    include 'registarAluno.php';
    exit;
}

try {
    $conexao = estabelecerConexao();
    $conexao->beginTransaction();

    // Vamos usar o email institucional como username
    $username = $emailInst;

    $idUtilizador = criarUtilizadorAluno($username, $password);

    $dadosAluno = [
        'id_aluno'           => (int)$codigoAluno,
        'nome'               => $nome,
        'data_nascimento'    => $dataNascimento, 
        'sexo'               => $sexo,
        'nif'                => $nif,
        'numero_cc'          => $numeroCc,
        'email_institucional'=> $emailInst,
        'email_pessoal'      => $emailPessoal,
        'morada'             => $morada,
        'codigo_postal'      => $codigoPostal,
        'cidade'             => $cidade,
        'situacao_academica' => $situacaoAcademica,
        'cv'                 => $cvPath,
        'linkedin'           => $linkedin,
        'github'             => $github,
        'nacionalidade_id'   => $nacionalidadeId !== '' ? (int)$nacionalidadeId : null,
        'curso_id'           => $cursoId         !== '' ? (int)$cursoId         : null,
        'escola_id'          => $escolaId        !== '' ? (int)$escolaId        : null,
        'turma_id'           => $turmaId         !== '' ? (int)$turmaId         : null,
    ];

    criarAluno($dadosAluno, $idUtilizador);

    $conexao->commit();

    header('Location: index.php');
    exit;

} catch (PDOException $e) {
    if (isset($conexao) && $conexao->inTransaction()) {
        $conexao->rollBack();
    }
    $erros[] = 'Erro ao criar aluno: ' . $e->getMessage();
    include 'registarAluno.php';
    exit;
}
