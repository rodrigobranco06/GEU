CREATE TABLE pais (
  id_pais SERIAL PRIMARY KEY,
  pais_desc VARCHAR(100) NOT NULL
);

CREATE TABLE nacionalidade (
  id_nacionalidade SERIAL PRIMARY KEY,
  nacionalidade_desc VARCHAR(100) NOT NULL
);

CREATE TABLE especializacao (
  id_especializacao SERIAL PRIMARY KEY,
  especializacao_desc VARCHAR(150) NOT NULL
);

CREATE TABLE escola (
  id_escola SERIAL PRIMARY KEY,
  escola_desc VARCHAR(150) NOT NULL
);

CREATE TABLE curso (
  id_curso SERIAL PRIMARY KEY,
  curso_desc VARCHAR(150) NOT NULL
);

CREATE TABLE ramo_atividade (
  id_ramo_atividade SERIAL PRIMARY KEY,
  ramo_atividade_desc VARCHAR(150) NOT NULL
);

CREATE TABLE area_cientifica (
  id_area_cientifica SERIAL PRIMARY KEY,
  area_cientifica_desc VARCHAR(150) NOT NULL
);

CREATE TABLE utilizador (
  id_utilizador SERIAL PRIMARY KEY,
  username VARCHAR(80) NOT NULL,
  password_hash TEXT NOT NULL,
  tipo_utilizador VARCHAR(40) NOT NULL,
  estado_conta VARCHAR(40) NOT NULL,
  data_criacao TIMESTAMP NOT NULL
);

CREATE TABLE professor (
  id_professor SERIAL PRIMARY KEY,
  nome VARCHAR(150) NOT NULL,
  data_nascimento DATE,
  sexo VARCHAR(20),
  nif VARCHAR(20),
  numero_cc VARCHAR(30),
  email_institucional VARCHAR(150),
  email_pessoal VARCHAR(150),
  morada VARCHAR(200),
  codigo_postal VARCHAR(16),
  cidade VARCHAR(100),
  utilizador_id INTEGER,
  nacionalidade_id INTEGER,
  escola_id INTEGER,
  especializacao_id INTEGER,
  CONSTRAINT professor_utilizador_fk FOREIGN KEY (utilizador_id) REFERENCES utilizador (id_utilizador),
  CONSTRAINT professor_nacionalidade_fk FOREIGN KEY (nacionalidade_id) REFERENCES nacionalidade (id_nacionalidade),
  CONSTRAINT professor_escola_fk FOREIGN KEY (escola_id) REFERENCES escola (id_escola),
  CONSTRAINT professor_especializacao_fk FOREIGN KEY (especializacao_id) REFERENCES especializacao (id_especializacao)
);

CREATE TABLE aluno (
  id_aluno SERIAL PRIMARY KEY,
  nome VARCHAR(150) NOT NULL,
  data_nascimento DATE,
  sexo VARCHAR(20),
  nif VARCHAR(20),
  numero_cc VARCHAR(30),
  email_institucional VARCHAR(150),
  email_pessoal VARCHAR(150),
  morada VARCHAR(200),
  codigo_postal VARCHAR(16),
  cidade VARCHAR(100),
  situacao_academica VARCHAR(80),
  cv TEXT,
  linkedin VARCHAR(200),
  github VARCHAR(200),
  utilizador_id INTEGER,
  nacionalidade_id INTEGER,
  curso_id INTEGER,
  escola_id INTEGER,
  turma_id INTEGER,
  CONSTRAINT aluno_utilizador_fk FOREIGN KEY (utilizador_id) REFERENCES utilizador (id_utilizador),
  CONSTRAINT aluno_nacionalidade_fk FOREIGN KEY (nacionalidade_id) REFERENCES nacionalidade (id_nacionalidade),
  CONSTRAINT aluno_curso_fk FOREIGN KEY (curso_id) REFERENCES curso (id_curso)
  CONSTRAINT aluno_escola_fk FOREIGN KEY (escola_id) REFERENCES escola (id_escola),
  CONSTRAINT aluno_turma_fk FOREIGN KEY (turma_id) REFERENCES escola (id_turma),
);

CREATE TABLE turma (
  id_turma SERIAL PRIMARY KEY,
  ano_inicio INTEGER NOT NULL,
  ano_fim INTEGER,
  ano_curricular INTEGER,
  curso_id INTEGER NOT NULL,
  professor_id INTEGER,
  CONSTRAINT turma_curso_fk FOREIGN KEY (curso_id) REFERENCES curso (id_curso),
  CONSTRAINT turma_professor_fk FOREIGN KEY (professor_id) REFERENCES professor (id_professor)
);

