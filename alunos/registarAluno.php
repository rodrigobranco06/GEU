<?php
include '../db.php';
include '../utils.php';

$conexao = estabelecerConexao();
$erro = '';
$sucesso = '';

/**
 * Função auxiliar para lidar com inputs de texto que são chaves estrangeiras.
 * Ex: Se o user escrever "Portuguesa", verifica se existe na tabela 'nacionalidade'.
 * Se sim, devolve o ID. Se não, cria e devolve o novo ID.
 */
function obterOuCriarID($conexao, $tabela, $colunaDesc, $colunaId, $valor) {
    if (empty($valor)) return null;
    
    // 1. Verificar se existe
    $sqlCheck = "SELECT $colunaId FROM $tabela WHERE $colunaDesc = :valor LIMIT 1";
    $stmt = $conexao->prepare($sqlCheck);
    $stmt->execute([':valor' => $valor]);
    $id = $stmt->fetchColumn();

    if ($id) {
        return $id;
    } else {
        // 2. Criar novo se não existir
        $sqlInsert = "INSERT INTO $tabela ($colunaDesc) VALUES (:valor)";
        $stmtInsert = $conexao->prepare($sqlInsert);
        $stmtInsert->execute([':valor' => $valor]);
        return $conexao->lastInsertId();
    }
}

// Buscar turmas para preencher o Select
$stmtTurmas = $conexao->query("SELECT id_turma, nome FROM turma ORDER BY nome ASC");
$listaTurmas = $stmtTurmas->fetchAll(PDO::FETCH_ASSOC);

