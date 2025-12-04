<?php
// admin/index.php

include '../db.php';
include '../utils.php';

$conexao = estabelecerConexao();

$erros   = [];
$sucesso = '';

// ========== HANDLER DE POST (INSERIR / APAGAR) ==========

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $acao   = $_POST['acao']   ?? '';
    $tabela = $_POST['tabela'] ?? '';

    if ($acao === 'adicionar') {

        $descricao = trim($_POST['descricao'] ?? '');

        if ($descricao === '') {
            $erros[] = 'A descrição é obrigatória.';
        } else {
            try {
                switch ($tabela) {
                    case 'escola':
                        $stmt = $conexao->prepare('INSERT INTO escola (escola_desc) VALUES (:desc)');
                        break;

                    case 'nacionalidade':
                        $stmt = $conexao->prepare('INSERT INTO nacionalidade (nacionalidade_desc) VALUES (:desc)');
                        break;

                    case 'especializacao':
                        $stmt = $conexao->prepare('INSERT INTO especializacao (especializacao_desc) VALUES (:desc)');
                        break;

                    case 'curso':
                        $stmt = $conexao->prepare('INSERT INTO curso (curso_desc) VALUES (:desc)');
                        break;

                    default:
                        $stmt = null;
                        $erros[] = 'Tabela inválida.';
                }

                if ($stmt) {
                    $stmt->execute(['desc' => $descricao]);
                    $sucesso = 'Registo adicionado com sucesso.';
                }
            } catch (PDOException $e) {
                $erros[] = 'Erro ao adicionar registo: ' . $e->getMessage();
            }
        }

    } elseif ($acao === 'apagar') {

        $id = (int)($_POST['id'] ?? 0);

        if ($id <= 0) {
            $erros[] = 'ID inválido para apagar.';
        } else {
            try {
                switch ($tabela) {
                    case 'escola':
                        $stmt = $conexao->prepare('DELETE FROM escola WHERE id_escola = :id');
                        break;

                    case 'nacionalidade':
                        $stmt = $conexao->prepare('DELETE FROM nacionalidade WHERE id_nacionalidade = :id');
                        break;

                    case 'especializacao':
                        $stmt = $conexao->prepare('DELETE FROM especializacao WHERE id_especializacao = :id');
                        break;

                    case 'curso':
                        $stmt = $conexao->prepare('DELETE FROM curso WHERE id_curso = :id');
                        break;

                    default:
                        $stmt = null;
                        $erros[] = 'Tabela inválida.';
                }

                if ($stmt) {
                    $stmt->execute(['id' => $id]);
                    $sucesso = 'Registo apagado com sucesso.';
                }
            } catch (PDOException $e) {
                // Se houver FKs a impedir o delete, vais ver aqui
                $erros[] = 'Erro ao apagar registo (possivelmente em uso noutras tabelas): ' . $e->getMessage();
            }
        }
    }
}

// ========== BUSCAR LISTAS PARA MOSTRAR ==========

function listarEscolas(PDO $con)
{
    $res = $con->query('SELECT id_escola, escola_desc FROM escola ORDER BY escola_desc');
    return $res->fetchAll(PDO::FETCH_ASSOC);
}

function listarNacionalidades(PDO $con)
{
    $res = $con->query('SELECT id_nacionalidade, nacionalidade_desc FROM nacionalidade ORDER BY nacionalidade_desc');
    return $res->fetchAll(PDO::FETCH_ASSOC);
}

function listarEspecializacoes(PDO $con)
{
    $res = $con->query('SELECT id_especializacao, especializacao_desc FROM especializacao ORDER BY especializacao_desc');
    return $res->fetchAll(PDO::FETCH_ASSOC);
}

function listarCursos(PDO $con)
{
    $res = $con->query('SELECT id_curso, curso_desc FROM curso ORDER BY curso_desc');
    return $res->fetchAll(PDO::FETCH_ASSOC);
}

$escolas         = listarEscolas($conexao);
$nacionalidades  = listarNacionalidades($conexao);
$especializacoes = listarEspecializacoes($conexao);
$cursos          = listarCursos($conexao);

