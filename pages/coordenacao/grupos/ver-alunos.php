<?php

session_start();
if (empty($_SESSION["login"])) {
    echo "<script>location.href='../../../index.php';</script>";
    exit();
}
require_once('../../../cfg/config.php');

// Verifique se o ID do subgrupo está presente no POST ou GET
$idsubgrupo = filter_input(INPUT_POST, 'idsubgrupo', FILTER_VALIDATE_INT) ?: filter_input(INPUT_GET, 'idsubgrupo', FILTER_VALIDATE_INT);
$nomeSubgrupo = filter_input(INPUT_POST, 'nome_subgrupo', FILTER_CALLBACK, ['options' => 'trim']) ?: filter_input(INPUT_GET, 'nome_subgrupo', FILTER_CALLBACK, ['options' => 'trim']);

if (!$idsubgrupo) {
    die("ID de subgrupo inválido.");
}

// Adicionar alunos ao subgrupo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['alunos'])) {
    $alunos = filter_input(INPUT_POST, 'alunos', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
    try {
        $conn->begin_transaction();

        $stmt = $conn->prepare("INSERT INTO alunos_subgrupos (idusuario, idsubgrupo) VALUES (?, ?)");
        foreach ($alunos as $idaluno) {
            $stmt->bind_param("ii", $idaluno, $idsubgrupo);
            $stmt->execute();
        }

        $conn->commit();
        $stmt->close();
        header("Location: ver-alunos.php?idsubgrupo={$idsubgrupo}&nome_subgrupo=" . urlencode($nomeSubgrupo));
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Erro ao adicionar alunos: " . $e->getMessage());
        die("Erro ao adicionar alunos.");
    }
}

// Remover aluno do subgrupo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_aluno'])) {
    $idaluno = filter_input(INPUT_POST, 'idaluno', FILTER_VALIDATE_INT);
    try {
        $stmt = $conn->prepare("DELETE FROM alunos_subgrupos WHERE idusuario = ? AND idsubgrupo = ?");
        $stmt->bind_param("ii", $idaluno, $idsubgrupo);
        $stmt->execute();
        $stmt->close();
        header("Location: ver-alunos.php?idsubgrupo={$idsubgrupo}&nome_subgrupo=" . urlencode($nomeSubgrupo));
        exit();
    } catch (Exception $e) {
        error_log("Erro ao remover aluno: " . $e->getMessage());
        die("Erro ao remover aluno.");
    }
}

try {
    $queryAlunos = "SELECT u.idusuario, u.nome
                    FROM usuarios u 
                    JOIN alunos_subgrupos asg ON u.idusuario = asg.idusuario 
                    WHERE asg.idsubgrupo = ?";

    $queryAlunosSemSubgrupo = "SELECT DISTINCT u.idusuario, u.nome
                                FROM usuarios u 
                                JOIN modulos_alunos ma ON u.idusuario = ma.idusuario 
                                WHERE u.idusuario NOT IN (
                                    SELECT idusuario 
                                    FROM alunos_subgrupos 
                                    WHERE idsubgrupo = ?
                                )";

    $stmtAlunos = $conn->prepare($queryAlunos);
    $stmtAlunos->bind_param("i", $idsubgrupo);
    $stmtAlunos->execute();
    $resultAlunos = $stmtAlunos->get_result();

    $stmtAlunosSemSubgrupo = $conn->prepare($queryAlunosSemSubgrupo);
    $stmtAlunosSemSubgrupo->bind_param("i", $idsubgrupo);
    $stmtAlunosSemSubgrupo->execute();
    $resultAlunosSemSubgrupo = $stmtAlunosSemSubgrupo->get_result();

} catch (Exception $e) {
    error_log("Erro na consulta: " . $e->getMessage());
    die("Erro ao buscar alunos.");
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alunos do Subgrupo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../../css/style.css">
    <script>
        function confirmRemoval() {
            return confirm('Tem certeza que deseja remover este aluno do subgrupo?');
        }
    </script>
</head>

<body>
    <header>
        <?php include('../../../includes/navbar.php'); ?>
        <?php include('../../../includes/menu-lateral-coordenacao.php'); ?>
    </header>
    <main>
        <div class="container mt-3">
            <h2>Alunos do Subgrupo: <?= htmlspecialchars($nomeSubgrupo) ?></h2>

            <?php if ($resultAlunos->num_rows > 0): ?>
                <div class="card mb-3">
                    <div class="card-header">Alunos no Subgrupo</div>
                    <div class="card-body">
                        <table class="table table-striped table-sm table-responsive">
                            <thead>
                                <tr>
                                    <th class="col-2">ID</th>
                                    <th class="col-7">Nome</th>
                                    <th class="col-3">Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($aluno = $resultAlunos->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($aluno['idusuario']) ?></td>
                                        <td><?= htmlspecialchars($aluno['nome']) ?></td>
                                        <td>
                                            <form method="post" style="display:inline;" onsubmit="return confirmRemoval();">
                                                <input type="hidden" name="idsubgrupo" value="<?= htmlspecialchars($idsubgrupo) ?>">
                                                <input type="hidden" name="idaluno" value="<?= htmlspecialchars($aluno['idusuario']) ?>">
                                                <input type="hidden" name="nome_subgrupo" value="<?= htmlspecialchars($nomeSubgrupo) ?>">
                                                <button type="submit" name="remove_aluno" class="btn btn-danger btn-sm">Remover</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-info">Nenhum aluno encontrado no subgrupo.</div>
            <?php endif; ?>

            <?php if ($resultAlunosSemSubgrupo->num_rows > 0): ?>
                <div class="card">
                    <div class="card-header">
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#alunosSemSubgrupoModal">
                            Alunos sem Subgrupo
                        </button>
                    </div>
                </div>

                <!-- Modal Alunos sem Subgrupo -->
                <div class="modal fade" id="alunosSemSubgrupoModal" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Alunos sem Subgrupo</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <form method="post">
                                    <input type="hidden" name="idsubgrupo" value="<?= htmlspecialchars($idsubgrupo) ?>">
                                    <input type="hidden" name="nome_subgrupo" value="<?= htmlspecialchars($nomeSubgrupo) ?>">
                                    <table class="table table-striped table-sm table-responsive">
                                        <thead>
                                            <tr>
                                                <th class="col-1">Select</th>
                                                <th class="col-3">ID</th>
                                                <th class="col-8">Nome</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($aluno = $resultAlunosSemSubgrupo->fetch_assoc()): ?>
                                                <tr>
                                                    <td><input type="checkbox" name="alunos[]" value="<?= htmlspecialchars($aluno['idusuario']) ?>"></td>
                                                    <td><?= htmlspecialchars($aluno['idusuario']) ?></td>
                                                    <td><?= htmlspecialchars($aluno['nome']) ?></td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                    <button type="submit" class="btn btn-primary">Adicionar ao Subgrupo</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <button onclick="location.href='grupos.php'" class="btn btn-secondary mt-3">Voltar</button>
        </div>
    </main>
    <footer>
        <div class="card footer-home rounded-0">
            <div class="card-body"></div>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>