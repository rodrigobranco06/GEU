<?php
include '../db.php';
include '../utils.php';

// Ir buscar o id da empresa vindo por GET
$idEmpresa = isset($_GET['id_empresa']) ? (int) $_GET['id_empresa'] : 0;

/**
 * Devolve uma empresa pelo id, ou false se não existir.
 */
function getEmpresaById($idEmpresa)
{
    $conexao = estabelecerConexao();

    // Query ajustada à estrutura do ScriptCriacaoTabelas_GEU.sql
    $sql = 'SELECT 
                e.*,
                u.password_hash,
                u.username,
                ra.ramo_atividade_desc,
                p.pais_desc,
                (SELECT COUNT(*) FROM pedido_estagio pe WHERE pe.empresa_id = e.id_empresa) as total_alunos_colocados
            FROM empresa e
            LEFT JOIN utilizador u
                ON e.utilizador_id = u.id_utilizador
            LEFT JOIN ramo_atividade ra
                ON e.ramo_atividade_id = ra.id_ramo_atividade
            LEFT JOIN pais p
                ON e.pais_id = p.id_pais
            WHERE e.id_empresa = :id_empresa';

    $prepare = $conexao->prepare($sql);
    $prepare->execute(['id_empresa' => $idEmpresa]);

    return $prepare->fetch(PDO::FETCH_ASSOC);
}

// Validar o ID
if ($idEmpresa <= 0) {
    die('ID de empresa inválido.');
}

// Obter dados da empresa
$empresa = getEmpresaById($idEmpresa);