?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>GEU — Painel Admin (Tabelas Secundárias)</title>
    <link rel="stylesheet" href="../css/base.css"><!-- se tiveres um -->
    <style>
        /* Podes mover isto para um .css depois */

        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: #f4f6f9;
            margin: 0;
        }

        #main-content {
            max-width: 1100px;
            margin: 40px auto;
            padding: 0 16px 40px;
        }

        h1 {
            margin-bottom: 8px;
        }

        .subtitulo {
            color: #555;
            margin-bottom: 24px;
        }

        .mensagens {
            margin-bottom: 20px;
        }

        .mensagens .erro {
            background: #ffe6e6;
            color: #c0392b;
            padding: 8px 12px;
            border-radius: 4px;
            margin-bottom: 6px;
        }

        .mensagens .sucesso {
            background: #e6ffef;
            color: #2e7d32;
            padding: 8px 12px;
            border-radius: 4px;
        }

        .painel-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 24px;
        }

        .card {
            background: #fff;
            border-radius: 10px;
            padding: 16px 18px 18px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.06);
        }

        .card h2 {
            margin-top: 0;
            font-size: 18px;
        }

        .card form.inline-form {
            display: flex;
            gap: 8px;
            margin-bottom: 12px;
            align-items: center;
        }

        .card form.inline-form input[type="text"] {
            flex: 1;
            padding: 6px 8px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }

        .btn-pequeno {
            padding: 6px 10px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-size: 13px;
        }

        .btn-add {
            background: #3498db;
            color: #fff;
        }

        .btn-add:hover {
            background: #2980b9;
        }

        table.lista {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        table.lista th, table.lista td {
            padding: 6px 4px;
            border-bottom: 1px solid #eee;
        }

        table.lista th {
            text-align: left;
            font-weight: 600;
            color: #555;
        }

        table.lista tr:last-child td {
            border-bottom: none;
        }

        .btn-delete {
            background: #e74c3c;
            color: #fff;
        }

        .btn-delete:hover {
            background: #c0392b;
        }

        .id-col {
            width: 60px;
            color: #777;
        }

        .acoes-col {
            width: 90px;
            text-align: right;
        }
    </style>
</head>
<body>

    <main id="main-content">
        <h1>Painel de administração</h1>
        <div class="subtitulo">
            Gestão de tabelas secundárias:
            <strong>Escolas</strong>,
            <strong>Nacionalidades</strong>,
            <strong>Especializações</strong> e
            <strong>Cursos</strong>.
        </div>

        <div class="mensagens">
            <?php foreach ($erros as $erro): ?>
                <div class="erro"><?= htmlspecialchars($erro) ?></div>
            <?php endforeach; ?>

            <?php if ($sucesso): ?>
                <div class="sucesso"><?= htmlspecialchars($sucesso) ?></div>
            <?php endif; ?>
        </div>

        <div class="painel-grid">
            <!-- ESCOLAS -->
            <section class="card">
                <h2>Escolas</h2>

                <!-- Form adicionar escola -->
                <form method="post" class="inline-form">
                    <input type="hidden" name="tabela" value="escola">
                    <input type="hidden" name="acao" value="adicionar">
                    <input
                        type="text"
                        name="descricao"
                        placeholder="Nova escola"
                    >
                    <button class="btn-pequeno btn-add" type="submit">
                        Adicionar
                    </button>
                </form>

                <!-- Lista de escolas -->
                <table class="lista">
                    <thead>
                        <tr>
                            <th class="id-col">ID</th>
                            <th>Descrição</th>
                            <th class="acoes-col">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($escolas as $esc): ?>
                        <tr>
                            <td class="id-col"><?= htmlspecialchars($esc['id_escola']) ?></td>
                            <td><?= htmlspecialchars($esc['escola_desc']) ?></td>
                            <td class="acoes-col">
                                <form method="post" style="display:inline;"
                                      onsubmit="return confirm('Eliminar esta escola?');">
                                    <input type="hidden" name="tabela" value="escola">
                                    <input type="hidden" name="acao" value="apagar">
                                    <input type="hidden" name="id" value="<?= (int)$esc['id_escola'] ?>">
                                    <button class="btn-pequeno btn-delete" type="submit">Apagar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($escolas)): ?>
                        <tr><td colspan="3">Sem escolas registadas.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </section>

            <!-- NACIONALIDADES -->
            <section class="card">
                <h2>Nacionalidades</h2>

                <form method="post" class="inline-form">
                    <input type="hidden" name="tabela" value="nacionalidade">
                    <input type="hidden" name="acao" value="adicionar">
                    <input
                        type="text"
                        name="descricao"
                        placeholder="Nova nacionalidade"
                    >
                    <button class="btn-pequeno btn-add" type="submit">
                        Adicionar
                    </button>
                </form>

                <table class="lista">
                    <thead>
                        <tr>
                            <th class="id-col">ID</th>
                            <th>Descrição</th>
                            <th class="acoes-col">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($nacionalidades as $nac): ?>
                        <tr>
                            <td class="id-col"><?= htmlspecialchars($nac['id_nacionalidade']) ?></td>
                            <td><?= htmlspecialchars($nac['nacionalidade_desc']) ?></td>
                            <td class="acoes-col">
                                <form method="post" style="display:inline;"
                                      onsubmit="return confirm('Eliminar esta nacionalidade?');">
                                    <input type="hidden" name="tabela" value="nacionalidade">
                                    <input type="hidden" name="acao" value="apagar">
                                    <input type="hidden" name="id" value="<?= (int)$nac['id_nacionalidade'] ?>">
                                    <button class="btn-pequeno btn-delete" type="submit">Apagar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($nacionalidades)): ?>
                        <tr><td colspan="3">Sem nacionalidades registadas.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </section>

            <!-- ESPECIALIZAÇÕES -->
            <section class="card">
                <h2>Especializações</h2>

                <form method="post" class="inline-form">
                    <input type="hidden" name="tabela" value="especializacao">
                    <input type="hidden" name="acao" value="adicionar">
                    <input
                        type="text"
                        name="descricao"
                        placeholder="Nova especialização"
                    >
                    <button class="btn-pequeno btn-add" type="submit">
                        Adicionar
                    </button>
                </form>

                <table class="lista">
                    <thead>
                        <tr>
                            <th class="id-col">ID</th>
                            <th>Descrição</th>
                            <th class="acoes-col">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($especializacoes as $esp): ?>
                        <tr>
                            <td class="id-col"><?= htmlspecialchars($esp['id_especializacao']) ?></td>
                            <td><?= htmlspecialchars($esp['especializacao_desc']) ?></td>
                            <td class="acoes-col">
                                <form method="post" style="display:inline;"
                                      onsubmit="return confirm('Eliminar esta especialização?');">
                                    <input type="hidden" name="tabela" value="especializacao">
                                    <input type="hidden" name="acao" value="apagar">
                                    <input type="hidden" name="id" value="<?= (int)$esp['id_especializacao'] ?>">
                                    <button class="btn-pequeno btn-delete" type="submit">Apagar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($especializacoes)): ?>
                        <tr><td colspan="3">Sem especializações registadas.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </section>

            <!-- CURSOS -->
            <section class="card">
                <h2>Cursos</h2>

                <form method="post" class="inline-form">
                    <input type="hidden" name="tabela" value="curso">
                    <input type="hidden" name="acao" value="adicionar">
                    <input
                        type="text"
                        name="descricao"
                        placeholder="Novo curso"
                    >
                    <button class="btn-pequeno btn-add" type="submit">
                        Adicionar
                    </button>
                </form>

                <table class="lista">
                    <thead>
                        <tr>
                            <th class="id-col">ID</th>
                            <th>Descrição</th>
                            <th class="acoes-col">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($cursos as $curso): ?>
                        <tr>
                            <td class="id-col"><?= htmlspecialchars($curso['id_curso']) ?></td>
                            <td><?= htmlspecialchars($curso['curso_desc']) ?></td>
                            <td class="acoes-col">
                                <form method="post" style="display:inline;"
                                      onsubmit="return confirm('Eliminar este curso?');">
                                    <input type="hidden" name="tabela" value="curso">
                                    <input type="hidden" name="acao" value="apagar">
                                    <input type="hidden" name="id" value="<?= (int)$curso['id_curso'] ?>">
                                    <button class="btn-pequeno btn-delete" type="submit">Apagar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($cursos)): ?>
                        <tr><td colspan="3">Sem cursos registados.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </section>

        </div>
    </main>

</body>
</html>
