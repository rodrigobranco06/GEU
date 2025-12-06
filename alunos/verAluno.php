<?php
include '../db.php';
include '../utils.php';

// 1. Obter ID do aluno via GET
$idAluno = isset($_GET['id_aluno']) ? (int)$_GET['id_aluno'] : 0;

if ($idAluno <= 0) {
    die('ID de aluno inválido.');
}

$conexao = estabelecerConexao();

// 2. Query para buscar TODOS os dados do aluno e relações
$sql = "SELECT 
            a.*,
            u.username, u.password_hash,
            n.nacionalidade_desc,
            c.curso_desc,
            e.escola_desc,
            t.nome AS nome_turma,
            -- Dados do Estágio (se existir)
            pe.id_pedido_estagio,
            pe.estado_pedido,
            emp.id_empresa, 
            emp.nome AS nome_empresa,
            p.nome AS nome_professor
        FROM aluno a
        LEFT JOIN utilizador u ON a.utilizador_id = u.id_utilizador
        LEFT JOIN nacionalidade n ON a.nacionalidade_id = n.id_nacionalidade
        LEFT JOIN curso c ON a.curso_id = c.id_curso
        LEFT JOIN escola e ON a.escola_id = e.id_escola
        LEFT JOIN turma t ON a.turma_id = t.id_turma
        -- Join para buscar info do estágio
        LEFT JOIN pedido_estagio pe ON pe.aluno_id = a.id_aluno
        LEFT JOIN empresa emp ON pe.empresa_id = emp.id_empresa
        LEFT JOIN professor p ON pe.professor_id = p.id_professor
        WHERE a.id_aluno = :id";

$stmt = $conexao->prepare($sql);
$stmt->execute([':id' => $idAluno]);
$aluno = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$aluno) {
    die("Aluno não encontrado.");
}

// 3. Formatar Data de Nascimento (YYYY-MM-DD -> DD/MM/YYYY)
$dataNascFormatada = '';
if (!empty($aluno['data_nascimento'])) {
    $dataNascFormatada = date('d/m/Y', strtotime($aluno['data_nascimento']));
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>GEU — Ver aluno</title>
    <link rel="stylesheet" href="css/verAluno.css" />
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

        <section class="content-grid">
            <form class="form-aluno">

                <div class="form-group">
                    <label for="codigo">Código Aluno</label>
                    <input id="codigo" type="text" value="<?= htmlspecialchars($aluno['username'] ?? '') ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input id="password" type="text" value="<?= htmlspecialchars($aluno['password_hash'] ?? '') ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="nome">Nome Aluno</label>
                    <input id="nome" type="text" value="<?= htmlspecialchars($aluno['nome'] ?? '') ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="dataNascimento">Data nascimento</label>
                    <input id="dataNascimento" type="text" value="<?= htmlspecialchars($dataNascFormatada) ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="sexo">Sexo</label>
                    <div class="select-wrapper">
                        <select id="sexo" disabled>
                            <option><?= htmlspecialchars($aluno['sexo'] ?? 'Não definido') ?></option>
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
                    <input id="nif" type="text" value="<?= htmlspecialchars($aluno['nif'] ?? '') ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="cc">Número CC</label>
                    <input id="cc" type="text" value="<?= htmlspecialchars($aluno['numero_cc'] ?? '') ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="curso">Curso</label>
                    <input id="curso" type="text" value="<?= htmlspecialchars($aluno['curso_desc'] ?? '') ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="turmaId">Turma</label>
                    <div class="select-wrapper">
                        <select id="turmaId" disabled>
                            <option><?= htmlspecialchars($aluno['nome_turma'] ?? 'Sem Turma') ?></option>
                        </select>
                        <span class="chevron">▾</span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="anoCurricular">Ano curricular</label>
                    <input id="anoCurricular" type="text" value="<?= htmlspecialchars($aluno['ano_curricular'] ?? '') ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="situacaoAcademica">Situação académica</label>
                    <div class="select-wrapper">
                        <select id="situacaoAcademica" disabled>
                            <option><?= htmlspecialchars($aluno['situacao_academica'] ?? 'Ativo') ?></option>
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
                    <input id="emailInstitucional" type="text" value="<?= htmlspecialchars($aluno['email_institucional'] ?? '') ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="emailPessoal">Email pessoal</label>
                    <input id="emailPessoal" type="text" value="<?= htmlspecialchars($aluno['email_pessoal'] ?? '') ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="morada">Morada</label>
                    <input id="morada" type="text" value="<?= htmlspecialchars($aluno['morada'] ?? '') ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="cp">Código-Postal</label>
                    <input id="cp" type="text" value="<?= htmlspecialchars($aluno['codigo_postal'] ?? '') ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="cidade">Cidade</label>
                    <input id="cidade" type="text" value="<?= htmlspecialchars($aluno['cidade'] ?? '') ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="idEstagio">ID Pedido Estágio</label>
                    <input id="idEstagio" type="text" value="<?= htmlspecialchars($aluno['id_pedido_estagio'] ?? 'N/A') ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="profOrientador">Professor orientador</label>
                    <input id="profOrientador" type="text" value="<?= htmlspecialchars($aluno['nome_professor'] ?? 'Sem orientador') ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="idEmpresa">ID empresa</label>
                    <input id="idEmpresa" type="text" value="<?= htmlspecialchars($aluno['id_empresa'] ?? 'N/A') ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="nomeEmpresa">Nome empresa</label>
                    <input id="nomeEmpresa" type="text" value="<?= htmlspecialchars($aluno['nome_empresa'] ?? 'Sem empresa') ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="estadoEstagio">Estado estágio</label>
                    <input id="estadoEstagio" type="text" value="<?= htmlspecialchars($aluno['estado_pedido'] ?? 'Não iniciado') ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="cv">CV</label>
                    <input id="cv" type="text" value="<?= htmlspecialchars($aluno['cv'] ?? 'Sem CV') ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="linkedin">LinkedIn</label>
                    <input id="linkedin" type="text" value="<?= htmlspecialchars($aluno['linkedin'] ?? '') ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="portfolio">Portefólio (GitHub)</label>
                    <input id="portfolio" type="text" value="<?= htmlspecialchars($aluno['github'] ?? '') ?>" readonly>
                </div>

            </form>

            <aside class="side-panel">
                <div class="side-top">
                    <a href="editarAluno.php?id_aluno=<?= $idAluno ?>" class="btn-editar">Editar</a>
                    <a class="btn-voltar" href="index.php">Voltar</a>
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

                <button type="button" class="perfil-voltar-btn">
                    Voltar
                </button>
            </div>
        </div>
    </div>

    <script src="js/verAluno.js"></script>
</body>
</html>