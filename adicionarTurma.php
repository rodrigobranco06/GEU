<?php
session_start();
include 'db.php';
include 'utils.php';

// Proteção básica: só Admins e Professores devem aceder a este script
if (!isset($_SESSION['logged_in']) || !in_array($_SESSION['cargo'], ['Administrador', 'Professor'])) {
    header("Location: login.php");
    exit();
}

function adicionarTurma($codigo, $nome, $ano_inicio, $ano_fim, $ano_curricular, $curso_id, $professor_id)
{
    try {
        $conexao = estabelecerConexao();

        $sql = "INSERT INTO turma 
                (codigo, nome, ano_inicio, ano_fim, ano_curricular, curso_id, professor_id)
                VALUES (:codigo, :nome, :ano_inicio, :ano_fim, :ano_curricular, :curso_id, :professor_id)";
        
        $prepare = $conexao->prepare($sql);

        // Se o professor_id estiver vazio no formulário, passamos null para a DB
        $professor_param = (!empty($professor_id)) ? $professor_id : null;
        $ano_fim_param = (!empty($ano_fim)) ? $ano_fim : null;

        $prepare->execute([
            'codigo'         => $codigo,
            'nome'           => $nome,
            'ano_inicio'     => $ano_inicio,
            'ano_fim'        => $ano_fim_param,
            'ano_curricular' => $ano_curricular,
            'curso_id'       => $curso_id,
            'professor_id'   => $professor_param
        ]);
        
        return true;
    } catch (PDOException $e) {
        error_log("Erro ao adicionar turma: " . $e->getMessage());
        return false;
    }
}

// Captura de dados simplificada (os nomes coincidem com o HTML do index.php)
$codigo         = $_POST['codigo'] ?? null;
$nome           = $_POST['nome'] ?? null;
$ano_inicio     = $_POST['ano_inicio'] ?? null;
$ano_fim        = $_POST['ano_fim'] ?? null;
$ano_curricular = $_POST['ano_curricular'] ?? null;
$curso_id       = $_POST['curso_id'] ?? null; // Corrigido de curso_desc
$professor_id   = $_POST['professor_id'] ?? null; // Corrigido de professor_codigo/nome

if ($codigo && $nome && $ano_inicio && $curso_id) {
    adicionarTurma(
        $codigo, 
        $nome, 
        $ano_inicio, 
        $ano_fim, 
        $ano_curricular, 
        $curso_id, 
        $professor_id
    );
}

header("Location: index.php");
exit;