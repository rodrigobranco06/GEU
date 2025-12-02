<?php
include '../db.php';
include '../utils.php';

/**
 * Verifica se já existe um professor com este id_professor
 */
function professorIdExiste(int $idProfessor): bool
{
    $conexao = estabelecerConexao();
    $stmt = $conexao->prepare('SELECT 1 FROM professor WHERE id_professor = :id');
    $stmt->execute(['id' => $idProfessor]);
    return (bool) $stmt->fetchColumn();
}

/**
 * Cria um utilizador e devolve o id_utilizador
 */
function criarUtilizadorProfessor(PDO $conexao, string $username, string $password): int
{
    $sql = 'INSERT INTO utilizador (
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
            )';

    $stmt = $conexao->prepare($sql);

    $stmt->execute([
        'username'        => $username,
        'password_hash'   => password_hash($password, PASSWORD_DEFAULT),
        'tipo_utilizador' => 'Professor',
        'estado_conta'    => 'Ativo',
    ]);

    return (int) $conexao->lastInsertId();
}

/**
 * Cria um professor ligado a um utilizador
 */
function criarProfessor(PDO $conexao, array $dados, int $utilizadorId): void
{
    $sql = 'INSERT INTO professor (
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
            )';

    $stmt = $conexao->prepare($sql);

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


$erros = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Ler dados do formulário
    $codigoProf      = trim($_POST['codigoProf'] ?? '');
    $password        = trim($_POST['password'] ?? '');
    $nome            = trim($_POST['nomeProf'] ?? '');
    $dataNascimento  = $_POST['data_nascimento'] ?? null; 
    $sexo            = trim($_POST['sexo'] ?? '');
    $nacionalidadeId = $_POST['nacionalidade_id'] ?? '';
    $especializacaoId= $_POST['especializacao_id'] ?? '';
    $nif             = trim($_POST['nif'] ?? '');
    $numeroCc        = trim($_POST['cc'] ?? '');
    $escolaId        = $_POST['escola_id'] ?? '';
    $emailInst       = trim($_POST['emailInstitucional'] ?? '');
    $emailPessoal    = trim($_POST['emailPessoal'] ?? '');
    $morada          = trim($_POST['morada'] ?? '');
    $codigoPostal    = trim($_POST['cp'] ?? '');
    $cidade          = trim($_POST['cidade'] ?? '');

    // Validações básicas
    if ($codigoProf === '' || !ctype_digit($codigoProf)) {
        $erros[] = 'O código do professor é obrigatório e deve ser numérico.';
    }

    if ($nome === '') {
        $erros[] = 'O nome do professor é obrigatório.';
    }

    if (empty($dataNascimento)) {
        $erros[] = 'A data de nascimento é obrigatória.';
    }

    if ($emailInst === '') {
        $erros[] = 'O email institucional é obrigatório.';
    }

    if ($password === '') {
        $erros[] = 'A password é obrigatória.';
    }

    // Verificar se o id_professor já existe
    if ($codigoProf !== '' && ctype_digit($codigoProf)) {
        if (professorIdExiste((int)$codigoProf)) {
            $erros[] = 'Já existe um professor com esse código/ID.';
        }
    }

    if (empty($erros)) {
        try {
            $conexao = estabelecerConexao();
            $conexao->beginTransaction();

            // Vamos usar o email institucional como username
            $username = $emailInst;

            $idUtilizador = criarUtilizadorProfessor($conexao, $username, $password);

            $dadosProfessor = [
                'id_professor'        => (int)$codigoProf,
                'nome'                => $nome,
                'data_nascimento'     => $dataNascimento, // yyyy-mm-dd
                'sexo'                => $sexo,
                'nif'                 => $nif,
                'numero_cc'           => $numeroCc,
                'email_institucional' => $emailInst,
                'email_pessoal'       => $emailPessoal,
                'morada'              => $morada,
                'codigo_postal'       => $codigoPostal,
                'cidade'              => $cidade,
                'nacionalidade_id'    => $nacionalidadeId !== '' ? (int)$nacionalidadeId : null,
                'escola_id'           => $escolaId        !== '' ? (int)$escolaId        : null,
                'especializacao_id'   => $especializacaoId!== '' ? (int)$especializacaoId: null,
            ];


            criarProfessor($conexao, $dadosProfessor, $idUtilizador);

            $conexao->commit();

            header('Location: index.php');
            exit;
        } catch (PDOException $e) {
            if (isset($conexao) && $conexao->inTransaction()) {
                $conexao->rollBack();
            }
            $erros[] = 'Erro ao criar professor: ' . $e->getMessage();
        }
    }
}