// --- PROCESSAR FORMULÁRIO (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conexao->beginTransaction();

        // 1. Dados de Login (Utilizador)
        $codigo   = $_POST['codigo'] ?? ''; // Será o username
        $password = $_POST['password'] ?? '';

        if (empty($codigo) || empty($password)) {
            throw new Exception("O Código de Aluno e a Password são obrigatórios.");
        }

        // Criar Utilizador
        $sqlUser = "INSERT INTO utilizador (username, password_hash, tipo_utilizador, estado_conta, data_criacao) 
                    VALUES (:user, :pass, 'aluno', 'ativo', NOW())";
        $stmtUser = $conexao->prepare($sqlUser);
        $stmtUser->execute([
            ':user' => $codigo,
            ':pass' => $password // Idealmente: password_hash($password, PASSWORD_DEFAULT)
        ]);
        $idUtilizador = $conexao->lastInsertId();

        // 2. Resolver IDs para campos de texto (FKs)
        $nomeNacionalidade = $_POST['nacionalidade'] ?? '';
        $idNacionalidade = obterOuCriarID($conexao, 'nacionalidade', 'nacionalidade_desc', 'id_nacionalidade', $nomeNacionalidade);

        $nomeCurso = $_POST['curso'] ?? '';
        $idCurso = obterOuCriarID($conexao, 'curso', 'curso_desc', 'id_curso', $nomeCurso);

        $nomeEscola = $_POST['escola'] ?? '';
        $idEscola = obterOuCriarID($conexao, 'escola', 'escola_desc', 'id_escola', $nomeEscola);

        // 3. Upload do CV (Simples)
        $nomeCV = '';
        if (isset($_FILES['cv']) && $_FILES['cv']['error'] === UPLOAD_ERR_OK) {
            $nomeCV = $_FILES['cv']['name'];
            // Para mover o ficheiro real, descomentar e garantir permissões na pasta:
            // move_uploaded_file($_FILES['cv']['tmp_name'], "../uploads/" . $nomeCV);
        }

        // 4. Inserir Aluno
        $sqlAluno = "INSERT INTO aluno (
            nome, data_nascimento, sexo, nif, numero_cc, 
            email_institucional, email_pessoal, morada, codigo_postal, cidade,
            situacao_academica, cv, linkedin, github, 
            utilizador_id, nacionalidade_id, curso_id, escola_id, turma_id, ano_curricular
        ) VALUES (
            :nome, :dataNasc, :sexo, :nif, :cc,
            :emailInst, :emailPes, :morada, :cp, :cidade,
            :situacao, :cv, :linkedin, :github,
            :uid, :nid, :cid, :eid, :tid, :ano
        )";

        $stmtAluno = $conexao->prepare($sqlAluno);
        $stmtAluno->execute([
            ':nome'         => $_POST['nome'] ?? '',
            ':dataNasc'     => $_POST['data_nascimento'] ?? null,
            ':sexo'         => $_POST['sexo'] ?? '',
            ':nif'          => $_POST['nif'] ?? '',
            ':cc'           => $_POST['cc'] ?? '',
            ':emailInst'    => $_POST['email_institucional'] ?? '',
            ':emailPes'     => $_POST['email_pessoal'] ?? '',
            ':morada'       => $_POST['morada'] ?? '',
            ':cp'           => $_POST['cp'] ?? '',
            ':cidade'       => $_POST['cidade'] ?? '',
            ':situacao'     => $_POST['situacao_academica'] ?? 'Ativo',
            ':cv'           => $nomeCV,
            ':linkedin'     => $_POST['linkedin'] ?? '',
            ':github'       => $_POST['github'] ?? '', // name="portfolio" no HTML
            ':uid'          => $idUtilizador,
            ':nid'          => $idNacionalidade,
            ':cid'          => $idCurso,
            ':eid'          => $idEscola,
            ':tid'          => !empty($_POST['turma_id']) ? $_POST['turma_id'] : null,
            ':ano'          => $_POST['ano_curricular'] ?? 1
        ]);

        $conexao->commit();
        $sucesso = "Aluno registado com sucesso!";
        // Opcional: Redirecionar
        // header("Location: index.php"); exit;

    } catch (Exception $e) {
        $conexao->rollBack();
        $erro = "Erro ao registar: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>GEU — Registar novo aluno</title>
    <link rel="stylesheet" href="css/registarAluno.css" />
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
            <a href="index.php" class="subtab-link">Ver Alunos</a>
            <a href="registarAluno.php" class="subtab-link active">Registar novo aluno</a>
        </nav>

        <?php if ($erro): ?>
            <div style="background-color: #f8d7da; color: #721c24; padding: 15px; margin: 20px; border-radius: 5px;">
                <?= htmlspecialchars($erro) ?>
            </div>
        <?php endif; ?>
        <?php if ($sucesso): ?>
            <div style="background-color: #d4edda; color: #155724; padding: 15px; margin: 20px; border-radius: 5px;">
                <?= htmlspecialchars($sucesso) ?>
            </div>
        <?php endif; ?>

        <section class="content-grid">
            <form class="form-aluno" id="form-registar" method="POST" action="registarAluno.php" enctype="multipart/form-data">

                <div class="form-group">
                    <label for="codigo">Código Aluno (Username)</label>
                    <input id="codigo" name="codigo" type="text" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" required>
                </div>

                <div class="form-group">
                    <label for="nome">Nome Aluno</label>
                    <input id="nome" name="nome" type="text" required>
                </div>

                <div class="form-group">
                    <label for="dataNascimento">Data nascimento</label>
                    <input id="dataNascimento" name="data_nascimento" type="date">
                </div>

                <div class="form-group">
                    <label for="sexo">Sexo</label>
                    <div class="select-wrapper">
                        <select id="sexo" name="sexo">
                            <option value="">Selecione um sexo</option>
                            <option value="Masculino">Masculino</option>
                            <option value="Feminino">Feminino</option>
                            <option value="Outro">Outro</option>
                        </select>
                        <span class="chevron">▾</span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="nacionalidade">Nacionalidade</label>
                    <input id="nacionalidade" name="nacionalidade" type="text" placeholder="Ex: Portuguesa">
                </div>

                <div class="form-group">
                    <label for="nif">NIF</label>
                    <input id="nif" name="nif" type="text">
                </div>

                <div class="form-group">
                    <label for="cc">Número CC</label>
                    <input id="cc" name="cc" type="text">
                </div>

                <div class="form-group">
                    <label for="curso">Curso</label>
                    <input id="curso" name="curso" type="text" placeholder="Ex: TESP de Programação">
                </div>

                <div class="form-group">
                    <label for="turmaId">Turma</label>
                    <div class="select-wrapper">
                        <select id="turmaId" name="turma_id">
                            <option value="">Selecione uma Turma</option>
                            <?php foreach ($listaTurmas as $t): ?>
                                <option value="<?= $t['id_turma'] ?>">
                                    <?= htmlspecialchars($t['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <span class="chevron">▾</span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="anoCurricular">Ano curricular</label>
                    <input id="anoCurricular" name="ano_curricular" type="number" min="1" value="1">
                </div>

                <div class="form-group">
                    <label for="situacaoAcademica">Situação académica</label>
                    <div class="select-wrapper">
                        <select id="situacaoAcademica" name="situacao_academica">
                            <option value="Ativo">Ativo</option>
                            <option value="Suspenso">Suspenso</option>
                        </select>
                        <span class="chevron">▾</span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="escola">Escola</label>
                    <input id="escola" name="escola" type="text" placeholder="Ex: ESGTS">
                </div>

                <div class="form-group">
                    <label for="emailInstitucional">Email institucional</label>
                    <input id="emailInstitucional" name="email_institucional" type="email">
                </div>

                <div class="form-group">
                    <label for="emailPessoal">Email pessoal</label>
                    <input id="emailPessoal" name="email_pessoal" type="email">
                </div>

                <div class="form-group">
                    <label for="morada">Morada</label>
                    <input id="morada" name="morada" type="text">
                </div>

                <div class="form-group">
                    <label for="cp">Código-Postal</label>
                    <input id="cp" name="cp" type="text">
                </div>

                <div class="form-group">
                    <label for="cidade">Cidade</label>
                    <input id="cidade" name="cidade" type="text">
                </div>

                <div class="form-group">
                    <label for="idEstagio">ID estágio (Desabilitado)</label>
                    <input id="idEstagio" type="text" disabled placeholder="Gerado posteriormente">
                </div>

                <div class="form-group">
                    <label for="profOrientador">Professor orientador</label>
                    <input id="profOrientador" type="text" disabled placeholder="Associado no estágio">
                </div>

                <div class="form-group">
                    <label for="idEmpresa">ID empresa</label>
                    <input id="idEmpresa" type="text" disabled placeholder="Associado no estágio">
                </div>

                <div class="form-group">
                    <label for="nomeEmpresa">Nome empresa</label>
                    <input id="nomeEmpresa" type="text" disabled placeholder="Associado no estágio">
                </div>

                <div class="form-group">
                    <label for="estadoEstagio">Estado estágio</label>
                    <input id="estadoEstagio" type="text" disabled placeholder="Não iniciado">
                </div>

                <div class="form-group">
                    <label for="cv">CV (PDF/DOC)</label>
                    <input id="cv" name="cv" type="file" accept=".pdf,.doc,.docx">
                </div>

                <div class="form-group">
                    <label for="linkedin">LinkedIn</label>
                    <input id="linkedin" name="linkedin" type="url">
                </div>

                <div class="form-group">
                    <label for="portfolio">Portefólio (GitHub)</label>
                    <input id="portfolio" name="github" type="url">
                </div>

            </form>

            <aside class="side-panel">
                <div class="side-top">
                    <button class="btn-salvar" type="submit" form="form-registar">
                        Salvar
                    </button>
                </div>

                <div class="side-image-wrapper">
                    <img src="../img/img_registarAluno.png" alt="Ilustração registar aluno">
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

                <button type="button" class="perfil-voltar-btn">
                    Voltar
                </button>
            </div>
        </div>
    </div>

    <script src="js/registarAluno.js"></script>

</body>
</html>