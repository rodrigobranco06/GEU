<?php
// modelsTurma.php

function getDadosTurma(PDO $conexao, $idTurma, $cargo, $idUtilizador) {
    $sql = "SELECT t.*, c.curso_desc FROM turma t 
             LEFT JOIN curso c ON t.curso_id = c.id_curso 
             WHERE t.id_turma = :id";

    if ($cargo === 'Professor') {
        $stmtProf = $conexao->prepare("SELECT id_professor FROM professor WHERE utilizador_id = ?");
        $stmtProf->execute([$idUtilizador]);
        $prof = $stmtProf->fetch();
        $sql .= " AND t.professor_id = " . ($prof['id_professor'] ?? 0);
    }

    $stmt = $conexao->prepare($sql);
    $stmt->execute([':id' => $idTurma]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getAlunosTurma(PDO $conexao, $idTurma, $cargo, $idUtilizador) {
    $sql = "SELECT DISTINCT a.id_aluno, a.nome, 
              (SELECT pe.estado_pedido FROM pedido_estagio pe 
               WHERE pe.aluno_id = a.id_aluno 
               ORDER BY pe.data_criacao DESC LIMIT 1) as estado_pedido 
            FROM aluno a";

    $params = [':id_turma' => $idTurma];

    if ($cargo === 'Administrador' || $cargo === 'Professor') {
        $sql .= " WHERE a.turma_id = :id_turma";
    } 
    elseif ($cargo === 'Empresa') {
        $sql .= " JOIN pedido_estagio pe ON a.id_aluno = pe.aluno_id
                  JOIN empresa e ON pe.empresa_id = e.id_empresa 
                  WHERE a.turma_id = :id_turma AND e.utilizador_id = :id_u";
        $params[':id_u'] = $idUtilizador;
    } 
    elseif ($cargo === 'Aluno') {
        $sql .= " WHERE a.turma_id = :id_turma AND a.utilizador_id = :id_u";
        $params[':id_u'] = $idUtilizador;
    }

    $sql .= " ORDER BY a.nome ASC";
    $stmt = $conexao->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getPerfilUtilizador(PDO $conexao, $idUtilizador, $cargo) {
    $tabela = strtolower($cargo);
    $campoEmail = ($cargo === 'Empresa') ? 'email' : 'email_institucional';
    
    $stmt = $conexao->prepare("SELECT nome, $campoEmail as email FROM $tabela WHERE utilizador_id = ?");
    $stmt->execute([$idUtilizador]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}