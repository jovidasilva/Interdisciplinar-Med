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
    <title>Grupos - Preceptor</title>
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
                    <h3>Meus Grupos</h3>
                    <?php
                    $idusuario = $_SESSION['idusuario'] ?? null;
                    $subgruposList = [];

                    if ($idusuario) {
                        $query = "SELECT DISTINCT sg.idsubgrupo, sg.nome_subgrupo, g.nome_grupo
                                  FROM preceptores_modulos pm
                                  JOIN rodizios r ON r.idmodulo = pm.idmodulo
                                  JOIN rodizios_subgrupos rs ON rs.idrodizio = r.idrodizio
                                  JOIN subgrupos sg ON sg.idsubgrupo = rs.idsubgrupo
                                  JOIN grupos g ON g.idgrupo = sg.idgrupo
                                  WHERE pm.idusuario = ?
                                  ORDER BY g.nome_grupo, sg.nome_subgrupo";
                        $stmt = $conn->prepare($query);
                        if (!$stmt) {
                            error_log("Erro na consulta (preceptor/grupos.php): " . $conn->error);
                            die("Erro ao processar a consulta. Tente novamente mais tarde.");
                        }
                        $stmt->bind_param("i", $idusuario);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $subgruposList = $result->fetch_all(MYSQLI_ASSOC);

                        $stmtAlunos = $conn->prepare("SELECT u.nome
                                                        FROM alunos_subgrupos asg
                                                        JOIN usuarios u ON u.idusuario = asg.idusuario
                                                        WHERE asg.idsubgrupo = ?
                                                        ORDER BY u.nome");
                        foreach ($subgruposList as $idx => $sg) {
                            $stmtAlunos->bind_param("i", $sg['idsubgrupo']);
                            $stmtAlunos->execute();
                            $subgruposList[$idx]['alunos'] = $stmtAlunos->get_result()->fetch_all(MYSQLI_ASSOC);
                        }
                    }
                    ?>

                    <?php if (empty($subgruposList)): ?>
                        <div class="alert alert-info">Você ainda não possui grupos/subgrupos vinculados aos seus módulos.</div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($subgruposList as $sg): ?>
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100">
                                        <div class="card-header">
                                            <i class="bi bi-people-fill"></i>
                                            Grupo <?php echo htmlspecialchars($sg['nome_grupo']); ?> -
                                            Subgrupo <?php echo htmlspecialchars($sg['nome_subgrupo']); ?>
                                        </div>
                                        <div class="card-body">
                                            <?php if (empty($sg['alunos'])): ?>
                                                <p class="text-muted mb-0">Nenhum aluno neste subgrupo.</p>
                                            <?php else: ?>
                                                <ul class="mb-0">
                                                    <?php foreach ($sg['alunos'] as $aluno): ?>
                                                        <li><?php echo htmlspecialchars($aluno['nome']); ?></li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
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