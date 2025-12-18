<?php
session_start();
require_once 'db.php';

// Bloqueia acesso direto via URL
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.php");
    exit();
}

$pdo = estabelecerConexao();

$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    header("Location: login.php?erro=3");
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT id_utilizador, password_hash, tipo_utilizador, estado_conta FROM utilizador WHERE username = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password_hash'])) {
        
        if ($user['estado_conta'] !== 'Ativo') {
            header("Location: login.php?erro=2");
            exit();
        }

        // Sucesso: Guardar dados na Sessão
        $_SESSION['id_utilizador'] = $user['id_utilizador'];
        $_SESSION['cargo'] = $user['tipo_utilizador']; // Aluno, Empresa, Professor, Administrador
        $_SESSION['logged_in'] = true;

        // REDIRECIONAMENTO ÚNICO: Todos para a página principal
        header("Location: index.php");
        exit();

    } else {
        // Credenciais erradas
        header("Location: login.php?erro=1");
        exit();
    }

} catch (PDOException $e) {
    error_log($e->getMessage());
    header("Location: login.php?erro=1");
    exit();
}