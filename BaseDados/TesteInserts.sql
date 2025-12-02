INSERT INTO nacionalidade (nacionalidade_desc)
VALUES ('Portuguesa');

INSERT INTO escola (escola_desc)
VALUES ('Escola Superior de Gestão de Santarém');

INSERT INTO especializacao (especializacao_desc)
VALUES ('Tecnologias de Informação');

INSERT INTO utilizador (username, password_hash, tipo_utilizador, estado_conta, data_criacao)
VALUES ('prof_teste', 'x', 'professor', 'ativo', NOW());

INSERT INTO curso (curso_desc)
VALUES ('TPSI');

INSERT INTO curso (curso_desc)
VALUES ('TDL');

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
) VALUES (
    240001462,
    'Professor Teste',
    '1980-01-01',
    'M',
    '123456789',
    '00000000',
    'prof.teste@esg.ipsantarem.pt',
    'prof.teste@gmail.com',
    'Morada de teste',
    '2000-000',
    'Santarém',
    1,  -- id_utilizador
    1,  -- id_nacionalidade
    1,  -- id_escola
    1   -- id_especializacao
);

INSERT INTO turma (
    codigo,
    nome,
    ano_inicio,
    ano_fim,
    ano_curricular,
    curso_id,
    professor_id
) VALUES (
    'tpsi20242026',
    'TPSI - 2 (2024/2026)',
    2024,
    2026,
    2,
    1,          -- id_curso para TPSI (ajusta se for outro)
    240001462   -- id_professor
);

