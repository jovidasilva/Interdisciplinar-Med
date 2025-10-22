<?php
session_start();

include('../../cfg/config.php');

if (empty($_SESSION["login"])) {
    echo "<script>location.href='../../index.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rodízios do Preceptor</title>
    <style>
        .bg-custom-green { background-color: #005E00 !important; color: white !important; }
        .border-custom-green { border-color: #005E00 !important; }
        .badge-custom-green { background-color: #005E00 !important; color: white !important; border-radius: 20px !important; padding: 5px 12px !important; }
    </style>
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
                    <h3>Meus Rodízios</h3>
                    <p class="text-muted">Esta página mostra os módulos em que você está associado e que estão atualmente atribuídos a grupos.</p>

                    <?php
                    $idpreceptor = $_SESSION['idusuario'] ?? null;
                    if ($idpreceptor) {
                        $rodizios = [];
                        // Consulta principal para rodízios do preceptor
                        $sql = "SELECT m.idmodulo, m.nome_modulo, m.periodo, r.inicio AS rodizio_inicio, r.fim AS rodizio_fim,
                                       GROUP_CONCAT(DISTINCT sg.nome_subgrupo ORDER BY sg.nome_subgrupo SEPARATOR ', ') AS subgrupos
                                FROM preceptores_modulos pm
                                JOIN modulos m ON pm.idmodulo = m.idmodulo
                                JOIN rodizios r ON r.idmodulo = m.idmodulo
                                JOIN rodizios_subgrupos rs ON rs.idrodizio = r.idrodizio
                                JOIN subgrupos sg ON sg.idsubgrupo = rs.idsubgrupo
                                WHERE pm.idusuario = ?
                                  AND (pm.data_inicio IS NULL OR pm.data_inicio <= r.fim)
                                  AND (pm.data_fim IS NULL OR pm.data_fim >= r.inicio)
                                GROUP BY m.idmodulo, m.nome_modulo, m.periodo, r.inicio, r.fim
                                ORDER BY CAST(m.periodo AS UNSIGNED), m.nome_modulo, r.inicio";

                        if ($stmt = $conn->prepare($sql)) {
                            $stmt->bind_param("i", $idpreceptor);
                            if ($stmt->execute()) {
                                $res = $stmt->get_result();
                                while ($row = $res->fetch_assoc()) {
                                    $subgruposStr = $row['subgrupos'];
                                    $subgruposList = $subgruposStr ? explode(', ', $subgruposStr) : [];

                                    // Formatar datas
                                    $dataInicio = $row['rodizio_inicio'] ? date('d/m/Y', strtotime($row['rodizio_inicio'])) : 'Não definida';
                                    $dataFim = $row['rodizio_fim'] ? date('d/m/Y', strtotime($row['rodizio_fim'])) : 'Não definida';

                                    $rodizios[] = [
                                        'idmodulo' => $row['idmodulo'],
                                        'nomeModulo' => $row['nome_modulo'],
                                        'periodo' => $row['periodo'],
                                        'dataInicio' => $dataInicio,
                                        'dataFim' => $dataFim,
                                        'subgrupos' => $subgruposList
                                    ];
                                }
                            }
                            $stmt->close();
                        }

                        // Subgrupos permitidos (tem horário)
                        $subPermitidos = [];
                        $sqlPermitidos = "SELECT DISTINCT sg.nome_subgrupo
                                         FROM horarios h
                                         JOIN subgrupos sg ON sg.idsubgrupo = h.idsubgrupo
                                         WHERE h.idpreceptor = ?";
                        if ($stmtP = $conn->prepare($sqlPermitidos)) {
                            $stmtP->bind_param("i", $idpreceptor);
                            if ($stmtP->execute()) {
                                $resP = $stmtP->get_result();
                                while ($r = $resP->fetch_row()) {
                                    $subPermitidos[] = $r[0];
                                }
                            }
                            $stmtP->close();
                        }

                        // Filtrar subgrupos
                        foreach ($rodizios as &$rod) {
                            $rod['subgrupos'] = array_values(array_filter($rod['subgrupos'], function ($sg) use ($subPermitidos) {
                                return in_array($sg, $subPermitidos);
                            }));
                        }
                        unset($rod);

                        // Agrupar por módulo
                        $rodiziosPorModulo = [];
                        foreach ($rodizios as $rod) {
                            $rodiziosPorModulo[$rod['nomeModulo']][] = $rod;
                        }

                        if (empty($rodizios)) {
                            echo '<div class="alert alert-info"><i class="bi bi-info-circle-fill"></i> Você não está associado a nenhum módulo que esteja atribuído a grupos no momento.</div>';
                        }

                        // Renderizar cartões
                        foreach ($rodiziosPorModulo as $nomeModulo => $lista) {
                            echo '<div class="mb-5"><h4 class="mb-3 border-bottom pb-2">' . htmlspecialchars($nomeModulo) . '</h4><div class="row">';
                            foreach ($lista as $rod) {
                                echo '<div class="col-md-6 col-lg-4 mb-4">';
                                echo '  <div class="card h-100 border-custom-green">';
                                echo '    <div class="card-header bg-custom-green"></div>';
                                echo '    <div class="card-body">';
                                echo '      <div class="row mb-3">';
                                echo '        <div class="col-md-6"><p class="mb-1"><strong>Data Início:</strong></p><p>' . htmlspecialchars($rod['dataInicio']) . '</p></div>';
                                echo '        <div class="col-md-6"><p class="mb-1"><strong>Data Fim:</strong></p><p>' . htmlspecialchars($rod['dataFim']) . '</p></div>';
                                echo '      </div>';

                                // Subgrupos layout A/B/C
                                $subA = array_filter($rod['subgrupos'], fn($s) => strpos($s, 'A') === 0);
                                $subB = array_filter($rod['subgrupos'], fn($s) => strpos($s, 'B') === 0);
                                $subC = array_filter($rod['subgrupos'], fn($s) => strpos($s, 'C') === 0);
                                $outros = array_filter($rod['subgrupos'], fn($s) => !in_array($s, array_merge($subA, $subB, $subC)));

                                echo '<p class="mb-1"><strong>Subgrupos:</strong></p>';
                                echo '<table style="width:100%;border-collapse:separate;border-spacing:0;"><tr>';
                                // Coluna A
                                echo '<td style="vertical-align:top;padding:0;text-align:left;">';
                                foreach ($subA as $sg) { echo '<span class="badge-custom-green" style="display:inline-block;margin-bottom:5px;">' . htmlspecialchars($sg) . '</span><br>'; }
                                echo '</td>';
                                // Coluna B
                                echo '<td style="vertical-align:top;padding:0;text-align:left;">';
                                foreach ($subB as $sg) { echo '<span class="badge-custom-green" style="display:inline-block;margin-bottom:5px;">' . htmlspecialchars($sg) . '</span><br>'; }
                                echo '</td>';
                                // Coluna C
                                echo '<td style="vertical-align:top;padding:0;text-align:left;">';
                                foreach ($subC as $sg) { echo '<span class="badge-custom-green" style="display:inline-block;margin-bottom:5px;">' . htmlspecialchars($sg) . '</span><br>'; }
                                echo '</td>';
                                echo '</tr></table>';

                                if (empty($rod['subgrupos'])) {
                                    echo '<span class="text-muted">Nenhum subgrupo disponível</span>';
                                } elseif (!empty($outros)) {
                                    foreach ($outros as $sg) {
                                        echo '<span class="badge-custom-green" style="display:inline-block;margin-bottom:5px;">' . htmlspecialchars($sg) . '</span> ';
                                    }
                                }

                                echo '    </div>'; // card-body
                                echo '  </div>'; // card
                                echo '</div>'; // col
                            }
                            echo '</div></div>'; // row e container modulo
                        }
                    } else {
                        echo '<div class="alert alert-danger">Usuário não autenticado.</div>';
                    }
                    ?>
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