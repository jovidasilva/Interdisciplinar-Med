<?php
session_start();
if (empty($_SESSION["login"])) {
    echo "<script>location.href='../../index.php';</script>";
    exit();
}
include('../../../cfg/config.php');


$idsubgrupo = isset($_POST['idsubgrupo']) ? $_POST['idsubgrupo'] : '';

$queryAlunos = "SELECT u.idusuario, u.nome
                FROM usuarios u 
                JOIN alunos_subgrupos asg ON u.idusuario = asg.idusuario 
                WHERE asg.idsubgrupo = ?";

$stmtAlunos = $conn->prepare($queryAlunos);
$stmtAlunos->bind_param("i", $idsubgrupo);
$stmtAlunos->execute();
$resultAlunos = $stmtAlunos->get_result();
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alunos do Subgrupo</title>
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
                    <h1>Alunos do Subgrupo</h1>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID do Aluno</th>
                                <th>Nome do Aluno</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $resultAlunos->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['idusuario']) ?></td>
                                    <td><?= htmlspecialchars($row['nome']) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <button onclick="history.back()" class="btn btn-secondary">Voltar</button>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<?php
$stmtAlunos->close();
$conn->close();
?>