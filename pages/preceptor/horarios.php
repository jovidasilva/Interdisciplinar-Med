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
    <title>Horários do Preceptor</title>
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
                    <h3>Meus Horários</h3>
                    <table class="table table-striped table-secondary table-bordered">
                        <thead>
                            <tr>
                                <th>Dia da Semana</th>
                                <th>Hora Início</th>
                                <th>Hora Fim</th>
                                <th>Módulo</th>
                                <th>Unidade</th>
                                <th>Subgrupo</th>
                                <th>Período</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $idpreceptor = $_SESSION['idusuario'] ?? null;

                            if ($idpreceptor) {
                                $sql = "SELECT h.*, u.nome_unidade, m.nome_modulo, m.periodo, sg.nome_subgrupo
                                        FROM horarios h
                                        JOIN unidades u ON h.idunidade = u.idunidade
                                        JOIN modulos m ON h.idmodulo = m.idmodulo
                                        JOIN subgrupos sg ON h.idsubgrupo = sg.idsubgrupo
                                        WHERE h.idpreceptor = ?
                                        ORDER BY FIELD(h.dia_semana, 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo'), h.hora_inicio";

                                $stmt = $conn->prepare($sql);
                                $stmt->bind_param("i", $idpreceptor);
                                if ($stmt->execute()) {
                                    $res = $stmt->get_result();
                                    if ($res->num_rows > 0) {
                                        while ($row = $res->fetch_object()) {
                                            echo "<tr>";
                                            echo "<td>" . htmlspecialchars($row->dia_semana) . "</td>";
                                            echo "<td>" . htmlspecialchars(date('H:i', strtotime($row->hora_inicio))) . "</td>";
                                            echo "<td>" . htmlspecialchars(date('H:i', strtotime($row->hora_fim))) . "</td>";
                                            echo "<td>" . htmlspecialchars($row->nome_modulo) . "</td>";
                                            echo "<td>" . htmlspecialchars($row->nome_unidade) . "</td>";
                                            echo "<td>" . htmlspecialchars($row->nome_subgrupo) . "</td>";
                                            echo "<td>" . htmlspecialchars($row->periodo) . "</td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='7'>Nenhum horário encontrado.</td></tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='7'>Erro ao buscar horários: " . htmlspecialchars($stmt->error) . "</td></tr>";
                                }
                                $stmt->close();
                            } else {
                                echo "<tr><td colspan='7'>Usuário não autenticado.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
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
