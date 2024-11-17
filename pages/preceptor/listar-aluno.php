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
    <title>Lista de alunos</title>
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
                    <h3>Lista de Alunos</h3>
                    <table class="table table-striped table-secondary table-bordered">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Nota</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT u.nome, 
                                           COALESCE((SELECT a.nota 
                                                     FROM avaliacoes a 
                                                     WHERE a.idaluno = u.idusuario 
                                                     ORDER BY a.data_avaliacao DESC 
                                                     LIMIT 1), 'Sem nota') AS nota
                                    FROM usuarios u
                                    WHERE u.tipo = 0";

                            $res = $conn->query($sql);

                            if (!$res) {
                                die("Erro na consulta: " . $conn->error);
                            }

                            $qtd = $res->num_rows;

                            if ($qtd > 0) {
                                while ($row = $res->fetch_object()) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row->nome) . "</td>";
                                    echo "<td>" . htmlspecialchars($row->nota) . "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='2'>Nenhum aluno encontrado.</td></tr>";
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
            <div class="card-body">
            </div>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>

</html>
