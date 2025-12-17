<?php
// administradores/deleteAdministrador.php

include 'modelsAdministradores.php';

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

$admin = getAdministradorById($idAdmin);
if (!$admin) {
    header('Location: index.php');
    exit;
}

deleteAdministradorEUtilizador($idAdmin, !empty($admin['id_utilizador']) ? (int)$admin['id_utilizador'] : null);

header('Location: index.php');
exit;
