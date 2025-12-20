<?php
// modelsPerfil.php

function getDadoscompletosPerfil(PDO $conexao, $idUtilizador, $cargo) {
    if ($cargo === 'Aluno') {
        $sql = "SELECT a.*, c.curso_desc, esc.escola_desc, prof.nome as professor_nome,
                (SELECT pe.estado_pedido FROM pedido_estagio pe WHERE pe.aluno_id = a.id_aluno ORDER BY pe.data_criacao DESC LIMIT 1) as estado_estagio
                FROM aluno a
                LEFT JOIN curso c ON a.curso_id = c.id_curso
                LEFT JOIN escola esc ON a.escola_id = esc.id_escola
                LEFT JOIN professor prof ON a.turma_id IN (SELECT id_turma FROM turma WHERE professor_id = prof.id_professor)
                WHERE a.utilizador_id = ?";
    } elseif ($cargo === 'Professor') {
        $sql = "SELECT p.*, esc.escola_desc, esp.especializacao_desc 
                FROM professor p 
                LEFT JOIN escola esc ON p.escola_id = esc.id_escola
                LEFT JOIN especializacao esp ON p.especializacao_id = esp.id_especializacao
                WHERE p.utilizador_id = ?";
    } elseif ($cargo === 'Empresa') {
        $sql = "SELECT e.*, r.ramo_atividade_desc 
                FROM empresa e 
                LEFT JOIN ramo_atividade r ON e.ramo_atividade_id = r.id_ramo_atividade
                WHERE e.utilizador_id = ?";
    } else { // Administrador
        $sql = "SELECT * FROM administrador WHERE utilizador_id = ?";
    }

    $stmt = $conexao->prepare($sql);
    $stmt->execute([$idUtilizador]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}