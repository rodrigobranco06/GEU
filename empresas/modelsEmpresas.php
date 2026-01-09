<?php
// empresas/modelsEmpresas.php

if (!defined('MODELS_EMPRESAS_LOADED')) {
    define('MODELS_EMPRESAS_LOADED', true);

include '../db.php';

/* ============================================================
   LISTAS AUXILIARES (Dropdowns)
============================================================ */

function listarRamosAtividade(): array {
    $con = estabelecerConexao();
    $stmt = $con->query('SELECT id_ramo_atividade, ramo_atividade_desc FROM ramo_atividade ORDER BY ramo_atividade_desc');
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function listarPaises(): array {
    $con = estabelecerConexao();
    $stmt = $con->query('SELECT id_pais, pais_desc FROM pais ORDER BY pais_desc');
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/* ============================================================
   LEITURA (READ)
============================================================ */

function getTodasEmpresas(): array {
    $con = estabelecerConexao();
    $sql = "
        SELECT 
            e.id_empresa,
            e.nome,
            e.email,
            (SELECT COUNT(*) 
                FROM pedido_estagio pe 
                WHERE pe.empresa_id = e.id_empresa 
                AND pe.estado_pedido = 'Concluído') AS numero_estagios,
            r.ramo_atividade_desc
        FROM empresa e
        LEFT JOIN ramo_atividade r ON e.ramo_atividade_id = r.id_ramo_atividade
        ORDER BY e.nome
    ";
    $stmt = $con->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getEmpresaById(int $idEmpresa) {
    $con = estabelecerConexao();
    $sql = '
        SELECT 
            e.*,
            r.ramo_atividade_desc,
            p.pais_desc,
            u.username AS email_login
        FROM empresa e
        LEFT JOIN ramo_atividade r ON e.ramo_atividade_id = r.id_ramo_atividade
        LEFT JOIN pais p ON e.pais_id = p.id_pais
        LEFT JOIN utilizador u ON e.utilizador_id = u.id_utilizador
        WHERE e.id_empresa = :id
    ';
    $stmt = $con->prepare($sql);
    $stmt->execute(['id' => $idEmpresa]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function verificarEmailExisteEmpresa(string $email): bool {
    $con = estabelecerConexao();
    
    $stmt = $con->prepare('SELECT 1 FROM utilizador WHERE username = :email');
    $stmt->execute(['email' => $email]);
    if ($stmt->fetchColumn()) {
        return true;
    }

    $stmt2 = $con->prepare('SELECT 1 FROM empresa WHERE email = :email');
    $stmt2->execute(['email' => $email]);
    return (bool)$stmt2->fetchColumn();
}

function empresaTemEstagios(int $idEmpresa): bool {
        $con = estabelecerConexao();
        // Verifica se existe algum pedido de estágio ligado a esta empresa
        $stmt = $con->prepare('SELECT 1 FROM pedido_estagio WHERE empresa_id = :id LIMIT 1');
        $stmt->execute(['id' => $idEmpresa]);
        return (bool)$stmt->fetchColumn();
    }

/* ============================================================
   CRIAÇÃO (CREATE)
============================================================ */

function criarUtilizadorEmpresa(string $email, string $password): int {
    $con = estabelecerConexao();
    $stmt = $con->prepare('
        INSERT INTO utilizador (username, password_hash, tipo_utilizador, estado_conta, data_criacao)
        VALUES (:username, :pass, :tipo, :estado, NOW())
    ');
    $stmt->execute([
        'username' => $email,
        'pass'     => password_hash($password, PASSWORD_DEFAULT),
        'tipo'     => 'Empresa',
        'estado'   => 'Ativo'
    ]);
    return (int)$con->lastInsertId();
}

function criarEmpresa(array $dados, int $idUtilizador): void {
    $con = estabelecerConexao();
    $sql = '
        INSERT INTO empresa (
            nome, nif, morada, codigo_postal, cidade, telefone, email,
            website, linkedin, 
            nome_responsavel, cargo_responsavel, email_responsavel, telefone_responsavel,
            numero_estagios, pais_id, ramo_atividade_id, utilizador_id
        ) VALUES (
            :nome, :nif, :morada, :cp, :cidade, :telefone, :email,
            :website, :linkedin,
            :nome_resp, :cargo_resp, :email_resp, :tel_resp,
            0, :pais, :ramo, :uid
        )
    ';
    
    $stmt = $con->prepare($sql);
    $stmt->execute([
        'nome'       => $dados['nome'],
        'nif'        => $dados['nif'],
        'morada'     => $dados['morada'],
        'cp'         => $dados['codigo_postal'],
        'cidade'     => $dados['cidade'],
        'telefone'   => $dados['telefone'],
        'email'      => $dados['email'],
        'website'    => $dados['website'],
        'linkedin'   => $dados['linkedin'],
        'nome_resp'  => $dados['nome_responsavel'],
        'cargo_resp' => $dados['cargo_responsavel'],
        'email_resp' => $dados['email_responsavel'],
        'tel_resp'   => $dados['telefone_responsavel'],
        'pais'       => $dados['pais_id'],
        'ramo'       => $dados['ramo_atividade_id'],
        'uid'        => $idUtilizador
    ]);
}

/* ============================================================
   ATUALIZAÇÃO (UPDATE)
============================================================ */

function updateEmpresa(int $idEmpresa, array $dados): void {
    $con = estabelecerConexao();
    $sql = '
        UPDATE empresa SET
            nome = :nome,
            nif = :nif,
            morada = :morada,
            codigo_postal = :cp,
            cidade = :cidade,
            telefone = :telefone,
            email = :email,
            website = :website,
            linkedin = :linkedin,
            nome_responsavel = :nome_resp,
            cargo_responsavel = :cargo_resp,
            email_responsavel = :email_resp,
            telefone_responsavel = :tel_resp,
            pais_id = :pais,
            ramo_atividade_id = :ramo
        WHERE id_empresa = :id
    ';
    
    $stmt = $con->prepare($sql);
    $stmt->execute([
        'nome'       => $dados['nome'],
        'nif'        => $dados['nif'],
        'morada'     => $dados['morada'],
        'cp'         => $dados['codigo_postal'],
        'cidade'     => $dados['cidade'],
        'telefone'   => $dados['telefone'],
        'email'      => $dados['email'],
        'website'    => $dados['website'],
        'linkedin'   => $dados['linkedin'],
        'nome_resp'  => $dados['nome_responsavel'],
        'cargo_resp' => $dados['cargo_responsavel'],
        'email_resp' => $dados['email_responsavel'],
        'tel_resp'   => $dados['telefone_responsavel'],
        'pais'       => $dados['pais_id'],
        'ramo'       => $dados['ramo_atividade_id'],
        'id'         => $idEmpresa
    ]);
}

function updatePasswordUtilizador(int $idUtilizador, string $novaPassword): void {
    if (empty($novaPassword)) return;
    $con = estabelecerConexao();
    $stmt = $con->prepare('UPDATE utilizador SET password_hash = :pwd WHERE id_utilizador = :id');
    $stmt->execute([
        'pwd' => password_hash($novaPassword, PASSWORD_DEFAULT),
        'id'  => $idUtilizador
    ]);
}

/* ============================================================
   ELIMINAÇÃO (DELETE)
============================================================ */

function deleteEmpresaEUtilizador(int $idEmpresa, ?int $idUtilizador): void {
    $con = estabelecerConexao();
    try {
        $con->beginTransaction();
        
        $stmt = $con->prepare('DELETE FROM empresa WHERE id_empresa = :id');
        $stmt->execute(['id' => $idEmpresa]);
        
        if ($idUtilizador) {
            $stmt = $con->prepare('DELETE FROM utilizador WHERE id_utilizador = :id');
            $stmt->execute(['id' => $idUtilizador]);
        }
        
        $con->commit();
    } catch (PDOException $e) {
        $con->rollBack();
        throw $e;
    }
}

}
?>