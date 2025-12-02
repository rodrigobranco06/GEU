<?php

include 'db.php';
include 'utils.php';

// Testar estabelecerConexao()
   
    $conexao = estabelecerConexao();

    // show_var( $conexao, '$conexao');


    // fazer uma query para obter todas as turmas
    function getCursos()
    {
        $conexao = estabelecerConexao();
         
        $res = $conexao->query('SELECT id_curso, curso_desc FROM curso');

        return $res->fetchAll(PDO::FETCH_ASSOC);
    }

    function getTurmas()
    {
        $conexao = estabelecerConexao();
         
        $res = $conexao->query('SELECT 
            t.id_turma,
            t.nome AS turma_nome,
            p.nome AS professor_nome
        FROM turma t
        LEFT JOIN professor p ON p.id_professor = t.professor_id');

        return $res->fetchAll(PDO::FETCH_ASSOC);
    }

    function getProfessores() {
    $conexao = estabelecerConexao();
    $res = $conexao->query("
        SELECT id_professor, nome 
        FROM professor
        ORDER BY nome
    ");
    return $res->fetchAll(PDO::FETCH_ASSOC);
}




    $turmas = getTurmas();
    // show_var( $turmas , '$turmas');

    $cursos = getCursos();
    // show_var( $cursos , '$cursos');

    $professores = getProfessores();
    // show_var( $professores , '$professores');



?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Estágios Universitários - Turmas</title>
    <link rel="stylesheet" href="css/index.css">
</head>

<body>

    <!-- Cabeçalho -->
    <header id="header">
        <div class="header-logo">
            <a href="index.html">
                <img src="img/Logo.png" alt="Gestão de Estágios Universitários">
            </a>
        </div>

        <nav class="nav-menu">
            <a href="alunos/index.html" class="nav-link">Alunos</a>
            <a href="professores/index.html" class="nav-link">Professores</a>
            <a href="empresas/index.html" class="nav-link">Empresas</a>
            <a href="index.html" class="nav-link active">Turmas</a>

            <button id="btn-conta" class="btn-conta">
                <img src="img/img_conta.png" alt="Conta">
            </button>
            <a href="login.html" class="btn-sair">Sair</a>
        </nav>
    </header>

    <!-- Conteúdo principal -->
    <main id="main-content">
        <div class="main-header">
            <h2 class="titulo-pagina">Turmas</h2>
            <button class="btn-criar-turma">Criar Nova Turma</button>
        </div>

        <div class="turmas-container">
            <?php foreach ($turmas as $turma): ?>
                <a href="turma.php?id=<?= $turma['id_turma'] ?>" class="turma-link">
                    <div class="turma-card">
                        <h3 class="turma-nome"><?= htmlspecialchars($turma['turma_nome']) ?></h3>

                        <p class="turma-professor-label">Professor orientador</p>

                        <p class="turma-professor-nome <?= empty($turma['professor_nome']) ? 'indefinido' : '' ?>">
                            <?= $turma['professor_nome'] ?: 'Indefinido' ?>
                        </p>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>


    </main>

    <!-- MODAL CRIAR TURMA -->
    <div id="modal-criar-turma" class="modal-overlay" style="display:none;">
        <div class="modal-content-box">

            <div class="modal-flex">

                <form class="modal-form" action="adicionarTurma.php" method="POST">
                    <label>Código Turma:</label>
                    <input type="text" id="codigo-turma" name="codigo" readonly>

                    <label>Nome:</label>
                    <input type="text" id="nome-turma" name="nome">

                    <label>Ano incio:</label>
                    <input type="text" id="ano-inicio" name="ano_inicio">

                    <label>Ano fim:</label>
                    <input type="text" id="ano-fim" name="ano_fim">

                    <label>Ano curricular:</label>
                    <input type="text" id="ano-curricular" name="ano_curricular">

                    <label for="curso">Curso:</label>
                    <input list="lista-cursos" id="curso" name="curso_desc" placeholder="Escreve o curso...">

                    <datalist id="lista-cursos">
                        <?php foreach ($cursos as $curso): ?>
                            <option value="<?= htmlspecialchars($curso['curso_desc']) ?>">
                        <?php endforeach; ?>
                    </datalist>





                    <label>Código professor:</label>
                    <input list="lista-codigos" id="prof-codigo" name="professor_codigo" placeholder="Escreve o código...">

                    <datalist id="lista-codigos">
                        <?php foreach ($professores as $p): ?>
                            <option value="<?= htmlspecialchars($p['id_professor']) ?>">
                                <?= htmlspecialchars($p['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </datalist>


                    <label>Professor orientador:</label>
                    <input list="lista-nomes" id="prof-nome" name="professor_nome" placeholder="Escreve o nome...">

                    <datalist id="lista-nomes">
                        <?php foreach ($professores as $p): ?>
                            <option value="<?= htmlspecialchars($p['nome']) ?>">
                                <?= htmlspecialchars($p['id_professor']) ?>
                            </option>
                        <?php endforeach; ?>
                    </datalist>

                    <div class="modal-buttons">
                        <button class="modal-btn criar" type="submit">Criar</button>
                        <button class="modal-btn voltar" id="btn-fechar-modal">Voltar</button>
                    </div>
                </form>

                <img class="modal-img" src="img/img_editar_turma.png" alt="">
            </div>

        </div>
    </div>

    <hr>

    <!-- Rodapé -->
    <footer id="footer">
        <div class="contactos">
            <h3>Contactos</h3>
            <p>
                <img src="img/img_email.png" alt="Email">
                <strong>Email:</strong> geral@ipsantarem.pt
            </p>
            <p>
                <img src="img/img_telemovel.png" alt="Telefone">
                <strong>Telefone:</strong> +351 243 309 520
            </p>
            <p>
                <img src="img/img_localizacao.png" alt="Endereço">
                <strong>Endereço:</strong> Complexo Andaluz, Apartado 279, 2001-904 Santarém
            </p>
        </div>

        <div class="logos">
            <img src="img/Logo.png" alt="Gestão de Estágios Universitários">
            <img src="img/img_confinanciado.png" alt="Confinanciado">
        </div>
    </footer>

    <!-- ======= MODAL PERFIL / CONTA ======= -->
    <div id="perfil-overlay" class="perfil-overlay">
    <div class="perfil-card">
        <div class="perfil-banner"></div>

        <div class="perfil-avatar">
            <img src="img/img_conta.png" alt="Avatar" class="perfil-avatar-img">
        </div>

        <div class="perfil-content">
            <div class="perfil-role">Aluno</div>
            <div class="perfil-name">Rodrigo Branco</div>

            <div class="perfil-row">
                <img src="img/img_email.png" alt="Email" class="perfil-row-img">
                <span class="perfil-row-text">240001087@esg.ipsantarem.pt</span>
            </div>

            <a href="verPerfil.html" class="perfil-row">
                <img src="img/img_definicoes.png" alt="Definições" class="perfil-row-img">
                <span class="perfil-row-text">Definições de conta</span>
            </a>

            <a href="login.html" class="perfil-logout-row">
                <img src="img/img_sair.png" alt="Sair" class="perfil-back-img">
                <span class="perfil-logout-text">Log out</span>
            </a>

            <button type="button" class="perfil-voltar-btn">
                Voltar
            </button>
        </div>
    </div>
</div>



<script src="js/index.js"></script>

</body>
</html>
