<?php
// alunos/fetchAlunos.php

include 'modelsAlunos.php';

header('Content-Type: application/json; charset=utf-8');

$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';

$alunos = getAlunos($search_term);

echo json_encode($alunos);
exit;
