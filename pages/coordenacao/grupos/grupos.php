<?php
session_start();
if (empty($_SESSION["login"])) {
    echo "<script>location.href='../../index.php';</script>";
    exit();
}
include('../../../cfg/config.php');

$queryGrupos = "SELECT g.nome_grupo, s.nome_subgrupo, r.periodo, r.inicio, r.fim, m.nome_modulo, s.idsubgrupo 
                FROM grupos g 
                JOIN subgrupos s ON g.idgrupo = s.idgrupo 
                JOIN rodizios_subgrupos rs ON s.idsubgrupo = rs.idsubgrupo 
                JOIN rodizios r ON rs.idrodizio = r.idrodizio 
                JOIN modulos m ON r.idmodulo = m.idmodulo 
                WHERE 1=1";

$stmtGrupos = $conn->prepare($queryGrupos);
$stmtGrupos->execute();
$resultGrupos = $stmtGrupos->get_result();

$grupos = array();
while ($row = $resultGrupos->fetch_assoc()) {
    $nomeGrupo = $row['nome_grupo'];
    $nomeSubgrupo = $row['nome_subgrupo'];

    if (!isset($grupos[$nomeGrupo])) {
        $grupos[$nomeGrupo] = array();
    }

    if (!isset($grupos[$nomeGrupo][$nomeSubgrupo])) {
        $grupos[$nomeGrupo][$nomeSubgrupo] = array(
            'nome_modulo' => $row['nome_modulo'],
            'periodo' => $row['periodo'],
            'inicio' => $row['inicio'],
            'fim' => $row['fim'],
            'idsubgrupo' => $row['idsubgrupo']
        );
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grupos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../../css/style.css">
</head>

<body>

    <header>
        <?php include('../../../includes/navbar.php'); ?>
        <?php include('../../../includes/menu-lateral-coordenacao.php'); ?>
    </header>

    <main>
        <div class="container mt-3">
            <div class="card">
                <div class="card-body">
                    <h1>Grupos</h1>
                    <table class="table table-striped mt-3">
                        <tbody>
                            <?php foreach ($grupos as $grupo => $subgrupos): ?>
                                <tr data-bs-toggle="collapse" data-bs-target="#grupo<?= htmlspecialchars($grupo) ?>"
                                    class="accordion-toggle">
                                    <td><?= htmlspecialchars($grupo) ?></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td colspan="6">
                                        <div id="grupo<?= htmlspecialchars($grupo) ?>" class="collapse">
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Subgrupo</th>
                                                        <th>Período</th>
                                                        <th>Ação</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($subgrupos as $subgrupoNome => $subgrupo): ?>
                                                        <tr>
                                                            <td><?= htmlspecialchars($subgrupoNome) ?></td>
                                                            <td><?= htmlspecialchars($subgrupo['periodo']) ?></td>
                                                            <td>
                                                                <form method="POST" action="ver-alunos.php">
                                                                    <input type="hidden" name="idsubgrupo"
                                                                        value="<?= $subgrupo['idsubgrupo'] ?>">
                                                                    <input type="hidden" name="nome_subgrupo"
                                                                        value="<?= $subgrupoNome ?>">
                                                                    <button type="submit" class="btn btn-info">Ver
                                                                        Alunos</button>
                                                                </form>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<?php
$stmtGrupos->close();
$conn->close();
?>