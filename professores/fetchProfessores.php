<?php
// professores/fetchProfessores.php

// Caminho de include ajustado para a estrutura do projeto
include '../db.php'; 

// Assume que a função estabelecerConexao() está definida em db.php
// e retorna um objeto PDO de conexão.

/**
 * Obtém a lista de professores, opcionalmente filtrada por um termo de pesquisa.
 * Utiliza Prepared Statements para segurança (SQL Injection).
 * @param string $searchTerm Termo a procurar no nome, email ou especialização.
 * @return array Lista de professores.
 */
function getProfessores($searchTerm = '')
{
    // Chama a função definida em db.php
    $conexao = estabelecerConexao();

    // Consulta base
    $sql = 'SELECT 
            p.id_professor,
            p.nome AS nome_professor,
            p.email_institucional AS email_institucional,
            e.especializacao_desc
        FROM professor p
        LEFT JOIN especializacao e ON e.id_especializacao = p.especializacao_id';

    $params = [];

    if (!empty($searchTerm)) {
        // Adiciona a cláusula WHERE para filtrar
        $sql .= ' WHERE p.nome LIKE :searchTerm 
                  OR p.email_institucional LIKE :searchTerm 
                  OR e.especializacao_desc LIKE :searchTerm';
        
        // O valor do parâmetro deve incluir '%' para pesquisa de substring
        $params[':searchTerm'] = '%' . $searchTerm . '%';
    }
    
    // Preparar e executar o statement
    $stmt = $conexao->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ----------------------------------------------------
// Lógica principal
// ----------------------------------------------------

// Define o cabeçalho para retornar JSON
header('Content-Type: application/json');

// Obtém o termo de pesquisa da requisição GET
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';

// Obtém os professores filtrados
$professores = getProfessores($search_term);

// Retorna o resultado como JSON
echo json_encode($professores);

?>