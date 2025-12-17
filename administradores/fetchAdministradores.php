<?php
// administradores/fetchAdministradores.php

include 'modelsAdministradores.php';

header('Content-Type: application/json');

$term = isset($_GET['search']) ? trim($_GET['search']) : '';

echo json_encode(getAdministradores($term));
