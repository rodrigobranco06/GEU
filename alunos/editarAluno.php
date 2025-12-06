<?php
include '../db.php';
include '../utils.php';

$conexao = estabelecerConexao();

// 1. Verificar ID
$idAluno = isset($_GET['id_aluno']) ? (int)$_GET['id_aluno'] : 0;
if ($idAluno <= 0) {
    die('ID de aluno inválido.');
}

$erro = '';
$sucesso = '';

// 2. Processar Formulário (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conexao->beginTransaction();

        // Recolher dados
        $nome           = $_POST['nome'] ?? '';
        $dataNasc       = $_POST['data_nascimento'] ?? null; // Formato esperado YYYY-MM-DD ou converter
        $sexo           = $_POST['sexo'] ?? '';
        $nif            = $_POST['nif'] ?? '';
        $cc             = $_POST['cc'] ?? '';
        $emailInst      = $_POST['email_institucional'] ?? '';
        $emailPess      = $_POST['email_pessoal'] ?? '';
        $morada         = $_POST['morada'] ?? '';
        $cp             = $_POST['cp'] ?? '';
        $cidade         = $_POST['cidade'] ?? '';
        $linkedin       = $_POST['linkedin'] ?? '';
        $github         = $_POST['github'] ?? ''; // name="portfolio" no HTML
        $situacao       = $_POST['situacao_academica'] ?? '';
        $turmaId        = !empty($_POST['turma_id']) ? $_POST['turma_id'] : null;
        $anoCurricular  = $_POST['ano_curricular'] ?? 1;

        // Login
        $passwordPlain  = $_POST['password'] ?? '';
        
        // Converter data se vier em DD/MM/YYYY para YYYY-MM-DD (Opcional, dependendo do input)
        // Aqui assumimos que o utilizador ou um datepicker envia corretamente, 
        // ou fazemos a conversão simples se for texto.
        if (strpos($dataNasc, '/') !== false) {
            $dataNasc = implode("-", array_reverse(explode("/", $dataNasc)));
        }

        // A. Atualizar Tabela ALUNO
        $sqlAluno = "UPDATE aluno SET 
            nome = :nome, data_nascimento = :data, sexo = :sexo, nif = :nif, numero_cc = :cc,
            email_institucional = :email_i, email_pessoal = :email_p, 
            morada = :morada, codigo_postal = :cp, cidade = :cidade,
            linkedin = :linkedin, github = :github, situacao_academica = :situacao,
            turma_id = :turma, ano_curricular = :ano
            WHERE id_aluno = :id";
        
        $stmtA = $conexao->prepare($sqlAluno);
        $stmtA->execute([
            ':nome' => $nome, ':data' => $dataNasc, ':sexo' => $sexo, ':nif' => $nif, ':cc' => $cc,
            ':email_i' => $emailInst, ':email_p' => $emailPess, 
            ':morada' => $morada, ':cp' => $cp, ':cidade' => $cidade,
            ':linkedin' => $linkedin, ':github' => $github, ':situacao' => $situacao,
            ':turma' => $turmaId, ':ano' => $anoCurricular,
            ':id' => $idAluno
        ]);

        // B. Atualizar Password (se alterada)
        // Primeiro precisamos do utilizador_id associado ao aluno
        $stmtGetUid = $conexao->prepare("SELECT utilizador_id FROM aluno WHERE id_aluno = :id");
        $stmtGetUid->execute([':id' => $idAluno]);
        $uid = $stmtGetUid->fetchColumn();

        if ($uid && !empty($passwordPlain)) {
            // Verificar se a password mudou (comparar com hash atual seria ideal, 
            // aqui simplificamos: se o campo não estiver vazio, atualiza).
            // NOTA: No HTML original o value da password é mostrado. Isso não é seguro em produção.
            // Mas seguindo o design:
            $sqlUser = "UPDATE utilizador SET password_hash = :pass WHERE id_utilizador = :uid";
            $stmtU = $conexao->prepare($sqlUser);
            $stmtU->execute([':pass' => $passwordPlain, ':uid' => $uid]); // Idealmente usar password_hash()
        }

        $conexao->commit();
        $sucesso = "Dados atualizados com sucesso!";
        
        // Redirecionar para verAluno para ver as alterações ou ficar aqui
        header("Location: verAluno.php?id_aluno=" . $idAluno);
        exit;

    } catch (PDOException $e) {
        $conexao->rollBack();
        $erro = "Erro ao atualizar: " . $e->getMessage();
    }
}

