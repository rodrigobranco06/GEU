<?php

function estabelecerConexao()
{
    if ($_SERVER['SERVER_NAME'] === 'localhost') {
        // Ambiente de desenvolvimento
        $dbname = 'geu';
        $hostname = 'localhost';
        $username = 'root';
        $pass = '';
        $port = 3307;
    } else {
        // Ambiente de produção (Hostinger)
        $dbname = 'u506280443_rodtomDB';
        $hostname = 'localhost';
        $username = 'u506280443_rodtomdbUser';
        $pass = '5eb~4!f;D';
        $port = 3306;
    }

    $dsn = "mysql:host=$hostname;dbname=$dbname;port=$port;charset=utf8mb4";

    try {
        return new PDO($dsn, $username, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
    } catch (PDOException $e) {
        die("Erro de conexão: " . $e->getMessage());
    }
}
