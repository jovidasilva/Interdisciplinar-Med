<?php
session_start();
if (empty($_SESSION["login"])) {
    echo "<script>location.href='../../index.php';</script>";
    exit();
}
include('../../../cfg/config.php');

$queryPeriodos = "SELECT DISTINCT periodo FROM rodizios ORDER BY periodo";
$resultPeriodos = $conn->query($queryPeriodos);

$queryModulos = "SELECT idmodulo, nome_modulo FROM modulos ORDER BY nome_modulo";
$resultModulos = $conn->query($queryModulos);

$selectedPeriodo = isset($_POST['periodo']) ? $_POST['periodo'] : '';
$selectedModulo = isset($_POST['modulo']) ? $_POST['modulo'] : '';

$queryGrupos = "SELECT g.nome_grupo, s.nome_subgrupo, r.periodo, r.inicio, r.fim, m.nome_modulo, s.idsubgrupo 
                FROM grupos g 
                JOIN subgrupos s ON g.idgrupo = s.idgrupo 
                JOIN rodizios_subgrupos rs ON s.idsubgrupo = rs.idsubgrupo 
                JOIN rodizios r ON rs.idrodizio = r.idrodizio 
                JOIN modulos m ON r.idmodulo = m.idmodulo 
                WHERE 1=1";

if ($selectedPeriodo) {
    $queryGrupos .= " AND r.periodo = ?";
}
if ($selectedModulo) {
    $queryGrupos .= " AND r.idmodulo = ?";
}

$stmtGrupos = $conn->prepare($queryGrupos);
if ($selectedPeriodo && $selectedModulo) {
    $stmtGrupos->bind_param("ii", $selectedPeriodo, $selectedModulo);
} elseif ($selectedPeriodo) {
    $stmtGrupos->bind_param("i", $selectedPeriodo);
} elseif ($selectedModulo) {
    $stmtGrupos->bind_param("i", $selectedModulo);
}
$stmtGrupos->execute();
$resultGrupos = $stmtGrupos->get_result();
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualização de Grupos e Subgrupos</title>
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
                    <h1>Visualização de Grupos e Subgrupos</h1>
                    <h2 class="mt-4">Grupos e Subgrupos</h2>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Grupo</th>
                                <th>Subgrupo</th>
                                <th>Módulo</th>
                                <th>Período</th>
                                <th>Data Início</th>
                                <th>Data Fim</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $resultGrupos->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['nome_grupo']) ?></td>
                                    <td><?= htmlspecialchars($row['nome_subgrupo']) ?></td>
                                    <td><?= htmlspecialchars($row['nome_modulo']) ?></td>
                                    <td><?= htmlspecialchars($row['periodo']) ?></td>
                                    <td><?= date("d/m/Y", strtotime($row['inicio'])) ?></td>
                                    <td><?= date("d/m/Y", strtotime($row['fim'])) ?></td>
                                    <td>
                                        <form method="POST" action="view-alunos.php">
                                            <input type="hidden" name="idsubgrupo" value="<?= $row['idsubgrupo'] ?>">
                                            <input type="hidden" name="nome_subgrupo" value="<?= $row['nome_subgrupo'] ?>">
                                            <button type="submit" class="btn btn-info">Ver Alunos</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
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
