<?php

function estabelecerConexao()
{
   // Devia mais tarde ser passado para um ficheiro de configuração
   $dbname = 'geu';
   $hostname = 'localhost';
   $username = 'root';
   $pass = '';

   $dsn = "mysql:host=$hostname;dbname=$dbname;port=3307;charset=utf8mb4";

   try {
      $conexao = new PDO( $dsn, $username, $pass ); 
   }
   catch ( PDOException $e ) {
      $e->getMessage();         // a dot notation no PHP é diferente '->'
   }

   return $conexao;
}

?>