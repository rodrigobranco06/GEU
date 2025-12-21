<?php
// professores/fetchProfessores.php

include '../db.php'; 


function getProfessores($searchTerm = '')
{
    $conexao = estabelecerConexao();

    $sql = 'SELECT 
            p.id_professor,
            p.nome AS nome_professor,
            p.email_institucional AS email_institucional,
            e.especializacao_desc
        FROM professor p
        LEFT JOIN especializacao e ON e.id_especializacao = p.especializacao_id';

    $params = [];

    if (!empty($searchTerm)) {
        $sql .= ' WHERE p.nome LIKE :searchTerm 
                  OR p.email_institucional LIKE :searchTerm 
                  OR e.especializacao_desc LIKE :searchTerm';
        
        $params[':searchTerm'] = '%' . $searchTerm . '%';
    }
    
    $stmt = $conexao->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


header('Content-Type: application/json');

$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';

$professores = getProfessores($search_term);

echo json_encode($professores);

?>