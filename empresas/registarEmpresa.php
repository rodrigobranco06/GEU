<?php
include '../db.php';
include '../utils.php';

$mensagem = '';
$erro = '';

// Verifica se o formulário foi submetido
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conexao = estabelecerConexao();

    // 1. Capturar dados do formulário
    // Nota: O "Código Empresa" (ID) geralmente é auto-incrementado pela BD, 
    // por isso ignoramos o input manual ou usamos apenas se a BD não for auto-increment.
    // Aqui assumo que a BD gera o ID automaticamente.
    
    $nome           = $_POST['nome'] ?? '';
    $nif            = $_POST['nif'] ?? '';
    $ramo           = $_POST['ramo'] ?? '';
    $morada         = $_POST['morada'] ?? '';
    $cp             = $_POST['cp'] ?? '';
    $cidade         = $_POST['cidade'] ?? '';
    $pais           = $_POST['pais'] ?? '';
    $telefone       = $_POST['telefone'] ?? '';
    $email          = $_POST['email'] ?? '';
    $website        = $_POST['website'] ?? '';
    $linkedin       = $_POST['linkedin'] ?? '';
    
    // Dados do Responsável
    $respNome       = $_POST['resp_nome'] ?? '';
    $respCargo      = $_POST['resp_cargo'] ?? '';
    $respEmail      = $_POST['resp_email'] ?? '';
    $respTel        = $_POST['resp_tel'] ?? '';
    
    // Dados de Login
    $username       = $_POST['username'] ?? '';
    $password       = $_POST['password'] ?? ''; // Em produção, usar password_hash()
    
    $numEstagios    = $_POST['num_estagios'] ?? 0;

    // Validação simples
    if (empty($nome) || empty($nif) || empty($username) || empty($password)) {
        $erro = "Por favor, preencha os campos obrigatórios (Nome, NIF, Utilizador, Password).";
    } else {
        try {
            // Iniciar transação (para garantir que insere nas duas tabelas ou em nenhuma)
            $conexao->beginTransaction();

            // 2. Inserir Utilizador
            // Tipo de utilizador fixo como 'empresa' (ajustar conforme a tua BD)
            $sqlUser = "INSERT INTO utilizador (username, password_hash, tipo_utilizador) VALUES (:user, :pass, 'empresa')";
            $stmtUser = $conexao->prepare($sqlUser);
            $stmtUser->execute([
                ':user' => $username,
                ':pass' => $password // Idealmente: password_hash($password, PASSWORD_DEFAULT)
            ]);
            
            $idUtilizador = $conexao->lastInsertId();

            // 3. Inserir Empresa
            $sqlEmpresa = "INSERT INTO empresa (
                utilizador_id, nome, nif, ramo_atividade, morada, codigo_postal, 
                cidade, pais, telefone, email, website, linkedin, 
                responsavel_nome, responsavel_cargo, responsavel_email, responsavel_telefone, 
                numero_estagios
            ) VALUES (
                :uid, :nome, :nif, :ramo, :morada, :cp, 
                :cidade, :pais, :tel, :email, :web, :linkedin, 
                :r_nome, :r_cargo, :r_email, :r_tel, 
                :n_estagios
            )";

            $stmtEmp = $conexao->prepare($sqlEmpresa);
            $stmtEmp->execute([
                ':uid'          => $idUtilizador,
                ':nome'         => $nome,
                ':nif'          => $nif,
                ':ramo'         => $ramo,
                ':morada'       => $morada,
                ':cp'           => $cp,
                ':cidade'       => $cidade,
                ':pais'         => $pais,
                ':tel'          => $telefone,
                ':email'        => $email,
                ':web'          => $website,
                ':linkedin'     => $linkedin,
                ':r_nome'       => $respNome,
                ':r_cargo'      => $respCargo,
                ':r_email'      => $respEmail,
                ':r_tel'        => $respTel,
                ':n_estagios'   => $numEstagios
            ]);

            // Confirmar transação
            $conexao->commit();
            
            // Redirecionar para a lista ou mostrar sucesso
            header("Location: index.php");
            exit;

        } catch (PDOException $e) {
            $conexao->rollBack();
            $erro = "Erro ao registar empresa: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>GEU — Registar nova empresa</title>
    <link rel="stylesheet" href="css/registarEmpresa.css" />
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
            <a href="index.php" class="subtab-link">Ver Empresas</a>
            <a href="registarEmpresa.php" class="subtab-link active">Registar nova empresa</a>
        </nav>

        <?php if ($erro): ?>
            <div style="background-color: #f8d7da; color: #721c24; padding: 10px; margin: 20px; border-radius: 5px;">
                <?= htmlspecialchars($erro) ?>
            </div>
        <?php endif; ?>

        <section class="content-grid">
            <form class="form-empresa" id="form-registar" method="POST" action="registarEmpresa.php">

                <div class="form-group">
                    <label for="codEmpresa">Código Empresa</label>
                    <input id="codEmpresa" type="text" placeholder="Gerado automaticamente" readonly>
                </div>

                <div class="form-group">
                    <label for="nomeEmpresa">Nome</label>
                    <input id="nomeEmpresa" name="nome" type="text" required>
                </div>

                <div class="form-group">
                    <label for="nifEmpresa">NIF empresa</label>
                    <input id="nifEmpresa" name="nif" type="text" required>
                </div>

                <div class="form-group">
                    <label for="ramo">Ramo de atividade</label>
                    <input id="ramo" name="ramo" type="text">
                </div>

                <div class="form-group">
                    <label for="morada">Morada</label>
                    <input id="morada" name="morada" type="text">
                </div>

                <div class="form-group">
                    <label for="cp">Código Postal</label>
                    <input id="cp" name="cp" type="text">
                </div>

                <div class="form-group">
                    <label for="cidade">Cidade</label>
                    <input id="cidade" name="cidade" type="text">
                </div>

                <div class="form-group">
                    <label for="pais">País</label>
                    <input id="pais" name="pais" type="text">
                </div>

                <div class="form-group">
                    <label for="telefone">Telefone</label>
                    <input id="telefone" name="telefone" type="text">
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email">
                </div>

                <div class="form-group">
                    <label for="website">Website</label>
                    <input id="website" name="website" type="text">
                </div>

                <div class="form-group">
                    <label for="linkedin">LinkedIn</label>
                    <input id="linkedin" name="linkedin" type="text">
                </div>

                <div class="form-group">
                    <label for="contactoResp">Contacto Responsável</label>
                    <input id="contactoResp" name="resp_nome" type="text">
                </div>

                <div class="form-group">
                    <label for="cargoResp">Cargo responsável</label>
                    <input id="cargoResp" name="resp_cargo" type="text">
                </div>

                <div class="form-group">
                    <label for="emailResp">Email responsável</label>
                    <input id="emailResp" name="resp_email" type="email">
                </div>

                <div class="form-group">
                    <label for="telResp">Telefone responsável</label>
                    <input id="telResp" name="resp_tel" type="text">
                </div>

                <div class="form-group">
                    <label for="user">Nome utilizador</label>
                    <input id="user" name="username" type="text" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" required>
                </div>

                <div class="form-group">
                    <label for="numEstagios">Número de estágios</label>
                    <input id="numEstagios" name="num_estagios" type="number" min="0">
                </div>

            </form>

            <aside class="side-panel">
                <div class="side-top">
                    <button class="btn-salvar" type="submit" form="form-registar">
                        Salvar
                    </button>
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

  <script src="js/registarEmpresa.js"></script>

</body>
</html>