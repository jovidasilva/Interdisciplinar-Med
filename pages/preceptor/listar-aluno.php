<?php
session_start();
if (empty($_SESSION["login"]) || !in_array($_SESSION['tipo'] ?? null, [1], true)) {
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
    <title>Lista de alunos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../css/style.css?v=<?php echo ASSET_VERSION; ?>">
</head>

<body>
    <header>
        <?php include('../../includes/navbar.php'); ?>
        <?php include('../../includes/menu-lateral-preceptor.php'); ?>
    </header>
    <main>
        <div class="container mt-3">
            <div class="card">
                <div class="card-body">
                    <h3>Lista de Alunos</h3>
                    <table class="table table-striped table-secondary table-bordered">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Nota</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $porPagina = 20;
                            $pagina = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
                            $offset = ($pagina - 1) * $porPagina;

                            $sqlCount = "SELECT COUNT(*) AS total FROM usuarios WHERE tipo = 0";
                            $resCount = $conn->query($sqlCount);
                            $totalRegistros = $resCount ? (int) $resCount->fetch_assoc()['total'] : 0;
                            $totalPaginas = max(1, (int) ceil($totalRegistros / $porPagina));
                            if ($pagina > $totalPaginas) {
                                $pagina = $totalPaginas;
                                $offset = ($pagina - 1) * $porPagina;
                            }

                            $sql = "SELECT u.nome,
                                           COALESCE((SELECT a.nota
                                                     FROM avaliacoes a
                                                     WHERE a.idaluno = u.idusuario
                                                     ORDER BY a.data_avaliacao DESC
                                                     LIMIT 1), 'Sem nota') AS nota
                                    FROM usuarios u
                                    WHERE u.tipo = 0
                                    LIMIT ? OFFSET ?";

                            $stmt = $conn->prepare($sql);

                            if (!$stmt) {
                                error_log("Erro na consulta (listar-aluno.php): " . $conn->error);
                                die("Erro ao processar a consulta. Tente novamente mais tarde.");
                            }

                            $stmt->bind_param("ii", $porPagina, $offset);
                            $stmt->execute();
                            $res = $stmt->get_result();

                            $qtd = $res->num_rows;

                            if ($qtd > 0) {
                                while ($row = $res->fetch_object()) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row->nome) . "</td>";
                                    echo "<td>" . htmlspecialchars($row->nota) . "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                if ($totalRegistros === 0) {
                                    echo "<tr><td colspan='2'>Nenhum aluno encontrado.</td></tr>";
                                } else {
                                    echo "<tr><td colspan='2'>Nenhum aluno encontrado nesta página. <a href='?" . http_build_query(array_merge($_GET, ['pagina' => 1])) . "'>Voltar à primeira página</a>.</td></tr>";
                                }
                            }
                            ?>
                        </tbody>
                    </table>

                    <?php if ($totalPaginas > 1): ?>
                        <nav aria-label="Paginação de alunos">
                            <ul class="pagination justify-content-center">
                                <?php
                                $paramsAnterior = $_GET;
                                $paramsAnterior['pagina'] = max(1, $pagina - 1);
                                $paramsProxima = $_GET;
                                $paramsProxima['pagina'] = min($totalPaginas, $pagina + 1);
                                ?>
                                <li class="page-item <?php echo $pagina <= 1 ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?<?php echo http_build_query($paramsAnterior); ?>">Anterior</a>
                                </li>
                                <?php for ($p = 1; $p <= $totalPaginas; $p++): ?>
                                    <?php $paramsP = $_GET; $paramsP['pagina'] = $p; ?>
                                    <li class="page-item <?php echo $p === $pagina ? 'active' : ''; ?>">
                                        <a class="page-link" href="?<?php echo http_build_query($paramsP); ?>"><?php echo $p; ?></a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?php echo $pagina >= $totalPaginas ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?<?php echo http_build_query($paramsProxima); ?>">Próxima</a>
                                </li>
                            </ul>
                        </nav>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>

</html>
