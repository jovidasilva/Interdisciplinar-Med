<?php
session_start();
if (empty($_SESSION["login"]) || !in_array($_SESSION['tipo'] ?? null, [0], true)) {
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
    <title>Meus Rodízios - Aluno</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../css/style.css?v=<?php echo ASSET_VERSION; ?>">
</head>

<body>
    <header>
        <?php include('../../includes/navbar.php'); ?>
        <?php include('../../includes/menu-lateral-aluno.php'); ?>
    </header>
    <main>
        <div class="container mt-3">
            <div class="card">
                <div class="card-body">
                    <h3>Meus Rodízios</h3>
                    <?php
                    $idusuario = $_SESSION['idusuario'] ?? null;
                    $rodizios = [];

                    if ($idusuario) {
                        $query = "SELECT DISTINCT r.idrodizio, r.periodo, r.inicio, r.fim, m.nome_modulo
                                  FROM alunos_subgrupos asg
                                  JOIN rodizios_subgrupos rs ON rs.idsubgrupo = asg.idsubgrupo
                                  JOIN rodizios r ON r.idrodizio = rs.idrodizio
                                  JOIN modulos m ON m.idmodulo = r.idmodulo
                                  WHERE asg.idusuario = ?
                                  ORDER BY r.inicio";
                        $stmt = $conn->prepare($query);
                        if (!$stmt) {
                            error_log("Erro na consulta (aluno/rodizios.php): " . $conn->error);
                            die("Erro ao processar a consulta. Tente novamente mais tarde.");
                        }
                        $stmt->bind_param("i", $idusuario);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $rodizios = $result->fetch_all(MYSQLI_ASSOC);
                    }

                    $hoje = date('Y-m-d');
                    ?>

                    <?php if (empty($rodizios)): ?>
                        <div class="alert alert-info">Você ainda não está em nenhum rodízio.</div>
                    <?php else: ?>
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Módulo</th>
                                    <th>Período</th>
                                    <th>Data Início</th>
                                    <th>Data Fim</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rodizios as $r): ?>
                                    <?php
                                    if ($hoje < $r['inicio']) {
                                        $status = '<span class="badge bg-secondary">Futuro</span>';
                                    } elseif ($hoje > $r['fim']) {
                                        $status = '<span class="badge bg-success">Concluído</span>';
                                    } else {
                                        $status = '<span class="badge bg-warning text-dark">Em andamento</span>';
                                    }
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($r['nome_modulo']); ?></td>
                                        <td><?php echo htmlspecialchars($r['periodo']); ?></td>
                                        <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($r['inicio']))); ?></td>
                                        <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($r['fim']))); ?></td>
                                        <td><?php echo $status; ?></td>
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