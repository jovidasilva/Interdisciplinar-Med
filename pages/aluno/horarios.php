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
    <title>Meus Horários - Aluno</title>
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
                    <h3>Meus Horários</h3>
                    <?php
                    $idusuario = $_SESSION['idusuario'] ?? null;
                    $horariosPorDia = [];
                    $diasOrdem = ['Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo'];

                    if ($idusuario) {
                        $query = "SELECT h.dia_semana, h.hora_inicio, h.hora_fim, h.local,
                                         u.nome_unidade, d.nome_departamento, m.nome_modulo,
                                         p.nome AS preceptor_nome
                                  FROM alunos_subgrupos asg
                                  JOIN horarios h ON h.idsubgrupo = asg.idsubgrupo
                                  JOIN unidades u ON h.idunidade = u.idunidade
                                  LEFT JOIN departamentos d ON h.iddepartamento = d.iddepartamento
                                  JOIN modulos m ON h.idmodulo = m.idmodulo
                                  JOIN usuarios p ON h.idpreceptor = p.idusuario
                                  WHERE asg.idusuario = ?
                                  ORDER BY FIELD(h.dia_semana, 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo'), h.hora_inicio";
                        $stmt = $conn->prepare($query);
                        if (!$stmt) {
                            error_log("Erro na consulta (aluno/horarios.php): " . $conn->error);
                            die("Erro ao processar a consulta. Tente novamente mais tarde.");
                        }
                        $stmt->bind_param("i", $idusuario);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        while ($row = $result->fetch_assoc()) {
                            $horariosPorDia[$row['dia_semana']][] = $row;
                        }
                    }
                    ?>

                    <?php if (empty($horariosPorDia)): ?>
                        <div class="alert alert-info">Você ainda não tem horários cadastrados.</div>
                    <?php else: ?>
                        <?php foreach ($diasOrdem as $dia): ?>
                            <?php if (!empty($horariosPorDia[$dia])): ?>
                                <h5 class="mt-4"><i class="bi bi-calendar-week"></i> <?php echo htmlspecialchars($dia); ?></h5>
                                <table class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Hora Início</th>
                                            <th>Hora Fim</th>
                                            <th>Módulo</th>
                                            <th>Unidade</th>
                                            <th>Departamento</th>
                                            <th>Preceptor</th>
                                            <th>Local</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($horariosPorDia[$dia] as $h): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars(substr($h['hora_inicio'], 0, 5)); ?></td>
                                                <td><?php echo htmlspecialchars(substr($h['hora_fim'], 0, 5)); ?></td>
                                                <td><?php echo htmlspecialchars($h['nome_modulo']); ?></td>
                                                <td><?php echo htmlspecialchars($h['nome_unidade']); ?></td>
                                                <td><?php echo htmlspecialchars($h['nome_departamento'] ?? '-'); ?></td>
                                                <td><?php echo htmlspecialchars($h['preceptor_nome']); ?></td>
                                                <td><?php echo htmlspecialchars($h['local'] ?? '-'); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        <?php endforeach; ?>
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