<?php
// professores/modelsProfessores.php

include '../db.php';
include '../utils.php';

/* ============================================================
   LISTAS SECUNDÁRIAS (SELECTS)
============================================================ */

function listarEscolas(): array {
    $con = estabelecerConexao();
    $stmt = $con->query('SELECT id_escola, escola_desc FROM escola ORDER BY escola_desc');
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function listarNacionalidades(): array {
    $con = estabelecerConexao();
    $stmt = $con->query('SELECT id_nacionalidade, nacionalidade_desc FROM nacionalidade ORDER BY nacionalidade_desc');
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function listarEspecializacoes(): array {
    $con = estabelecerConexao();
    $stmt = $con->query('SELECT id_especializacao, especializacao_desc FROM especializacao ORDER BY especializacao_desc');
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/* ============================================================
   PROFESSORES
============================================================ */

function getTodosProfessores(): array {
    $con = estabelecerConexao();

    $stmt = $con->query('
        SELECT 
            p.id_professor,
            p.nome AS nome_professor,
            p.email_institucional,
            e.especializacao_desc
        FROM professor p
        LEFT JOIN especializacao e ON e.id_especializacao = p.especializacao_id
        ORDER BY p.nome
    ');

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function professorIdExiste(int $idProfessor): bool {
    $con = estabelecerConexao();
    $stmt = $con->prepare('SELECT 1 FROM professor WHERE id_professor = :id');
    $stmt->execute(['id' => $idProfessor]);
    return (bool) $stmt->fetchColumn();
}

function getProfessorById(int $idProfessor) {
    $con = estabelecerConexao();

    $stmt = $con->prepare('
        SELECT 
            p.*,
            u.id_utilizador,
            u.username,
            n.nacionalidade_desc,
            e.escola_desc,
            s.especializacao_desc
        FROM professor p
        LEFT JOIN utilizador u
            ON p.utilizador_id = u.id_utilizador
        LEFT JOIN nacionalidade n
            ON p.nacionalidade_id = n.id_nacionalidade
        LEFT JOIN escola e
            ON p.escola_id = e.id_escola
        LEFT JOIN especializacao s
            ON p.especializacao_id = s.id_especializacao
        WHERE p.id_professor = :id
    ');
    $stmt->execute(['id' => $idProfessor]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/* ============================================================
   CRIAÇÃO
============================================================ */

function criarUtilizadorProfessor(string $username, string $password): int {
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
        'tipo_utilizador' => 'Professor',
        'estado_conta'    => 'Ativo',
    ]);

    return (int) $con->lastInsertId();
}

function criarProfessor(array $dados, int $utilizadorId): void {
    $con = estabelecerConexao();

    $stmt = $con->prepare('
        INSERT INTO professor (
            id_professor,
            nome,
            data_nascimento,
            sexo,
            nif,
            numero_cc,
            email_institucional,
            email_pessoal,
            morada,
            codigo_postal,
            cidade,
            utilizador_id,
            nacionalidade_id,
            escola_id,
            especializacao_id
        )
        VALUES (
            :id_professor,
            :nome,
            :data_nascimento,
            :sexo,
            :nif,
            :numero_cc,
            :email_institucional,
            :email_pessoal,
            :morada,
            :codigo_postal,
            :cidade,
            :utilizador_id,
            :nacionalidade_id,
            :escola_id,
            :especializacao_id
        )
    ');

    $stmt->execute([
        'id_professor'        => $dados['id_professor'],
        'nome'                => $dados['nome'],
        'data_nascimento'     => $dados['data_nascimento'],
        'sexo'                => $dados['sexo'],
        'nif'                 => $dados['nif'],
        'numero_cc'           => $dados['numero_cc'],
        'email_institucional' => $dados['email_institucional'],
        'email_pessoal'       => $dados['email_pessoal'],
        'morada'              => $dados['morada'],
        'codigo_postal'       => $dados['codigo_postal'],
        'cidade'              => $dados['cidade'],
        'utilizador_id'       => $utilizadorId,
        'nacionalidade_id'    => $dados['nacionalidade_id'],
        'escola_id'           => $dados['escola_id'],
        'especializacao_id'   => $dados['especializacao_id'],
    ]);
}

/* ============================================================
   UPDATE
============================================================ */

function updateProfessor(int $idProfessor, array $dados): void {
    $con = estabelecerConexao();

    $stmt = $con->prepare('
        UPDATE professor
        SET
            nome                = :nome,
            data_nascimento     = :data_nascimento,
            sexo                = :sexo,
            nif                 = :nif,
            numero_cc           = :numero_cc,
            email_institucional = :email_institucional,
            email_pessoal       = :email_pessoal,
            morada              = :morada,
            codigo_postal       = :codigo_postal,
            cidade              = :cidade,
            nacionalidade_id    = :nacionalidade_id,
            escola_id           = :escola_id,
            especializacao_id   = :especializacao_id
        WHERE id_professor     = :id_professor
    ');

    $stmt->execute([
        'nome'               => $dados['nome'],
        'data_nascimento'    => $dados['data_nascimento'],
        'sexo'               => $dados['sexo'],
        'nif'                => $dados['nif'],
        'numero_cc'          => $dados['numero_cc'],
        'email_institucional'=> $dados['email_institucional'],
        'email_pessoal'      => $dados['email_pessoal'],
        'morada'             => $dados['morada'],
        'codigo_postal'      => $dados['codigo_postal'],
        'cidade'             => $dados['cidade'],
        'nacionalidade_id'   => $dados['nacionalidade_id'],
        'escola_id'          => $dados['escola_id'],
        'especializacao_id'  => $dados['especializacao_id'],
        'id_professor'       => $idProfessor,
    ]);
}

function updatePasswordUtilizador(int $idUtilizador, string $novaPassword): void {
    if ($novaPassword === '') {
        return;
    }

    $con = estabelecerConexao();

    $stmt = $con->prepare('
        UPDATE utilizador
        SET password_hash = :pwd
        WHERE id_utilizador = :id_utilizador
    ');

    $stmt->execute([
        'pwd'           => password_hash($novaPassword, PASSWORD_DEFAULT),
        'id_utilizador' => $idUtilizador,
    ]);
}

/* ============================================================
   DELETE
============================================================ */

function deleteProfessorEUtilizador(int $idProfessor, ?int $idUtilizador = null): void {
    $con = estabelecerConexao();

    try {
        $con->beginTransaction();

        $stmt = $con->prepare('DELETE FROM professor WHERE id_professor = :id_professor');
        $stmt->execute(['id_professor' => $idProfessor]);

        if (!empty($idUtilizador)) {
            $stmt = $con->prepare('DELETE FROM utilizador WHERE id_utilizador = :id_utilizador');
            $stmt->execute(['id_utilizador' => $idUtilizador]);
        }

        $con->commit();
    } catch (PDOException $e) {
        if ($con->inTransaction()) {
            $con->rollBack();
        }
        die('Erro ao eliminar professor: ' . $e->getMessage());
    }
}
