<?php
include '../db.php';
include '../utils.php';

// 1) Determinar o ID do professor (GET ou POST)
$idProfessor = 0;

if (isset($_GET['id_professor'])) {
    $idProfessor = $_GET['id_professor'];
} 

/**
 * Lê o professor + joins necessários
 */
function getProfessorById($idProfessor)
{
    $conexao = estabelecerConexao();

    // Prepared statement
    $prepare = $conexao->prepare('SELECT 
            p.*,
            u.password_hash,
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
        WHERE p.id_professor = :id_professor');

    $prepare->execute([
        'id_professor' => $idProfessor
    ]);

    return $prepare->fetch(PDO::FETCH_ASSOC);
}


/**
 * Faz UPDATE aos campos simples do professor
 */
function updateProfessor($idProfessor, array $dados)
{
    $conexao = estabelecerConexao();


    $prepare = $conexao->prepare('UPDATE professor
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
            cidade              = :cidade
        WHERE id_professor     = :id_professor');

    $prepare->execute([
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
        'id_professor'       => $idProfessor,
    ]);
}

// Atualizar a password na tabela utilizador 

function updatePasswordUtilizador($idUtilizador, $novaPassword)
{
    if (!$idUtilizador || $novaPassword === '') {
        return;
    }

    $conexao = estabelecerConexao();

    $prepare = $conexao->prepare('UPDATE utilizador
        SET password_hash = :pwd
        WHERE id_utilizador = :id_utilizador');

    $prepare->execute([
        'pwd'           => $novaPassword,
        'id_utilizador' => $idUtilizador,
    ]);
}

// Validar ID
if ($idProfessor <= 0) {
    die('ID de professor inválido.');
}

// Ler professor (antes de eventual POST, para ter utilizador_id, etc.)
$professor = getProfessorById($idProfessor);

if (!$professor) {
    die('Professor não encontrado.');
}

// Se for POST, tratar dos dados e fazer UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Ler/limpar dados do formulário
    $nome               = trim($_POST['nomeProf'] ?? '');
    $dataNascimentoDb = $_POST['data_nascimento'] ?? null;
    $sexo               = trim($_POST['sexo'] ?? ''); 
    $nif                = trim($_POST['nif'] ?? '');
    $numeroCc           = trim($_POST['cc'] ?? '');
    $emailInst          = trim($_POST['emailInstitucional'] ?? '');
    $emailPessoal       = trim($_POST['emailPessoal'] ?? '');
    $morada             = trim($_POST['morada'] ?? '');
    $codigoPostal       = trim($_POST['cp'] ?? '');
    $cidade             = trim($_POST['cidade'] ?? '');
    $passwordForm       = trim($_POST['password'] ?? '');

    

    // Preparar array para update
    $dadosUpdate = [
        'nome'               => $nome,
        'data_nascimento'    => $dataNascimentoDb,
        'sexo'               => $sexo,       
        'nif'                => $nif,
        'numero_cc'          => $numeroCc,
        'email_institucional'=> $emailInst,
        'email_pessoal'      => $emailPessoal,
        'morada'             => $morada,
        'codigo_postal'      => $codigoPostal,
        'cidade'             => $cidade,
    ];

    // UPDATE na tabela professor
    updateProfessor($idProfessor, $dadosUpdate);

    // Opcional: atualizar password no utilizador se tiver sido alterada
    if ($passwordForm !== '') {
        updatePasswordUtilizador($professor['utilizador_id'], $passwordForm);
    }

    // Redirecionar para a página de ver professor (padrão PRG: Post-Redirect-Get)
    header('Location: verProfessor.php?id_professor=' . urlencode($idProfessor));
    exit;
}

// 3) Se não for POST, chegamos aqui para mostrar o formulário preenchido

// Formatar data para DD/MM/YYYY para mostrar no formulário
$dataNascimentoFormatada = '';
if (!empty($professor['data_nascimento'])) {
    $dataNascimentoFormatada = date('d/m/Y', strtotime($professor['data_nascimento']));
}


$sexoDB = $professor['sexo'] ?? '';

?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>GEU — Editar professor</title>
    <link rel="stylesheet" href="css/editarProfessor.css" />
</head>
<body>

    <!-- ======= CABEÇALHO ======= -->
    <header id="header">
        <div class="header-logo">
            <a href="index.php">
                <img src="../img/Logo.png" alt="Gestão de Estágios Universitários">
            </a>
        </div>

        <nav class="nav-menu">
            <a href="../alunos/index.php" class="nav-link">Alunos</a>
            <a href="index.php" class="nav-link active">Professores</a>
            <a href="../empresas/index.php" class="nav-link">Empresas</a>
            <a href="../index.php" class="nav-link">Turmas</a>

            <button class="btn-conta" id="btn-conta">
                <img src="../img/img_conta.png" alt="Conta">
            </button>
            <a href="../login.php" class="btn-sair">Sair</a>
        </nav>
    </header>

    <!-- ======= CONTEÚDO PRINCIPAL ======= -->
    <main id="main-content">

        <!-- Subtabs -->
        <nav class="subtabs">
            <a href="index.php" class="subtab-link active">Ver Professores</a>
            <a href="registarProfessor.php" class="subtab-link">Registar novo professor</a>
        </nav>

        <section class="content-grid">
            <!-- Coluna esquerda: formulário EDITÁVEL -->
            <form class="form-professor"
                  method="post"
                  action="editarProfessor.php?id_professor=<?= urlencode($idProfessor) ?>">

                <!-- Hidden para garantir o id no POST -->
                <input
                    type="hidden"
                    name="id_professor"
                    value="<?= htmlspecialchars($idProfessor) ?>"
                >

                <div class="form-group">
                    <label for="codigoProf">Código Professor</label>
                    <input
                        id="codigoProf"
                        name="codigoProf"
                        type="text"
                        value="<?= htmlspecialchars($professor['id_professor'] ?? '') ?>"
                        readonly
                    >
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input
                        id="password"
                        name="password"
                        type="text"
                        value="<?= htmlspecialchars($professor['password_hash'] ?? '') ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="nomeProf">Nome Professor</label>
                    <input
                        id="nomeProf"
                        name="nomeProf"
                        type="text"
                        value="<?= htmlspecialchars($professor['nome'] ?? '') ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="dataNascimento">Data nascimento</label>
                    <input
                        id="dataNascimento"
                        name="data_nascimento"
                        type="date"
                        value="<?= htmlspecialchars($professor['data_nascimento'] ?? '') ?>"
                    >
                </div>




                <div class="form-group">
                    <label for="sexo">Sexo</label>
                    <div class="select-wrapper">
                        <select id="sexo" name="sexo">
                            <option value="">
                                Selecione um sexo
                            </option>
                            <option
                                value="Masculino"
                                <?= ($sexoDB === 'Masculino') ? 'selected' : '' ?>
                            >
                                Masculino
                            </option>
                            <option
                                value="Feminino"
                                <?= ($sexoDB === 'Feminino') ? 'selected' : '' ?>
                            >
                                Feminino
                            </option>
                            <option
                                value="Outro"
                                <?= ($sexoDB === 'Outro') ? 'selected' : '' ?>
                            >
                                Outro
                            </option>
                        </select>
                        <span class="chevron">▾</span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="nacionalidade">Nacionalidade</label>
                    <input
                        id="nacionalidade"
                        name="nacionalidade"
                        type="text"
                        value="<?= htmlspecialchars($professor['nacionalidade_desc'] ?? '') ?>"
                        readonly
                    >
                </div>

                <div class="form-group">
                    <label for="especializacao">Especialização</label>
                    <input
                        id="especializacao"
                        name="especializacao"
                        type="text"
                        value="<?= htmlspecialchars($professor['especializacao_desc'] ?? '') ?>"
                        readonly
                    >
                </div>

                <div class="form-group">
                    <label for="nif">NIF</label>
                    <input
                        id="nif"
                        name="nif"
                        type="text"
                        value="<?= htmlspecialchars($professor['nif'] ?? '') ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="cc">Número CC</label>
                    <input
                        id="cc"
                        name="cc"
                        type="text"
                        value="<?= htmlspecialchars($professor['numero_cc'] ?? '') ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="escola">Escola</label>
                    <input
                        id="escola"
                        name="escola"
                        type="text"
                        value="<?= htmlspecialchars($professor['escola_desc'] ?? '') ?>"
                        readonly
                    >
                </div>

                <div class="form-group">
                    <label for="emailInstitucional">Email institucional</label>
                    <input
                        id="emailInstitucional"
                        name="emailInstitucional"
                        type="email"
                        value="<?= htmlspecialchars($professor['email_institucional'] ?? '') ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="emailPessoal">Email pessoal</label>
                    <input
                        id="emailPessoal"
                        name="emailPessoal"
                        type="email"
                        value="<?= htmlspecialchars($professor['email_pessoal'] ?? '') ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="morada">Morada</label>
                    <input
                        id="morada"
                        name="morada"
                        type="text"
                        value="<?= htmlspecialchars($professor['morada'] ?? '') ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="cp">Código-Postal</label>
                    <input
                        id="cp"
                        name="cp"
                        type="text"
                        value="<?= htmlspecialchars($professor['codigo_postal'] ?? '') ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="cidade">Cidade</label>
                    <input
                        id="cidade"
                        name="cidade"
                        type="text"
                        value="<?= htmlspecialchars($professor['cidade'] ?? '') ?>"
                    >
                </div>

                <!-- Botões na própria <form> para o submit funcionar -->
                <div class="side-top side-top-inside-form">
                    <button class="btn-salvar" type="submit">
                        Salvar
                    </button>
                    <a
                        class="btn-voltar"
                        href="verProfessor.php?id_professor=<?= urlencode($idProfessor) ?>"
                    >
                        Voltar
                    </a>
                </div>

            </form>

            <!-- Coluna direita: imagem (sem botões, porque estão na form agora) -->
            <aside class="side-panel">
                <div class="side-image-wrapper">
                    <img src="../img/img_registarAluno.png" alt="Ilustração professor">
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

    <script src="js/editarProfessor.js"></script>
</body>
</html>
