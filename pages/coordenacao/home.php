<?php
session_start();
if (empty($_SESSION["login"])) {
    echo "<script>location.href='../../index.php';</script>";
    exit();
}

include('../../cfg/config.php');

// Buscar estatísticas gerais
$sql_total_alunos = "SELECT COUNT(*) as total FROM usuarios WHERE tipo = 0 AND ativo = 1";
$result_alunos = $conn->query($sql_total_alunos);
$total_alunos = $result_alunos->fetch_assoc()['total'];

$sql_total_preceptores = "SELECT COUNT(*) as total FROM usuarios WHERE tipo = 1 AND ativo = 1";
$result_preceptores = $conn->query($sql_total_preceptores);
$total_preceptores = $result_preceptores->fetch_assoc()['total'];

$sql_total_avaliacoes = "SELECT COUNT(*) as total FROM avaliacoes";
$result_avaliacoes = $conn->query($sql_total_avaliacoes);
$total_avaliacoes = $result_avaliacoes->fetch_assoc()['total'];

$sql_media_geral = "SELECT AVG(nota) as media FROM avaliacoes";
$result_media = $conn->query($sql_media_geral);
$media_geral = $result_media->fetch_assoc()['media'];

// Buscar avaliações recentes
$sql_avaliacoes_recentes = "SELECT 
    u.nome AS aluno,
    p.nome AS preceptor,
    a.nota,
    a.data_avaliacao
FROM avaliacoes a
INNER JOIN usuarios u ON a.idaluno = u.idusuario
INNER JOIN usuarios p ON a.idpreceptor = p.idusuario
ORDER BY a.data_avaliacao DESC
LIMIT 5";
$result_recentes = $conn->query($sql_avaliacoes_recentes);

// Buscar distribuição de alunos por módulo
$sql_alunos_modulo = "SELECT 
    m.nome_modulo,
    COUNT(DISTINCT ma.idusuario) as total_alunos
FROM modulos m
LEFT JOIN modulos_alunos ma ON m.idmodulo = ma.idmodulo
GROUP BY m.idmodulo, m.nome_modulo
ORDER BY total_alunos DESC
LIMIT 5";
$result_modulos = $conn->query($sql_alunos_modulo);

// Buscar alunos com média baixa (< 7)
$sql_alertas = "SELECT 
    u.nome AS aluno,
    u.registro AS matricula,
    AVG(a.nota) as media
FROM usuarios u
INNER JOIN avaliacoes a ON u.idusuario = a.idaluno
WHERE u.tipo = 0
GROUP BY u.idusuario, u.nome, u.registro
HAVING AVG(a.nota) < 7
ORDER BY media ASC
LIMIT 5";
$result_alertas = $conn->query($sql_alertas);

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coordenação</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../css/style.css">
</head>

