<?php
// modelsTabelasSecundarias.php

function listarTabela(PDO $con, $tabela, $colunaDesc) {
    $res = $con->query("SELECT * FROM $tabela ORDER BY $colunaDesc ASC");
    return $res->fetchAll(PDO::FETCH_ASSOC);
}

function adicionarRegisto(PDO $con, $tabela, $colunaDesc, $valor) {
    $stmt = $con->prepare("INSERT INTO $tabela ($colunaDesc) VALUES (:valor)");
    return $stmt->execute(['valor' => $valor]);
}

function apagarRegisto(PDO $con, $tabela, $colunaId, $id) {
    $stmt = $con->prepare("DELETE FROM $tabela WHERE $colunaId = :id");
    return $stmt->execute(['id' => $id]);
}