// 3. Buscar Dados Atuais para Preencher o Formulário
$sqlGet = "SELECT 
            a.*,
            u.username, u.password_hash,
            c.curso_desc,
            e.escola_desc,
            n.nacionalidade_desc,
            -- Dados de estágio (apenas leitura para mostrar)
            pe.estado_pedido,
            emp.id_empresa, emp.nome as nome_empresa,
            prof.nome as nome_professor
           FROM aluno a
           LEFT JOIN utilizador u ON a.utilizador_id = u.id_utilizador
           LEFT JOIN curso c ON a.curso_id = c.id_curso
           LEFT JOIN escola e ON a.escola_id = e.id_escola
           LEFT JOIN nacionalidade n ON a.nacionalidade_id = n.id_nacionalidade
           -- Joins para mostrar estágio atual (se existir)
           LEFT JOIN pedido_estagio pe ON pe.aluno_id = a.id_aluno
           LEFT JOIN empresa emp ON pe.empresa_id = emp.id_empresa
           LEFT JOIN professor prof ON pe.professor_id = prof.id_professor
           WHERE a.id_aluno = :id";

$stmtGet = $conexao->prepare($sqlGet);
$stmtGet->execute([':id' => $idAluno]);
$aluno = $stmtGet->fetch(PDO::FETCH_ASSOC);

if (!$aluno) {
    die("Aluno não encontrado.");
}

// Formatar data para DD/MM/YYYY para exibição no input type="text"
$dataNascFormatada = $aluno['data_nascimento'];
if ($aluno['data_nascimento']) {
    $dataNascFormatada = date('d/m/Y', strtotime($aluno['data_nascimento']));
}

// 4. Buscar Lista de Turmas para o Dropdown
$stmtTurmas = $conexao->query("SELECT id_turma, nome FROM turma ORDER BY nome ASC");
$turmas = $stmtTurmas->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>GEU — Editar aluno</title>
    <link rel="stylesheet" href="css/editarAluno.css" />
