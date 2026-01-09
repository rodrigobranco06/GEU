<?php
// administradores/modelsAdministradores.php

if (!defined('MODELS_ADMINISTRADORES_LOADED')) {
    define('MODELS_ADMINISTRADORES_LOADED', true);

include '../db.php';
include '../utils.php';

/* ============================================================
   LISTAGENS
============================================================ */

function getTodosAdministradores(): array {
    $con = estabelecerConexao();

    $res = $con->query('
        SELECT
            a.id_admin,
            a.nome AS nome_admin,
            a.email_institucional
        FROM administrador a
        ORDER BY a.nome
    ');

    return $res->fetchAll(PDO::FETCH_ASSOC);
}

function getAdministradores(string $searchTerm = ''): array {
    $con = estabelecerConexao();

    $sql = '
        SELECT
            a.id_admin,
            a.nome AS nome_admin,
            a.email_institucional
        FROM administrador a
    ';

    $params = [];

    if ($searchTerm !== '') {
        $sql .= '
            WHERE a.nome LIKE :search
               OR a.email_institucional LIKE :search
               OR CAST(a.id_admin AS CHAR) LIKE :search
        ';
        $params[':search'] = '%' . $searchTerm . '%';
    }

    $sql .= ' ORDER BY a.nome';

    $stmt = $con->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAdministradorById(int $idAdmin) {
    $con = estabelecerConexao();

    $stmt = $con->prepare('
        SELECT
            a.*,
            u.id_utilizador,
            u.username
        FROM administrador a
        LEFT JOIN utilizador u
            ON a.utilizador_id = u.id_utilizador
        WHERE a.id_admin = :id
    ');
    $stmt->execute(['id' => $idAdmin]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function verificarEmailExisteAdmin(string $email): bool {
    $con = estabelecerConexao();
    // Verifica na tabela utilizador (username é unico)
    $stmt = $con->prepare('SELECT 1 FROM utilizador WHERE username = :email');
    $stmt->execute(['email' => $email]);
    
    if ($stmt->fetchColumn()) {
        return true;
    }

    // Verifica redundância na tabela administrador
    $stmt2 = $con->prepare('SELECT 1 FROM administrador WHERE email_institucional = :email');
    $stmt2->execute(['email' => $email]);
    return (bool)$stmt2->fetchColumn();
}

/* ============================================================
   CRIAÇÃO
============================================================ */

function criarUtilizadorAdministrador(string $username, string $password): int {
    $con = estabelecerConexao();

    $stmt = $con->prepare('
        INSERT INTO utilizador (
            username,
            password_hash,
            tipo_utilizador,
            estado_conta,
            data_criacao
        )
        VALUES (
            :username,
            :password_hash,
            :tipo_utilizador,
            :estado_conta,
            NOW()
        )
    ');

    $stmt->execute([
        'username'        => $username,
        'password_hash'   => password_hash($password, PASSWORD_DEFAULT),
        'tipo_utilizador' => 'Administrador',
        'estado_conta'    => 'Ativo',
    ]);

    return (int) $con->lastInsertId();
}


function criarAdministrador(array $dados, int $utilizadorId): int {
    $con = estabelecerConexao();

    $stmt = $con->prepare('
        INSERT INTO administrador (
            nome,
            email_institucional,
            utilizador_id
        )
        VALUES (
            :nome,
            :email_institucional,
            :utilizador_id
        )
    ');

    $stmt->execute([
        'nome'                => $dados['nome'],
        'email_institucional' => $dados['email_institucional'],
        'utilizador_id'       => $utilizadorId,
    ]);

    return (int) $con->lastInsertId();
}

/* ============================================================
   UPDATE
============================================================ */

function updateAdministrador(int $idAdmin, array $dados): void {
    $con = estabelecerConexao();

    $stmt = $con->prepare('
        UPDATE administrador
        SET
            nome = :nome,
            email_institucional = :email_institucional
        WHERE id_admin = :id
    ');

    $stmt->execute([
        'nome'                => $dados['nome'],
        'email_institucional' => $dados['email_institucional'],
        'id'                  => $idAdmin,
    ]);
}

function updatePasswordUtilizadorAdministrador(int $idUtilizador, string $novaPassword): void {
    $novaPassword = trim($novaPassword);

    if ($novaPassword === '') {
        return; 
    }

    $con = estabelecerConexao();

    $stmt = $con->prepare('
        UPDATE utilizador
        SET password_hash = :pwd
        WHERE id_utilizador = :id
    ');

    $stmt->execute([
        'pwd' => password_hash($novaPassword, PASSWORD_DEFAULT),
        'id'  => $idUtilizador,
    ]);
}

/* ============================================================
   DELETE
============================================================ */

function deleteAdministradorEUtilizador(int $idAdmin, ?int $idUtilizador = null): void {
    $con = estabelecerConexao();

    try {
        $con->beginTransaction();

        $stmt = $con->prepare('DELETE FROM administrador WHERE id_admin = :id');
        $stmt->execute(['id' => $idAdmin]);

        if (!empty($idUtilizador)) {
            $stmt = $con->prepare('DELETE FROM utilizador WHERE id_utilizador = :uid');
            $stmt->execute(['uid' => $idUtilizador]);
        }

        $con->commit();
    } catch (PDOException $e) {
        if ($con->inTransaction()) {
            $con->rollBack();
        }
        die('Erro ao eliminar administrador: ' . $e->getMessage());
    }
}
}
