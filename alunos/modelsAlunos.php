<?php
// alunos/modelsAlunos.php

include '../db.php';
include '../utils.php';

/**
 * Se este ficheiro já foi incluído neste request, não voltar a declarar funções.
 * Isto evita o "Cannot redeclare listarCursos()" mesmo com vários includes.
 */
if (function_exists('listarCursos')) {
    return;
}

/* ============================================================
   LISTAS SECUNDÁRIAS (SELECTS)
============================================================ */

function listarCursos(): array {
    $con = estabelecerConexao();
    $res = $con->query('SELECT id_curso, curso_desc FROM curso ORDER BY curso_desc');
    return $res->fetchAll(PDO::FETCH_ASSOC);
}

function listarTurmas(): array {
    $con = estabelecerConexao();
    $res = $con->query('
        SELECT id_turma, codigo, nome, curso_id
        FROM turma
        ORDER BY nome
    ');
    return $res->fetchAll(PDO::FETCH_ASSOC);
}

function listarEscolas(): array {
    $con = estabelecerConexao();
    $res = $con->query('SELECT id_escola, escola_desc FROM escola ORDER BY escola_desc');
    return $res->fetchAll(PDO::FETCH_ASSOC);
}

function listarNacionalidades(): array {
    $con = estabelecerConexao();
    $res = $con->query('SELECT id_nacionalidade, nacionalidade_desc FROM nacionalidade ORDER BY nacionalidade_desc');
    return $res->fetchAll(PDO::FETCH_ASSOC);
}

/* ============================================================
   ALUNOS — LISTAGENS
============================================================ */

function getTodosAlunos(): array {
    $con = estabelecerConexao();

    $sql = '
        SELECT
            a.id_aluno,
            a.nome AS nome_aluno,
            c.curso_desc,
            emp.nome AS nome_empresa,
            pe.estado_pedido
        FROM aluno a
        LEFT JOIN curso c
            ON c.id_curso = a.curso_id
        LEFT JOIN (
            SELECT pe1.*
            FROM pedido_estagio pe1
            JOIN (
                SELECT aluno_id, MAX(data_criacao) AS max_data
                FROM pedido_estagio
                GROUP BY aluno_id
            ) ult
              ON ult.aluno_id = pe1.aluno_id
             AND ult.max_data = pe1.data_criacao
        ) pe
            ON pe.aluno_id = a.id_aluno
        LEFT JOIN empresa emp
            ON emp.id_empresa = pe.empresa_id
        ORDER BY a.nome
    ';

    $res = $con->query($sql);
    return $res->fetchAll(PDO::FETCH_ASSOC);
}

function getAlunos(string $searchTerm = ''): array {
    $con = estabelecerConexao();

    $sql = '
        SELECT
            a.id_aluno,
            a.nome AS nome_aluno,
            c.curso_desc,
            emp.nome AS nome_empresa,
            pe.estado_pedido
        FROM aluno a
        LEFT JOIN curso c
            ON c.id_curso = a.curso_id
        LEFT JOIN (
            SELECT pe1.*
            FROM pedido_estagio pe1
            JOIN (
                SELECT aluno_id, MAX(data_criacao) AS max_data
                FROM pedido_estagio
                GROUP BY aluno_id
            ) ult
              ON ult.aluno_id = pe1.aluno_id
             AND ult.max_data = pe1.data_criacao
        ) pe
            ON pe.aluno_id = a.id_aluno
        LEFT JOIN empresa emp
            ON emp.id_empresa = pe.empresa_id
    ';

    $params = [];

    if ($searchTerm !== '') {
        $sql .= '
            WHERE a.nome LIKE :search
               OR c.curso_desc LIKE :search
               OR emp.nome LIKE :search
               OR pe.estado_pedido LIKE :search
        ';
        $params[':search'] = '%' . $searchTerm . '%';
    }

    $sql .= ' ORDER BY a.nome';

    $stmt = $con->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Detalhe de um aluno (para ver/editar).
 */
function getAlunoById(int $idAluno) {
    $con = estabelecerConexao();

    $stmt = $con->prepare('
        SELECT
            a.*,
            u.id_utilizador,
            u.username,
            n.nacionalidade_desc,
            c.curso_desc,
            e.escola_desc,
            t.id_turma AS turma_codigo
        FROM aluno a
        LEFT JOIN utilizador u
            ON a.utilizador_id = u.id_utilizador
        LEFT JOIN nacionalidade n
            ON a.nacionalidade_id = n.id_nacionalidade
        LEFT JOIN curso c
            ON a.curso_id = c.id_curso
        LEFT JOIN escola e
            ON a.escola_id = e.id_escola
        LEFT JOIN turma t
            ON a.turma_id = t.id_turma
        WHERE a.id_aluno = :id
    ');
    $stmt->execute(['id' => $idAluno]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function alunoIdExiste(int $idAluno): bool {
    $con = estabelecerConexao();
    $stmt = $con->prepare('SELECT 1 FROM aluno WHERE id_aluno = :id');
    $stmt->execute(['id' => $idAluno]);
    return (bool) $stmt->fetchColumn();
}

/* ============================================================
   CRIAÇÃO
============================================================ */

function criarUtilizadorAluno(string $username, string $password): int {
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
        'tipo_utilizador' => 'Aluno',
        'estado_conta'    => 'Ativo',
    ]);

    return (int) $con->lastInsertId();
}

function criarAluno(array $dados, int $utilizadorId): void {
    $con = estabelecerConexao();

    $stmt = $con->prepare('
        INSERT INTO aluno (
            id_aluno,
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
            situacao_academica,
            cv,
            linkedin,
            github,
            utilizador_id,
            nacionalidade_id,
            curso_id,
            escola_id,
            turma_id
        )
        VALUES (
            :id_aluno,
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
            :situacao_academica,
            :cv,
            :linkedin,
            :github,
            :utilizador_id,
            :nacionalidade_id,
            :curso_id,
            :escola_id,
            :turma_id
        )
    ');

    $stmt->execute([
        'id_aluno'           => $dados['id_aluno'],
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
        'situacao_academica' => $dados['situacao_academica'],
        'cv'                 => $dados['cv'],
        'linkedin'           => $dados['linkedin'],
        'github'             => $dados['github'],
        'utilizador_id'      => $utilizadorId,
        'nacionalidade_id'   => $dados['nacionalidade_id'],
        'curso_id'           => $dados['curso_id'],
        'escola_id'          => $dados['escola_id'],
        'turma_id'           => $dados['turma_id'],
    ]);
}

/* ============================================================
   UPDATE
============================================================ */

function updateAluno(int $idAluno, array $dados): void {
    $con = estabelecerConexao();

    $stmt = $con->prepare('
        UPDATE aluno
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
            situacao_academica  = :situacao_academica,
            cv                  = :cv,
            linkedin            = :linkedin,
            github              = :github,
            nacionalidade_id    = :nacionalidade_id,
            curso_id            = :curso_id,
            escola_id           = :escola_id,
            turma_id            = :turma_id
        WHERE id_aluno          = :id_aluno
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
        'situacao_academica' => $dados['situacao_academica'],
        'cv'                 => $dados['cv'],
        'linkedin'           => $dados['linkedin'],
        'github'             => $dados['github'],
        'nacionalidade_id'   => $dados['nacionalidade_id'],
        'curso_id'           => $dados['curso_id'],
        'escola_id'          => $dados['escola_id'],
        'turma_id'           => $dados['turma_id'],
        'id_aluno'           => $idAluno,
    ]);
}

function updatePasswordUtilizadorAluno(int $idUtilizador, string $novaPassword): void {
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

function deleteAlunoEUtilizador(int $idAluno, ?int $idUtilizador = null): void {
    $con = estabelecerConexao();

    try {
        $con->beginTransaction();

        $stmt = $con->prepare('DELETE FROM aluno WHERE id_aluno = :id_aluno');
        $stmt->execute(['id_aluno' => $idAluno]);

        if (!empty($idUtilizador)) {
            $stmt = $con->prepare('DELETE FROM utilizador WHERE id_utilizador = :id_utilizador');
            $stmt->execute(['id_utilizador' => $idUtilizador]);
        }

        $con->commit();
    } catch (PDOException $e) {
        if ($con->inTransaction()) {
            $con->rollBack();
        }
        die('Erro ao eliminar aluno: ' . $e->getMessage());
    }
}