<body>
    <header>
        <?php include('../../includes/navbar.php'); ?>
        <?php include('../../includes/menu-lateral-coordenacao.php'); ?>
    </header>
    <main>
        <div class="container-fluid content">
            <div class="row">
                <div class="col-12">
                    <h2 class="mb-4">Dashboard - Coordenação</h2>
                    
                    <!-- Cards de Estatísticas -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card text-white bg-primary mb-3">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-title mb-0">Alunos Ativos</h6>
                                            <h2 class="mb-0"><?php echo $total_alunos; ?></h2>
                                        </div>
                                        <div>
                                            <i class="bi bi-people-fill" style="font-size: 3rem; opacity: 0.5;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="card text-white bg-success mb-3">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-title mb-0">Preceptores Ativos</h6>
                                            <h2 class="mb-0"><?php echo $total_preceptores; ?></h2>
                                        </div>
                                        <div>
                                            <i class="bi bi-person-badge-fill" style="font-size: 3rem; opacity: 0.5;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="card text-white bg-info mb-3">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-title mb-0">Total de Avaliações</h6>
                                            <h2 class="mb-0"><?php echo $total_avaliacoes; ?></h2>
                                        </div>
                                        <div>
                                            <i class="bi bi-clipboard-check-fill" style="font-size: 3rem; opacity: 0.5;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="card text-white bg-warning mb-3">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-title mb-0">Média Geral</h6>
                                            <h2 class="mb-0"><?php echo $media_geral ? number_format($media_geral, 2) : '0.00'; ?></h2>
                                        </div>
                                        <div>
                                            <i class="bi bi-graph-up-arrow" style="font-size: 3rem; opacity: 0.5;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Ações Rápidas -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="bi bi-lightning-fill"></i> Ações Rápidas</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3 mb-2">
                                            <a href="relatorios.php" class="btn btn-outline-primary w-100">
                                                <i class="bi bi-file-earmark-text"></i> Relatórios
                                            </a>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <a href="listar-aluno.php" class="btn btn-outline-success w-100">
                                                <i class="bi bi-people"></i> Gerenciar Alunos
                                            </a>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <a href="listar-preceptor.php" class="btn btn-outline-info w-100">
                                                <i class="bi bi-person-badge"></i> Gerenciar Preceptores
                                            </a>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <a href="listar-modulo.php" class="btn btn-outline-warning w-100">
                                                <i class="bi bi-book"></i> Gerenciar Módulos
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Conteúdo Principal -->
                    <div class="row">
                        <!-- Avaliações Recentes -->
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0"><i class="bi bi-clock-history"></i> Avaliações Recentes</h5>
                                </div>
                                <div class="card-body">
                                    <?php if ($result_recentes && $result_recentes->num_rows > 0): ?>
                                        <div class="list-group">
                                            <?php while ($row = $result_recentes->fetch_assoc()): ?>
                                                <div class="list-group-item">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <strong><?php echo htmlspecialchars($row['aluno']); ?></strong>
                                                            <br>
                                                            <small class="text-muted">
                                                                Avaliado por: <?php echo htmlspecialchars($row['preceptor']); ?>
                                                            </small>
                                                        </div>
                                                        <div class="text-end">
                                                            <span class="badge <?php echo $row['nota'] >= 7 ? 'bg-success' : 'bg-danger'; ?>">
                                                                <?php echo number_format($row['nota'], 2); ?>
                                                            </span>
                                                            <br>
                                                            <small class="text-muted">
                                                                <?php echo date('d/m/Y', strtotime($row['data_avaliacao'])); ?>
                                                            </small>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endwhile; ?>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-center text-muted">Nenhuma avaliação registrada</p>
                                    <?php endif; ?>
                                </div>
                                <div class="card-footer text-center">
                                    <a href="relatorios.php" class="btn btn-sm btn-primary">Ver Todos os Relatórios</a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Distribuição por Módulo -->
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header bg-success text-white">
                                    <h5 class="mb-0"><i class="bi bi-pie-chart-fill"></i> Alunos por Módulo</h5>
                                </div>
                                <div class="card-body">
                                    <?php if ($result_modulos && $result_modulos->num_rows > 0): ?>
                                        <div class="list-group">
                                            <?php while ($row = $result_modulos->fetch_assoc()): ?>
                                                <div class="list-group-item">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <span><?php echo htmlspecialchars($row['nome_modulo']); ?></span>
                                                        <span class="badge bg-success rounded-pill">
                                                            <?php echo $row['total_alunos']; ?> aluno(s)
                                                        </span>
                                                    </div>
                                                </div>
                                            <?php endwhile; ?>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-center text-muted">Nenhum módulo cadastrado</p>
                                    <?php endif; ?>
                                </div>
                                <div class="card-footer text-center">
                                    <a href="listar-modulo.php" class="btn btn-sm btn-success">Gerenciar Módulos</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Alertas -->
                    <?php if ($result_alertas && $result_alertas->num_rows > 0): ?>
                    <div class="row">
                        <div class="col-12">
                            <div class="card border-danger">
                                <div class="card-header bg-danger text-white">
                                    <h5 class="mb-0"><i class="bi bi-exclamation-triangle-fill"></i> Alunos com Média Abaixo de 7</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Matrícula</th>
                                                    <th>Aluno</th>
                                                    <th>Média</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while ($row = $result_alertas->fetch_assoc()): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($row['matricula']); ?></td>
                                                        <td><?php echo htmlspecialchars($row['aluno']); ?></td>
                                                        <td>
                                                            <span class="badge bg-danger">
                                                                <?php echo number_format($row['media'], 2); ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-warning text-dark">Atenção Necessária</span>
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
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