-- EMPRESA
CREATE TABLE empresa (
  id_empresa SERIAL PRIMARY KEY,
  nome VARCHAR(200) NOT NULL,
  nif VARCHAR(20),
  morada VARCHAR(200),
  codigo_postal VARCHAR(16),
  cidade VARCHAR(100),
  telefone VARCHAR(40),
  email VARCHAR(150),
  website VARCHAR(200),
  linkedin VARCHAR(200),
  nome_responsavel VARCHAR(150),
  cargo_responsavel VARCHAR(100),
  email_responsavel VARCHAR(150),
  telefone_responsavel VARCHAR(15),
  numero_estagios INTEGER,
  pais_id INTEGER,
  utilizador_id INTEGER,
  ramo_atividade_id INTEGER,
  CONSTRAINT empresa_pais_fk FOREIGN KEY (pais_id) REFERENCES pais (id_pais),
  CONSTRAINT empresa_utilizador_fk FOREIGN KEY (utilizador_id) REFERENCES utilizador (id_utilizador),
  CONSTRAINT empresa_ramo_fk FOREIGN KEY (ramo_atividade_id) REFERENCES ramo_atividade (id_ramo_atividade)
);

CREATE TABLE pedido_estagio (
  id_pedido_estagio SERIAL PRIMARY KEY,
  estado_pedido VARCHAR(40) NOT NULL,
  fase_atual VARCHAR(40),
  data_criacao TIMESTAMP NOT NULL,
  data_ultima_atualizacao TIMESTAMP,
  aluno_id INTEGER NOT NULL,
  professor_id INTEGER,
  empresa_id INTEGER,
  CONSTRAINT pedido_aluno_fk FOREIGN KEY (aluno_id) REFERENCES aluno (id_aluno),
  CONSTRAINT pedido_professor_fk FOREIGN KEY (professor_id) REFERENCES professor (id_professor),
  CONSTRAINT pedido_empresa_fk FOREIGN KEY (empresa_id) REFERENCES empresa (id_empresa)
);

CREATE TABLE fase_confirmacao (
  id_pedido_estagio INTEGER PRIMARY KEY,
  numero_ucs_atraso VARCHAR(60),
  estado_confirmacao VARCHAR(40),
  data_confirmacao DATE,
  CONSTRAINT fase_confirmacao_pedido_fk FOREIGN KEY (id_pedido_estagio) REFERENCES pedido_estagio (id_pedido_estagio)
);

CREATE TABLE fase_area (
  id_pedido_estagio INTEGER PRIMARY KEY,
  cidade VARCHAR(100),
  data_inicio_prevista DATE,
  data_fim_prevista DATE,
  estado_definicao_area VARCHAR(40),
  data_definicao_area DATE,
  area_cientifica_id INTEGER,
  CONSTRAINT fase_area_pedido_fk FOREIGN KEY (id_pedido_estagio) REFERENCES pedido_estagio (id_pedido_estagio),
  CONSTRAINT fase_area_area_fk FOREIGN KEY (area_cientifica_id) REFERENCES area_cientifica (id_area_cientifica)
);

CREATE TABLE fase_email (
  id_pedido_estagio INTEGER PRIMARY KEY,
  email_empresa VARCHAR(150),
  cv TEXT,
  estado_envio_email VARCHAR(40),
  data_envio_email TIMESTAMP,
  CONSTRAINT fase_email_pedido_fk FOREIGN KEY (id_pedido_estagio) REFERENCES pedido_estagio (id_pedido_estagio)
);

CREATE TABLE fase_resposta (
  id_pedido_estagio INTEGER PRIMARY KEY,
  resposta_empresa VARCHAR(40),
  mensagem_recebida TEXT,
  data_resposta TIMESTAMP,
  CONSTRAINT fase_resposta_pedido_fk FOREIGN KEY (id_pedido_estagio) REFERENCES pedido_estagio (id_pedido_estagio)
);

CREATE TABLE fase_plano (
  id_pedido_estagio INTEGER PRIMARY KEY,
  plano_estagio TEXT,
  data_inicio DATE,
  data_fim DATE,
  CONSTRAINT fase_plano_pedido_fk FOREIGN KEY (id_pedido_estagio) REFERENCES pedido_estagio (id_pedido_estagio)
);

CREATE TABLE fase_avaliacao (
  id_pedido_estagio INTEGER PRIMARY KEY,
  nota_final NUMERIC(4,2),
  relatorio TEXT,
  observacoes TEXT,
  data_avaliacao DATE,
  CONSTRAINT fase_publicacao_pedido_fk FOREIGN KEY (id_pedido_estagio) REFERENCES pedido_estagio (id_pedido_estagio)
);
