<?php
session_start();
include 'db.php';

if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit();
}

$conexao = estabelecerConexao();
$cargo = $_SESSION['cargo'];
$idUser = $_SESSION['id_utilizador'];

$acao = $_POST['acao'] ?? '';
$github = $_POST['github'] ?? null;
$linkedin = $_POST['linkedin'] ?? null;

if (!in_array($cargo, ['Aluno', 'Empresa'])) {
    header("Location: verPerfil.php");
    exit();
}

try {
    $tabela = strtolower($cargo);
    
    if ($acao === 'update_github') {
        $stmt = $conexao->prepare("UPDATE $tabela SET github = ? WHERE utilizador_id = ?");
        $stmt->execute([$github, $idUser]);
        $sucesso = 'github';
    } 
    elseif ($acao === 'update_linkedin') {
        $stmt = $conexao->prepare("UPDATE $tabela SET linkedin = ? WHERE utilizador_id = ?");
        $stmt->execute([$linkedin, $idUser]);
        $sucesso = 'linkedin';
    }

    header("Location: verPerfil.php?sucesso=$sucesso");
    exit();

} catch (PDOException $e) {
    error_log($e->getMessage());
    die("Erro ao atualizar o perfil. Por favor, tente novamente.");
}