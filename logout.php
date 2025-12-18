<?php
session_start();
session_unset(); // Limpa as variáveis
session_destroy(); // Destrói a sessão
header("Location: login.php");
exit();
?>