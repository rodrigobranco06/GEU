<?php
// empresas/deleteEmpresa.php
include 'modelsEmpresas.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$idEmpresa = (int)($_GET['id_empresa'] ?? $_POST['id_empresa'] ?? 0);
$empresa = getEmpresaById($idEmpresa);

if (!$empresa) die('Empresa não encontrada.');

if (empresaTemEstagios($idEmpresa)) {
    $_SESSION['erro_sistema'] = "Não é possível eliminar a empresa '{$empresa['nome']}' pois tem histórico de estágios associados.";
    
    header("Location: editarEmpresa.php?id_empresa=" . $idEmpresa);
    exit; 
}

$idUtilizador = isset($empresa['utilizador_id']) ? (int)$empresa['utilizador_id'] : null;

deleteEmpresaEUtilizador($idEmpresa, $idUtilizador);

header('Location: index.php');
exit;
?>