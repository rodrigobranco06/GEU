<?php
// alunos/fetchAlunos.php

include 'modelsAlunos.php';

// Cabeçalho JSON
header('Content-Type: application/json');

// Termo de pesquisa vindo por GET (?search=...)
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';

// Vai buscar alunos filtrados
$alunos = getAlunos($search_term);

// Devolve JSON
echo json_encode($alunos);
