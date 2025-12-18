<?php
// tabelasSecundarias.php
session_start();
include '../db.php';
include 'modelsTabelasSecundarias.php';

// Segurança: Apenas Administradores devem aceder
if (!isset($_SESSION['logged_in']) || $_SESSION['cargo'] !== 'Administrador') {
    header("Location: ../login.php");
    exit();
}

$conexao = estabelecerConexao();
$erros   = [];
$sucesso = '';

// Definição das tabelas e respetivas colunas para facilitar a manutenção
$config = [
    'escola'          => ['id' => 'id_escola',          'desc' => 'escola_desc'],
    'nacionalidade'   => ['id' => 'id_nacionalidade',   'desc' => 'nacionalidade_desc'],
    'especializacao'  => ['id' => 'id_especializacao',  'desc' => 'especializacao_desc'],
    'curso'           => ['id' => 'id_curso',           'desc' => 'curso_desc'],
    'area_cientifica' => ['id' => 'id_area_cientifica', 'desc' => 'area_cientifica_desc'],
    'ramo_atividade'  => ['id' => 'id_ramo_atividade',  'desc' => 'ramo_atividade_desc'] // Adicionado aqui
];

// ========== PROCESSAMENTO DE FORMULÁRIOS ==========
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao   = $_POST['acao']   ?? '';
    $tabela = $_POST['tabela'] ?? '';

    if (array_key_exists($tabela, $config)) {
        $cfg = $config[$tabela];

        if ($acao === 'adicionar') {
            $descricao = trim($_POST['descricao'] ?? '');
            if ($descricao === '') {
                $erros[] = 'A descrição não pode estar vazia.';
            } else {
                if (adicionarRegisto($conexao, $tabela, $cfg['desc'], $descricao)) {
                    $sucesso = 'Registo adicionado com sucesso.';
                }
            }
        } elseif ($acao === 'apagar') {
            $id = (int)($_POST['id'] ?? 0);
            try {
                if (apagarRegisto($conexao, $tabela, $cfg['id'], $id)) {
                    $sucesso = 'Registo eliminado com sucesso.';
                }
            } catch (PDOException $e) {
                // Erro de Integridade Referencial (FK) tratado aqui
                $erros[] = 'Não foi possível apagar: este item está a ser utilizado por outros registos no sistema.';
            }
        }
    }
}

// ========== CARREGAR DADOS ==========
$dados = [];
foreach ($config as $tabela => $cfg) {
    $dados[$tabela] = listarTabela($conexao, $tabela, $cfg['desc']);
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GEU — Gestão de Tabelas Secundárias</title>
    <link rel="stylesheet" href="tabelasSecundarias.css">
</head>
<body>

<main id="main-content">
    <div class="header-admin">
        <h1>Painel Administrativo</h1>
        <p>Gestão de listas do sistema, áreas científicas e ramos de atividade.</p>
        <a href="../administradores/index.php" class="btn-voltar">Voltar ao Início</a>
    </div>

    <div class="mensagens">
        <?php foreach ($erros as $e): ?> <div class="alert erro"><?= htmlspecialchars($e) ?></div> <?php endforeach; ?>
        <?php if ($sucesso): ?> <div class="alert sucesso"><?= htmlspecialchars($sucesso) ?></div> <?php endif; ?>
    </div>

    <div class="painel-grid">
        <?php foreach ($config as $nomeTabela => $cfg): ?>
            <section class="card">
                <h2><?= ucfirst(str_replace('_', ' ', $nomeTabela)) ?></h2>

                <form method="post" class="inline-form">
                    <input type="hidden" name="tabela" value="<?= $nomeTabela ?>">
                    <input type="hidden" name="acao" value="adicionar">
                    <input type="text" name="descricao" placeholder="Novo item..." required>
                    <button class="btn btn-add" type="submit">Adicionar</button>
                </form>

                <div class="scroll-table">
                    <table class="lista">
                        <thead>
                            <tr>
                                <th class="col-id">ID</th>
                                <th>Descrição</th>
                                <th class="col-acoes"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($dados[$nomeTabela] as $item): ?>
                                <tr>
                                    <td><?= $item[$cfg['id']] ?></td>
                                    <td><?= htmlspecialchars($item[$cfg['desc']]) ?></td>
                                    <td class="td-acoes">
                                        <form method="post" onsubmit="return confirm('Eliminar permanentemente?');">
                                            <input type="hidden" name="tabela" value="<?= $nomeTabela ?>">
                                            <input type="hidden" name="acao" value="apagar">
                                            <input type="hidden" name="id" value="<?= $item[$cfg['id']] ?>">
                                            <button type="submit" class="btn-del">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        <?php endforeach; ?>
    </div>
</main>

</body>
</html>