function buscarNacionalidades(): array
{
    $conexao = estabelecerConexao();
    $stmt = $conexao->query('SELECT id_nacionalidade, nacionalidade_desc FROM nacionalidade ORDER BY nacionalidade_desc');
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function buscarEscolas(): array
{
    $conexao = estabelecerConexao();
    $stmt = $conexao->query('SELECT id_escola, escola_desc FROM escola ORDER BY escola_desc');
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function buscarEspecializacoes(): array
{
    $conexao = estabelecerConexao();
    $stmt = $conexao->query('SELECT id_especializacao, especializacao_desc FROM especializacao ORDER BY especializacao_desc');
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$nacionalidades   = buscarNacionalidades();
$escolas          = buscarEscolas();
$especializacoes  = buscarEspecializacoes();


?>



<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>GEU — Registar novo professor</title>
    <link rel="stylesheet" href="css/registarProfessor.css" />
</head>
<body>

    <!-- ======= CABEÇALHO ======= -->
    <header id="header">
        <div class="header-logo">
            <a href="index.html">
                <img src="../img/Logo.png" alt="Gestão de Estágios Universitários">
            </a>
        </div>

        <nav class="nav-menu">
            <a href="../alunos/index.html" class="nav-link">Alunos</a>
            <a href="index.html" class="nav-link active">Professores</a>
            <a href="../empresas/index.html" class="nav-link">Empresas</a>
            <a href="../index.html" class="nav-link">Turmas</a>

            <button class="btn-conta" id="btn-conta">
                <img src="../img/img_conta.png" alt="Conta">
            </button>
            <a href="login.html" class="btn-sair">Sair</a>
        </nav>
    </header>

    <!-- ======= CONTEÚDO PRINCIPAL ======= -->
    <main id="main-content">

        <!-- Subtabs -->
        <nav class="subtabs">
            <a href="index.html" class="subtab-link">Ver Professores</a>
            <a href="registarProfessor.html" class="subtab-link active">Registar novo professor</a>
        </nav>

        <section class="content-grid">
            <!-- Coluna esquerda: formulário -->
            <form class="form-professor" method="post" action="registarProfessor.php" id="formRegistoProf">

                <?php if (!empty($erros)): ?>
                    <div class="erros">
                        <ul>
                            <?php foreach ($erros as $erro): ?>
                                <li><?= htmlspecialchars($erro) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="codigoProf">Código Professor</label>
                    <input
                        id="codigoProf"
                        name="codigoProf"
                        type="text"
                        value="<?= htmlspecialchars($_POST['codigoProf'] ?? '') ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        value="<?= htmlspecialchars($_POST['password'] ?? '') ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="nomeProf">Nome Professor</label>
                    <input
                        id="nomeProf"
                        name="nomeProf"
                        type="text"
                        value="<?= htmlspecialchars($_POST['nomeProf'] ?? '') ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="dataNascimento">Data nascimento</label>
                    <input
                        id="dataNascimento"
                        name="data_nascimento"
                        type="date"
                        value="<?= htmlspecialchars($_POST['data_nascimento'] ?? '') ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="sexo">Sexo</label>
                    <div class="select-wrapper">
                        <select id="sexo" name="sexo">
                            <option value="">Selecione um sexo</option>
                            <option value="Masculino" <?= (($_POST['sexo'] ?? '') === 'Masculino') ? 'selected' : '' ?>>Masculino</option>
                            <option value="Feminino" <?= (($_POST['sexo'] ?? '') === 'Feminino') ? 'selected' : '' ?>>Feminino</option>
                            <option value="Outro"     <?= (($_POST['sexo'] ?? '') === 'Outro')     ? 'selected' : '' ?>>Outro</option>
                        </select>
                        <span class="chevron">▾</span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="nacionalidade">Nacionalidade</label>
                    <div class="select-wrapper">
                        <select id="nacionalidade" name="nacionalidade_id">
                            <option value="">Selecione uma nacionalidade</option>
                            <?php foreach ($nacionalidades as $nac): ?>
                                <option
                                    value="<?= $nac['id_nacionalidade'] ?>"
                                    <?= (($_POST['nacionalidade_id'] ?? '') == $nac['id_nacionalidade']) ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars($nac['nacionalidade_desc']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <span class="chevron">▾</span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="especializacao">Especialização</label>
                    <div class="select-wrapper">
                        <select id="especializacao" name="especializacao_id">
                            <option value="">Selecione uma especialização</option>
                            <?php foreach ($especializacoes as $esp): ?>
                                <option
                                    value="<?= $esp['id_especializacao'] ?>"
                                    <?= (($_POST['especializacao_id'] ?? '') == $esp['id_especializacao']) ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars($esp['especializacao_desc']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <span class="chevron">▾</span>
                    </div>
                </div>



                <div class="form-group">
                    <label for="nif">NIF</label>
                    <input
                        id="nif"
                        name="nif"
                        type="text"
                        value="<?= htmlspecialchars($_POST['nif'] ?? '') ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="cc">Número CC</label>
                    <input
                        id="cc"
                        name="cc"
                        type="text"
                        value="<?= htmlspecialchars($_POST['cc'] ?? '') ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="escola">Escola</label>
                    <div class="select-wrapper">
                        <select id="escola" name="escola_id">
                            <option value="">Selecione uma escola</option>
                            <?php foreach ($escolas as $esc): ?>
                                <option
                                    value="<?= $esc['id_escola'] ?>"
                                    <?= (($_POST['escola_id'] ?? '') == $esc['id_escola']) ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars($esc['escola_desc']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <span class="chevron">▾</span>
                    </div>
                </div>


                <div class="form-group">
                    <label for="emailInstitucional">Email institucional</label>
                    <input
                        id="emailInstitucional"
                        name="emailInstitucional"
                        type="email"
                        value="<?= htmlspecialchars($_POST['emailInstitucional'] ?? '') ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="emailPessoal">Email pessoal <span class="opcional">(opcional)</span></label>
                    <input
                        id="emailPessoal"
                        name="emailPessoal"
                        type="email"
                        value="<?= htmlspecialchars($_POST['emailPessoal'] ?? '') ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="morada">Morada</label>
                    <input
                        id="morada"
                        name="morada"
                        type="text"
                        value="<?= htmlspecialchars($_POST['morada'] ?? '') ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="cp">Código-Postal</label>
                    <input
                        id="cp"
                        name="cp"
                        type="text"
                        value="<?= htmlspecialchars($_POST['cp'] ?? '') ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="cidade">Cidade</label>
                    <input
                        id="cidade"
                        name="cidade"
                        type="text"
                        value="<?= htmlspecialchars($_POST['cidade'] ?? '') ?>"
                    >
                </div>

                <div class="side-top">
                    <button class="btn-salvar" type="submit">
                        Salvar
                    </button>
                </div>

            </form>

            <!-- Coluna direita: botão + imagem -->
            <aside class="side-panel">
                <div class="side-top">
                    <button class="btn-salvar" type="submit" form="formRegistoProf">
                        Salvar
                    </button>
                </div>

                <div class="side-image-wrapper">
                    <img src="../img/img_registarAluno.png" alt="Ilustração registar professor">
                </div>
            </aside>
        </section>
    </main>

    <!-- ======= RODAPÉ ======= -->
    <footer id="footer">
        <div class="contactos">
            <h3>Contactos</h3>
            <p>
                <img src="../img/img_email.png" alt="Email">
                <strong>Email:</strong> geral@ipsantarem.pt
            </p>
            <p>
                <img src="../img/img_telemovel.png" alt="Telefone">
                <strong>Telefone:</strong> +351 243 309 520
            </p>
            <p>
                <img src="../img/img_localizacao.png" alt="Endereço">
                <strong>Endereço:</strong> Complexo Andaluz, Apartado 279, 2001-904 Santarém
            </p>
        </div>

        <div class="logos">
            <img src="../img/Logo.png" alt="Gestão de Estágios Universitários">
            <img src="../img/img_confinanciado.png" alt="Confinanciado">
        </div>
    </footer>

<!-- ======= MODAL PERFIL / CONTA ======= -->
    <div id="perfil-overlay" class="perfil-overlay">
    <div class="perfil-card">
        <div class="perfil-banner"></div>

        <div class="perfil-avatar">
            <img src="../img/img_conta.png" alt="Avatar" class="perfil-avatar-img">
        </div>

        <div class="perfil-content">
            <div class="perfil-role">Aluno</div>
            <div class="perfil-name">Rodrigo Branco</div>

            <div class="perfil-row">
                <img src="../img/img_email.png" alt="Email" class="perfil-row-img">
                <span class="perfil-row-text">240001087@esg.ipsantarem.pt</span>
            </div>

            <a href="../verPerfil.html" class="perfil-row">
                <img src="../img/img_definicoes.png" alt="Definições" class="perfil-row-img">
                <span class="perfil-row-text">Definições de conta</span>
            </a>

            <a href="../login.html" class="perfil-logout-row">
                <img src="../img/img_sair.png" alt="Sair" class="perfil-back-img">
                <span class="perfil-logout-text">Log out</span>
            </a>

            <button type="button" class="perfil-voltar-btn">
                Voltar
            </button>
        </div>
    </div>
</div>

  <script src="js/registarProfessor.js"></script>

</body>
</html>