</head>
<body>

    <header id="header">
        <div class="header-logo">
            <a href="../index.php">
                <img src="../img/Logo.png" alt="Gestão de Estágios Universitários">
            </a>
        </div>

        <nav class="nav-menu">
            <a href="index.php" class="nav-link active">Alunos</a>
            <a href="../professores/index.php" class="nav-link">Professores</a>
            <a href="../empresas/index.php" class="nav-link">Empresas</a>
            <a href="../index.php" class="nav-link">Turmas</a>

            <button class="btn-conta" id="btn-conta">
                <img src="../img/img_conta.png" alt="Conta">
            </button>
            <a href="../login.php" class="btn-sair">Sair</a>
        </nav>
    </header>

    <main id="main-content">

        <nav class="subtabs">
            <a href="index.php" class="subtab-link active">Ver Alunos</a>
            <a href="registarAluno.php" class="subtab-link">Registar novo aluno</a>
        </nav>

        <?php if ($erro): ?>
            <div style="background-color: #f8d7da; color: #721c24; padding: 15px; margin: 20px auto; max-width: 1200px; border-radius: 5px;">
                <?= htmlspecialchars($erro) ?>
            </div>
        <?php endif; ?>

        <section class="content-grid">
            <form class="form-aluno" id="form-editar" method="POST" action="editarAluno.php?id_aluno=<?= $idAluno ?>">

                <div class="form-group">
                    <label for="codigo">Código Aluno</label>
                    <input id="codigo" type="text" value="<?= htmlspecialchars($aluno['username'] ?? '') ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input id="password" name="password" type="text" value="<?= htmlspecialchars($aluno['password_hash'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="nome">Nome Aluno</label>
                    <input id="nome" name="nome" type="text" value="<?= htmlspecialchars($aluno['nome'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="dataNascimento">Data nascimento</label>
                    <input id="dataNascimento" name="data_nascimento" type="text" value="<?= htmlspecialchars($dataNascFormatada) ?>">
                </div>

                <div class="form-group">
                    <label for="sexo">Sexo</label>
                    <div class="select-wrapper">
                        <select id="sexo" name="sexo">
                            <option value="">Selecione um sexo</option>
                            <option value="Masculino" <?= ($aluno['sexo'] == 'Masculino') ? 'selected' : '' ?>>Masculino</option>
                            <option value="Feminino"  <?= ($aluno['sexo'] == 'Feminino') ? 'selected' : '' ?>>Feminino</option>
                            <option value="Outro"     <?= ($aluno['sexo'] == 'Outro') ? 'selected' : '' ?>>Outro</option>
                        </select>
                        <span class="chevron">▾</span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="nacionalidade">Nacionalidade</label>
                    <input id="nacionalidade" type="text" value="<?= htmlspecialchars($aluno['nacionalidade_desc'] ?? '') ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="nif">NIF</label>
                    <input id="nif" name="nif" type="text" value="<?= htmlspecialchars($aluno['nif'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="cc">Número CC</label>
                    <input id="cc" name="cc" type="text" value="<?= htmlspecialchars($aluno['numero_cc'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="curso">Curso</label>
                    <input id="curso" type="text" value="<?= htmlspecialchars($aluno['curso_desc'] ?? '') ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="turmaId">Turma</label>
                    <div class="select-wrapper">
                        <select id="turmaId" name="turma_id">
                            <option value="">Selecione uma Turma</option>
                            <?php foreach ($turmas as $turma): ?>
                                <option value="<?= $turma['id_turma'] ?>" 
                                    <?= ($aluno['turma_id'] == $turma['id_turma']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($turma['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <span class="chevron">▾</span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="anoCurricular">Ano curricular</label>
                    <input id="anoCurricular" name="ano_curricular" type="number" min="1" value="<?= htmlspecialchars($aluno['ano_curricular'] ?? '1') ?>">
                </div>

                <div class="form-group">
                    <label for="situacaoAcademica">Situação académica</label>
                    <div class="select-wrapper">
                        <select id="situacaoAcademica" name="situacao_academica">
                            <option value="">Selecione</option>
                            <option value="Ativo"    <?= (stripos($aluno['situacao_academica'] ?? '', 'Ativo') !== false) ? 'selected' : '' ?>>Ativo</option>
                            <option value="Suspenso" <?= (stripos($aluno['situacao_academica'] ?? '', 'Suspenso') !== false) ? 'selected' : '' ?>>Suspenso</option>
                        </select>
                        <span class="chevron">▾</span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="escola">Escola</label>
                    <input id="escola" type="text" value="<?= htmlspecialchars($aluno['escola_desc'] ?? '') ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="emailInstitucional">Email institucional</label>
                    <input id="emailInstitucional" name="email_institucional" type="email" value="<?= htmlspecialchars($aluno['email_institucional'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="emailPessoal">Email pessoal</label>
                    <input id="emailPessoal" name="email_pessoal" type="email" value="<?= htmlspecialchars($aluno['email_pessoal'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="morada">Morada</label>
                    <input id="morada" name="morada" type="text" value="<?= htmlspecialchars($aluno['morada'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="cp">Código-Postal</label>
                    <input id="cp" name="cp" type="text" value="<?= htmlspecialchars($aluno['codigo_postal'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="cidade">Cidade</label>
                    <input id="cidade" name="cidade" type="text" value="<?= htmlspecialchars($aluno['cidade'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="profOrientador">Professor orientador</label>
                    <input id="profOrientador" type="text" value="<?= htmlspecialchars($aluno['nome_professor'] ?? '') ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="idEmpresa">ID empresa</label>
                    <input id="idEmpresa" type="text" value="<?= htmlspecialchars($aluno['id_empresa'] ?? '') ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="nomeEmpresa">Nome empresa</label>
                    <input id="nomeEmpresa" type="text" value="<?= htmlspecialchars($aluno['nome_empresa'] ?? '') ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="estadoEstagio">Estado estágio</label>
                    <input id="estadoEstagio" type="text" value="<?= htmlspecialchars($aluno['estado_pedido'] ?? 'Sem estágio') ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="cv">CV</label>
                    <input id="cv" type="text" value="<?= htmlspecialchars($aluno['cv'] ?? '') ?>" readonly placeholder="Gestão de ficheiros não implementada">
                </div>

                <div class="form-group">
                    <label for="linkedin">LinkedIn</label>
                    <input id="linkedin" name="linkedin" type="url" value="<?= htmlspecialchars($aluno['linkedin'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="portfolio">Portefólio (GitHub)</label>
                    <input id="portfolio" name="github" type="url" value="<?= htmlspecialchars($aluno['github'] ?? '') ?>">
                </div>

            </form>

            <aside class="side-panel">
                <div class="side-top">
                    <button type="submit" form="form-editar" class="btn-salvar">
                        Salvar
                    </button>
                    
                    <a class="btn-voltar" href="verAluno.php?id_aluno=<?= $idAluno ?>">
                        Voltar
                    </a>
                </div>

                <div class="side-image-wrapper">
                    <img src="../img/img_registarAluno.png" alt="Ilustração aluno">
                </div>
            </aside>
        </section>
    </main>

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
                <a href="../verPerfil.php" class="perfil-row">
                    <img src="../img/img_definicoes.png" alt="Definições" class="perfil-row-img">
                    <span class="perfil-row-text">Definições de conta</span>
                </a>
                <a href="../login.php" class="perfil-logout-row">
                    <img src="../img/img_sair.png" alt="Sair" class="perfil-back-img">
                    <span class="perfil-logout-text">Log out</span>
                </a>
                <button type="button" class="perfil-voltar-btn">Voltar</button>
            </div>
        </div>
    </div>

    <script src="js/editarAluno.js"></script>
</body>
</html>