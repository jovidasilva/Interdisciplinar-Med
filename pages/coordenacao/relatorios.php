<?php
session_start();
if (empty($_SESSION["login"]) || !in_array($_SESSION['tipo'] ?? null, [2, 3], true)) {
    echo "<script>location.href='../../index.php';</script>";
    exit();
}

include('../../cfg/config.php');

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatorios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../css/style.css?v=<?php echo ASSET_VERSION; ?>">
</head>

<body>
    <header>
        <?php include('../../includes/navbar.php'); ?>
        <?php include('../../includes/menu-lateral-coordenacao.php'); ?>
    </header>
    <main>
        <div class="container mt-3">
            <?php
            $totalAlunosAtivos = 0;
            $totalPreceptoresAtivos = 0;
            $totalPendentes = 0;
            $totalModulos = 0;
            $totalUnidades = 0;
            $totalAvaliacoes = 0;
            $mediaGeral = 0;

            $res = $conn->query("SELECT COUNT(*) AS total FROM usuarios WHERE tipo = 0 AND ativo = 1");
            if ($res) {
                $totalAlunosAtivos = (int) $res->fetch_assoc()['total'];
            }

            $res = $conn->query("SELECT COUNT(*) AS total FROM usuarios WHERE tipo = 1 AND ativo = 1");
            if ($res) {
                $totalPreceptoresAtivos = (int) $res->fetch_assoc()['total'];
            }

            $res = $conn->query("SELECT COUNT(*) AS total FROM usuarios WHERE tipo = -1");
            if ($res) {
                $totalPendentes = (int) $res->fetch_assoc()['total'];
            }

            $res = $conn->query("SELECT COUNT(*) AS total FROM modulos");
            if ($res) {
                $totalModulos = (int) $res->fetch_assoc()['total'];
            }

            $res = $conn->query("SELECT COUNT(*) AS total FROM unidades");
            if ($res) {
                $totalUnidades = (int) $res->fetch_assoc()['total'];
            }

            $res = $conn->query("SELECT COUNT(*) AS total, AVG(nota) AS media FROM avaliacoes");
            if ($res) {
                $row = $res->fetch_assoc();
                $totalAvaliacoes = (int) $row['total'];
                $mediaGeral = $row['media'] !== null ? (float) $row['media'] : 0;
            }

            // Alunos matriculados em algum módulo mas sem nenhuma avaliação registrada.
            $alunosSemAvaliacao = [];
            $query = "SELECT DISTINCT u.idusuario, u.nome, u.registro
                      FROM usuarios u
                      JOIN modulos_alunos ma ON ma.idusuario = u.idusuario
                      WHERE u.tipo = 0
                        AND NOT EXISTS (SELECT 1 FROM avaliacoes a WHERE a.idaluno = u.idusuario)
                      ORDER BY u.nome";
            $res = $conn->query($query);
            if ($res) {
                $alunosSemAvaliacao = $res->fetch_all(MYSQLI_ASSOC);
            }

            // Módulos sem nenhum preceptor associado em preceptores_modulos.
            $modulosSemPreceptor = [];
            $query = "SELECT m.idmodulo, m.nome_modulo
                      FROM modulos m
                      WHERE NOT EXISTS (SELECT 1 FROM preceptores_modulos pm WHERE pm.idmodulo = m.idmodulo)
                      ORDER BY m.nome_modulo";
            $res = $conn->query($query);
            if ($res) {
                $modulosSemPreceptor = $res->fetch_all(MYSQLI_ASSOC);
            }
            ?>

            <h3 class="mb-3">Relatórios Gerenciais</h3>

            <div class="row g-3 mb-4">
                <div class="col-md-3 col-sm-6">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <i class="bi bi-person-fill fs-2 text-primary"></i>
                            <h2 class="mt-2"><?php echo $totalAlunosAtivos; ?></h2>
                            <p class="text-muted mb-0">Alunos Ativos</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <i class="bi bi-person-workspace fs-2 text-success"></i>
                            <h2 class="mt-2"><?php echo $totalPreceptoresAtivos; ?></h2>
                            <p class="text-muted mb-0">Preceptores Ativos</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <i class="bi bi-person-check fs-2 text-warning"></i>
                            <h2 class="mt-2"><?php echo $totalPendentes; ?></h2>
                            <p class="text-muted mb-0">Usuários Pendentes de Aprovação</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <i class="bi bi-grid fs-2 text-info"></i>
                            <h2 class="mt-2"><?php echo $totalModulos; ?></h2>
                            <p class="text-muted mb-0">Módulos Cadastrados</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <i class="bi bi-building fs-2 text-secondary"></i>
                            <h2 class="mt-2"><?php echo $totalUnidades; ?></h2>
                            <p class="text-muted mb-0">Unidades Cadastradas</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <i class="bi bi-journal-text fs-2 text-primary"></i>
                            <h2 class="mt-2"><?php echo $totalAvaliacoes; ?></h2>
                            <p class="text-muted mb-0">Avaliações Realizadas</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <i class="bi bi-graph-up fs-2 text-success"></i>
                            <h2 class="mt-2"><?php echo number_format($mediaGeral, 2, ',', '.'); ?></h2>
                            <p class="text-muted mb-0">Média Geral de Notas</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <h4><i class="bi bi-exclamation-triangle text-warning"></i> Alunos sem Avaliação</h4>
                    <?php if (empty($alunosSemAvaliacao)): ?>
                        <div class="alert alert-success mb-0">Não há alunos matriculados sem avaliação.</div>
                    <?php else: ?>
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>RA</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($alunosSemAvaliacao as $a): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($a['nome']); ?></td>
                                        <td><?php echo htmlspecialchars($a['registro']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <h4><i class="bi bi-exclamation-triangle text-warning"></i> Módulos sem Preceptor Associado</h4>
                    <?php if (empty($modulosSemPreceptor)): ?>
                        <div class="alert alert-success mb-0">Não há módulos sem preceptor associado.</div>
                    <?php else: ?>
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Módulo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($modulosSemPreceptor as $m): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($m['nome_modulo']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
    <footer>
        <div class="card footer-home rounded-0">
            <div class="card-body">
            </div>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>

</html>