if (!$empresa) {
    die('Empresa não encontrada.');
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>GEU — Ver empresa</title>
    <link rel="stylesheet" href="css/verEmpresa.css" />
</head>
<body>

    <header id="header">
        <div class="header-logo">
            <a href="../index.php">
                <img src="../img/Logo.png" alt="Gestão de Estágios Universitários">
            </a>
        </div>

        <nav class="nav-menu">
            <a href="../alunos/index.php" class="nav-link">Alunos</a>
            <a href="../professores/index.php" class="nav-link">Professores</a>
            <a href="index.php" class="nav-link active">Empresas</a>
            <a href="../index.php" class="nav-link">Turmas</a>

            <button class="btn-conta" id="btn-conta">
                <img src="../img/img_conta.png" alt="Conta">
            </button>
            <a href="../login.php" class="btn-sair">Sair</a>
        </nav>
    </header>

    <main id="main-content">

        <nav class="subtabs">
            <a href="index.php" class="subtab-link active">Ver Empresas</a>
            <a href="registarEmpresa.php" class="subtab-link">Registar nova empresa</a>
        </nav>

        <h2 class="empresa-titulo"><?= htmlspecialchars($empresa['nome'] ?? 'Empresa') ?>:</h2>

        <section class="content-grid">
            <form class="form-empresa">

                <div class="form-group">
                    <label for="codEmpresa">Código Empresa</label>
                    <input 
                        id="codEmpresa" 
                        type="text" 
                        value="<?= htmlspecialchars($empresa['id_empresa'] ?? '') ?>" 
                        readonly
                    >
                </div>

                <div class="form-group">
                    <label for="nomeEmpresa">Nome</label>
                    <input 
                        id="nomeEmpresa" 
                        type="text" 
                        value="<?= htmlspecialchars($empresa['nome'] ?? '') ?>" 
                        readonly
                    >
                </div>

                <div class="form-group">
                    <label for="nifEmpresa">NIF empresa</label>
                    <input 
                        id="nifEmpresa" 
                        type="text" 
                        value="<?= htmlspecialchars($empresa['nif'] ?? '') ?>" 
                        readonly
                    >
                </div>

                <div class="form-group">
                    <label for="ramo">Ramo de atividade</label>
                    <input 
                        id="ramo" 
                        type="text"
                        value="<?= htmlspecialchars($empresa['ramo_atividade_desc'] ?? 'Não definido') ?>" 
                        readonly
                    >
                </div>

                <div class="form-group">
                    <label for="morada">Morada</label>
                    <input 
                        id="morada" 
                        type="text"
                        value="<?= htmlspecialchars($empresa['morada'] ?? '') ?>" 
                        readonly
                    >
                </div>

                <div class="form-group">
                    <label for="cp">Código Postal</label>
                    <input 
                        id="cp" 
                        type="text" 
                        value="<?= htmlspecialchars($empresa['codigo_postal'] ?? '') ?>" 
                        readonly
                    >
                </div>

                <div class="form-group">
                    <label for="cidade">Cidade</label>
                    <input 
                        id="cidade" 
                        type="text" 
                        value="<?= htmlspecialchars($empresa['cidade'] ?? '') ?>" 
                        readonly
                    >
                </div>

                <div class="form-group">
                    <label for="pais">País</label>
                    <input 
                        id="pais" 
                        type="text" 
                        value="<?= htmlspecialchars($empresa['pais_desc'] ?? '') ?>" 
                        readonly
                    >
                </div>

                <div class="form-group">
                    <label for="telefone">Telefone</label>
                    <input 
                        id="telefone" 
                        type="text" 
                        value="<?= htmlspecialchars($empresa['telefone'] ?? '') ?>" 
                        readonly
                    >
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input 
                        id="email" 
                        type="text"
                        value="<?= htmlspecialchars($empresa['email'] ?? '') ?>" 
                        readonly
                    >
                </div>

                <div class="form-group">
                    <label for="website">Website</label>
                    <input 
                        id="website" 
                        type="text" 
                        value="<?= htmlspecialchars($empresa['website'] ?? '') ?>" 
                        readonly
                    >
                </div>

                <div class="form-group">
                    <label for="linkedin">LinkedIn</label>
                    <input 
                        id="linkedin" 
                        type="text"
                        value="<?= htmlspecialchars($empresa['linkedin'] ?? '') ?>" 
                        readonly
                    >
                </div>

                <div class="form-group">
                    <label for="contactoResp">Contacto Responsável</label>
                    <input 
                        id="contactoResp" 
                        type="text"
                        value="<?= htmlspecialchars($empresa['nome_responsavel'] ?? '') ?>" 
                        readonly
                    >
                </div>

                <div class="form-group">
                    <label for="cargoResp">Cargo responsável</label>
                    <input 
                        id="cargoResp" 
                        type="text"
                        value="<?= htmlspecialchars($empresa['cargo_responsavel'] ?? '') ?>" 
                        readonly
                    >
                </div>

                <div class="form-group">
                    <label for="emailResp">Email responsável</label>
                    <input 
                        id="emailResp" 
                        type="text"
                        value="<?= htmlspecialchars($empresa['email_responsavel'] ?? '') ?>" 
                        readonly
                    >
                </div>

                <div class="form-group">
                    <label for="telResp">Telefone responsável</label>
                    <input 
                        id="telResp" 
                        type="text"
                        value="<?= htmlspecialchars($empresa['telefone_responsavel'] ?? '') ?>" 
                        readonly
                    >
                </div>

                <div class="form-group">
                    <label for="user">Nome utilizador</label>
                    <input 
                        id="user" 
                        type="text" 
                        value="<?= htmlspecialchars($empresa['username'] ?? '') ?>" 
                        readonly
                    >
                </div>

                <div class="form-group">
                    <label for="password">Password (Hash)</label>
                    <input 
                        id="password" 
                        type="text"
                        value="<?= htmlspecialchars($empresa['password_hash'] ?? '') ?>" 
                        readonly
                    >
                </div>

                <div class="form-group">
                    <label for="numEstagios">Alunos Colocados</label>
                    <input 
                        id="numEstagios" 
                        type="text" 
                        value="<?= htmlspecialchars($empresa['total_alunos_colocados'] ?? '0') ?>" 
                        readonly
                    >
                </div>

            </form>

            <aside class="side-panel">
                <div class="side-top">
                    <a 
                        class="btn-editar" 
                        href="editarEmpresa.php?id_empresa=<?= urlencode($empresa['id_empresa']) ?>"
                    >
                        Editar
                    </a>
                    <a class="btn-voltar" href="index.php">
                        Voltar
                    </a>
                </div>

                <div class="side-image-wrapper">
                    <img src="../img/img_registarAluno.png" alt="Ilustração empresa">
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

    <script src="js/verEmpresa.js"></script>

</body>
</html>