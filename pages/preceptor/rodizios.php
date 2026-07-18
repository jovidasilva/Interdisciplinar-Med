<?php
session_start();

include('../../cfg/config.php');

if (empty($_SESSION["login"]) || !in_array($_SESSION['tipo'] ?? null, [1], true)) {
    echo "<script>location.href='../../index.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rodízios dos Meus Módulos</title>
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
                    <h3>Rodízios dos Meus Módulos</h3>
                    <?php
                    $idusuario = $_SESSION['idusuario'] ?? null;
                    $rodizios = [];

                    if ($idusuario) {
                        $query = "SELECT r.idrodizio, r.periodo, r.inicio, r.fim, m.nome_modulo,
                                         GROUP_CONCAT(DISTINCT CONCAT(g.nome_grupo, '-', sg.nome_subgrupo)
                                                       ORDER BY g.nome_grupo, sg.nome_subgrupo SEPARATOR ', ') AS grupos_subgrupos
                                  FROM preceptores_modulos pm
                                  JOIN rodizios r ON r.idmodulo = pm.idmodulo
                                  JOIN modulos m ON m.idmodulo = r.idmodulo
                                  LEFT JOIN rodizios_subgrupos rs ON rs.idrodizio = r.idrodizio
                                  LEFT JOIN subgrupos sg ON sg.idsubgrupo = rs.idsubgrupo
                                  LEFT JOIN grupos g ON g.idgrupo = sg.idgrupo
                                  WHERE pm.idusuario = ?
                                  GROUP BY r.idrodizio, r.periodo, r.inicio, r.fim, m.nome_modulo
                                  ORDER BY r.inicio DESC";
                        $stmt = $conn->prepare($query);
                        if (!$stmt) {
                            error_log("Erro na consulta (preceptor/rodizios.php): " . $conn->error);
                            die("Erro ao processar a consulta. Tente novamente mais tarde.");
                        }
                        $stmt->bind_param("i", $idusuario);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $rodizios = $result->fetch_all(MYSQLI_ASSOC);
                    }
                    ?>

                    <?php if (empty($rodizios)): ?>
                        <div class="alert alert-info">Não há rodízios associados aos seus módulos no momento.</div>
                    <?php else: ?>
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Módulo</th>
                                    <th>Período</th>
                                    <th>Data Início</th>
                                    <th>Data Fim</th>
                                    <th>Grupos/Subgrupos</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rodizios as $r): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($r['nome_modulo']); ?></td>
                                        <td><?php echo htmlspecialchars($r['periodo']); ?></td>
                                        <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($r['inicio']))); ?></td>
                                        <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($r['fim']))); ?></td>
                                        <td><?php echo htmlspecialchars($r['grupos_subgrupos'] ?? '-'); ?></td>
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
            <div class="card-body"></div>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>