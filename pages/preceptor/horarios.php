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
    <title>Horários do Preceptor</title>
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
                    <h3>Meus Horários</h3>
                    <?php
                    $idusuario = $_SESSION['idusuario'] ?? null;
                    $horarios = [];

                    if ($idusuario) {
                        $query = "SELECT h.dia_semana, h.hora_inicio, h.hora_fim, h.local,
                                         u.nome_unidade, d.nome_departamento, m.nome_modulo, sg.nome_subgrupo
                                  FROM horarios h
                                  JOIN unidades u ON h.idunidade = u.idunidade
                                  LEFT JOIN departamentos d ON h.iddepartamento = d.iddepartamento
                                  JOIN modulos m ON h.idmodulo = m.idmodulo
                                  JOIN subgrupos sg ON h.idsubgrupo = sg.idsubgrupo
                                  WHERE h.idpreceptor = ?
                                  ORDER BY FIELD(h.dia_semana, 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo'), h.hora_inicio";
                        $stmt = $conn->prepare($query);
                        if (!$stmt) {
                            error_log("Erro na consulta (preceptor/horarios.php): " . $conn->error);
                            die("Erro ao processar a consulta. Tente novamente mais tarde.");
                        }
                        $stmt->bind_param("i", $idusuario);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $horarios = $result->fetch_all(MYSQLI_ASSOC);
                    }
                    ?>

                    <?php if (empty($horarios)): ?>
                        <div class="alert alert-info">Você ainda não tem horários cadastrados.</div>
                    <?php else: ?>
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Dia</th>
                                    <th>Hora Início</th>
                                    <th>Hora Fim</th>
                                    <th>Módulo</th>
                                    <th>Unidade</th>
                                    <th>Departamento</th>
                                    <th>Subgrupo</th>
                                    <th>Local</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($horarios as $h): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($h['dia_semana']); ?></td>
                                        <td><?php echo htmlspecialchars(substr($h['hora_inicio'], 0, 5)); ?></td>
                                        <td><?php echo htmlspecialchars(substr($h['hora_fim'], 0, 5)); ?></td>
                                        <td><?php echo htmlspecialchars($h['nome_modulo']); ?></td>
                                        <td><?php echo htmlspecialchars($h['nome_unidade']); ?></td>
                                        <td><?php echo htmlspecialchars($h['nome_departamento'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($h['nome_subgrupo']); ?></td>
                                        <td><?php echo htmlspecialchars($h['local'] ?? '-'); ?></td>
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
