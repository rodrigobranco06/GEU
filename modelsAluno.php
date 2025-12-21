<?php
// modelsAluno.php


function verificarAcessoAluno(PDO $conexao, $idAluno, $idUtilizador) {
    $stmt = $conexao->prepare("SELECT id_aluno FROM aluno WHERE id_aluno = ? AND utilizador_id = ?");
    $stmt->execute([$idAluno, $idUtilizador]);
    return $stmt->fetch() ? true : false;
}


function getDadosAluno(PDO $conexao, $idAluno) {
    $sql = "SELECT nome, email_institucional, turma_id, 
            SUBSTRING_INDEX(email_institucional, '@', 1) as numero_aluno 
            FROM aluno WHERE id_aluno = :id";
    $stmt = $conexao->prepare($sql);
    $stmt->execute([':id' => $idAluno]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}


function getPedidosEstagio(PDO $conexao, $idAluno, $cargo, $idUtilizador) {
    $sql = "SELECT p.* FROM pedido_estagio p";
    $params = [':id' => $idAluno];

    if ($cargo === 'Empresa') {
        $sql .= " INNER JOIN empresa e ON p.empresa_id = e.id_empresa 
                  WHERE p.aluno_id = :id AND e.utilizador_id = :id_u";
        $params[':id_u'] = $idUtilizador;
    } else {
        $sql .= " WHERE p.aluno_id = :id";
    }

    $sql .= " ORDER BY p.data_criacao DESC";
    $stmt = $conexao->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


function getPerfilLogado(PDO $conexao, $idUtilizador, $cargo) {
    $tabela = strtolower($cargo);
    $campoEmail = ($cargo === 'Empresa') ? 'email' : 'email_institucional';
    
    $stmt = $conexao->prepare("SELECT nome, $campoEmail as email FROM $tabela WHERE utilizador_id = ?");
    $stmt->execute([$idUtilizador]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}