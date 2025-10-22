<?php
session_start();
if (empty($_SESSION["login"])) {
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
    <title>Grupos do Preceptor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../css/style.css">
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
                    <h3>Meus Subgrupos</h3>
                    <?php
                    $idpreceptor = $_SESSION['idusuario'] ?? null;
                    if ($idpreceptor) {
                        $subgrupos = [];
                        $sql = "SELECT DISTINCT sg.idsubgrupo, sg.nome_subgrupo, g.idgrupo, g.nome_grupo
                                FROM subgrupos sg
                                JOIN grupos g ON sg.idgrupo = g.idgrupo
                                JOIN horarios h ON sg.idsubgrupo = h.idsubgrupo
                                WHERE h.idpreceptor = ?
                                ORDER BY g.nome_grupo, sg.nome_subgrupo";
                        if ($stmt = $conn->prepare($sql)) {
                            $stmt->bind_param("i", $idpreceptor);
                            if ($stmt->execute()) {
                                $res = $stmt->get_result();
                                while ($row = $res->fetch_assoc()) {
                                    // contagem de alunos no subgrupo
                                    $count = 0;
                                    $stmtC = $conn->prepare("SELECT COUNT(*) FROM alunos_subgrupos WHERE idsubgrupo = ?");
                                    $stmtC->bind_param("i", $row['idsubgrupo']);
                                    $stmtC->execute();
                                    $stmtC->bind_result($count);
                                    $stmtC->fetch();
                                    $stmtC->close();

                                    $subgrupos[] = [
                                        'idsubgrupo' => $row['idsubgrupo'],
                                        'nomeSubgrupo' => $row['nome_subgrupo'],
                                        'idgrupo' => $row['idgrupo'],
                                        'nomeGrupo' => $row['nome_grupo'],
                                        'totalAlunos' => $count
                                    ];
                                }
                            }
                            $stmt->close();
                        }
                    ?>
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Grupo</th>
                                <th>Subgrupo</th>
                                <th>Total de Alunos</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($subgrupos)) {
                                foreach ($subgrupos as $sg) { ?>
                                    <tr>
                                        <td><?= htmlspecialchars($sg['nomeGrupo']) ?></td>
                                        <td><?= htmlspecialchars($sg['nomeSubgrupo']) ?></td>
                                        <td><?= htmlspecialchars($sg['totalAlunos']) ?></td>
                                        <td>
                                            <a href="grupos-alunos.php?idsubgrupo=<?= urlencode($sg['idsubgrupo']) ?>" class="btn btn-primary btn-sm">
                                                <i class="bi bi-people"></i> Ver Integrantes
                                            </a>
                                        </td>
                                    </tr>
                                <?php } 
                            } else { ?>
                                <tr><td colspan="4">Nenhum subgrupo encontrado.</td></tr>
                            <?php } ?>
                        </tbody>
                    </table>
                    <?php } else { echo '<div class="alert alert-danger">Usuário não autenticado.</div>'; } ?>
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