<?php
include 'db.php';
include 'utils.php';

function adicionarTurma( $codigo, $nome, $ano_inicio, $ano_fim, $ano_curricular, $curso_id, $professor_id )
{
    $conexao = estabelecerConexao();

    $prepare = $conexao->prepare("INSERT INTO turma 
                    (codigo, nome, ano_inicio, ano_fim, ano_curricular, curso_id, professor_id)
            VALUES (:codigo, :nome, :ano_inicio, :ano_fim, :ano_curricular, :curso_id, :professor_id)");

    $prepare->execute([
        'codigo'         => $codigo,
        'nome'           => $nome,
        'ano_inicio'     => $ano_inicio,
        'ano_fim'        => $ano_fim,
        'ano_curricular' => $ano_curricular,
        'curso_id'       => $curso_id,
        'professor_id'   => $professor_id
    ]);
}


// Receber dados do POST

$codigo         = $_POST['codigo'];
$nome           = $_POST['nome'];
$ano_inicio     = $_POST['ano_inicio'];
$ano_fim        = $_POST['ano_fim'];
$ano_curricular = $_POST['ano_curricular'];

// Curso pode vir por nome, vamos buscar o ID
$cursoDesc  = $_POST['curso_desc'];
$profCodigo = $_POST['professor_codigo'];
$profNome   = $_POST['professor_nome'];

// Estabelecer conexão
$conexao = estabelecerConexao();

// Obter curso_id pelo nome
$stmt = $conexao->prepare("SELECT id_curso FROM curso WHERE curso_desc = ?");
$stmt->execute([$cursoDesc]);
$curso = $stmt->fetch(PDO::FETCH_ASSOC);
$curso_id = $curso['id_curso'] ?? null;

// Obter professor_id (código OU nome)
if (!empty($profCodigo)) {
    $professor_id = $profCodigo;
} else {
    $stmt = $conexao->prepare("SELECT id_professor FROM professor WHERE nome = ?");
    $stmt->execute([$profNome]);
    $prof = $stmt->fetch(PDO::FETCH_ASSOC);
    $professor_id = $prof['id_professor'] ?? null;
}


// Chamar a função para adicionar a turma
adicionarTurma(
    $codigo, 
    $nome, 
    $ano_inicio, 
    $ano_fim, 
    $ano_curricular, 
    $curso_id, 
    $professor_id
);

// Redirecionar de volta à página das turmas
header("Location: index.php");
exit;
