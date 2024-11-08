```php
<?php

require_once('../../../cfg/config.php');

$idsubgrupo = filter_input(INPUT_POST, 'idsubgrupo', FILTER_VALIDATE_INT);
$nomeSubgrupo = filter_input(INPUT_POST, 'nome_subgrupo', FILTER_SANITIZE_STRING);

if (!$idsubgrupo) {
    die("ID de subgrupo inválido.");
}

try {
    // Preparar consultas com prepared statements
    $queryAlunos = "SELECT u.idusuario, u.nome
                    FROM usuarios u 
                    JOIN alunos_subgrupos asg ON u.idusuario = asg.idusuario 
                    WHERE asg.idsubgrupo = ?";

    $queryAlunosSemSubgrupo = "SELECT u.idusuario, u.nome
                                FROM usuarios u 
                                JOIN modulos_alunos ma ON u.idusuario = ma.idusuario 
                                WHERE u.idusuario NOT IN (
                                    SELECT idusuario 
                                    FROM alunos_subgrupos 
                                    WHERE idsubgrupo = ?
                                )";

    // Executar consultas
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

<h2>Alunos do Subgrupo: <?= htmlspecialchars($nomeSubgrupo) ?></h2>

<?php if ($resultAlunos->num_rows > 0): ?>
    <div class="card mb-3">
        <div class="card-header">Alunos no Subgrupo</div>
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($aluno = $resultAlunos->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($aluno['idusuario']) ?></td>
                            <td><?= htmlspecialchars($aluno['nome']) ?></td>
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
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#alunosSemSubgrupoModal">
                Alunos sem Subgrupo
            </button>
        </div>
    </div>

    <!-- Modal Alunos sem Subgrupo -->
    <div class="modal fade" id="alunosSemSubgrupoModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Alunos sem Subgrupo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($aluno = $resultAlunosSemSubgrupo->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($aluno['idusuario']) ?></td>
                                    <td><?= htmlspecialchars($aluno['nome']) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
```