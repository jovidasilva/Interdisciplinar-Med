<?php
session_start();
if (empty($_SESSION["login"])) {
    echo "<script>location.href='../../index.php';</script>";
    exit();
}
include('../../cfg/config.php');

$idsubgrupo = isset($_GET['idsubgrupo']) ? intval($_GET['idsubgrupo']) : 0;
$idpreceptor = $_SESSION['idusuario'] ?? null;
if (!$idpreceptor || !$idsubgrupo) {
    echo "<script>location.href='grupos.php';</script>";
    exit();
}

// Verifica se preceptor possui acesso a esse subgrupo
$stmtCheck = $conn->prepare("SELECT COUNT(*) FROM horarios WHERE idpreceptor = ? AND idsubgrupo = ?");
$stmtCheck->bind_param("ii", $idpreceptor, $idsubgrupo);
$stmtCheck->execute();
$stmtCheck->bind_result($temAcesso);
$stmtCheck->fetch();
$stmtCheck->close();

if ($temAcesso == 0) {
    echo "<script>alert('Você não tem acesso a este subgrupo.');location.href='grupos.php';</script>";
    exit();
}

// Obtém nome do subgrupo
$nomeSubgrupo = '';
$stmtNome = $conn->prepare("SELECT nome_subgrupo FROM subgrupos WHERE idsubgrupo = ?");
$stmtNome->bind_param("i", $idsubgrupo);
$stmtNome->execute();
$stmtNome->bind_result($nomeSubgrupo);
$stmtNome->fetch();
$stmtNome->close();

// Lista alunos
$alunos = [];
$sqlAlunos = "SELECT u.idusuario, u.nome, u.registro FROM usuarios u JOIN alunos_subgrupos als ON u.idusuario = als.idusuario WHERE als.idsubgrupo = ? AND u.tipo = 0 ORDER BY u.nome";
$stmtA = $conn->prepare($sqlAlunos);
$stmtA->bind_param("i", $idsubgrupo);
if ($stmtA->execute()) {
    $resA = $stmtA->get_result();
    while ($row = $resA->fetch_assoc()) {
        // verifica se já foi avaliado
        $avaliado = false;
        $stmtAval = $conn->prepare("SELECT 1 FROM avaliacoes WHERE idaluno = ? AND idpreceptor = ? LIMIT 1");
        $stmtAval->bind_param("ii", $row['idusuario'], $idpreceptor);
        $stmtAval->execute();
        $stmtAval->store_result();
        $avaliado = $stmtAval->num_rows > 0;
        $stmtAval->close();

        $alunos[] = [
            'idusuario' => $row['idusuario'],
            'nome' => $row['nome'],
            'registro' => $row['registro'],
            'avaliado' => $avaliado
        ];
    }
}
$stmtA->close();
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Integrantes do Subgrupo <?php echo htmlspecialchars($nomeSubgrupo); ?></title>
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
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3>Integrantes do Subgrupo <?php echo htmlspecialchars($nomeSubgrupo); ?></h3>
                        <a href="grupos.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Voltar</a>
                    </div>
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Registro</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($alunos)) {
                                foreach ($alunos as $al) { ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($al['nome']); ?></td>
                                        <td><?php echo htmlspecialchars($al['registro']); ?></td>
                                        <td>
                                            <?php if ($al['avaliado']) { ?>
                                                <span class="badge text-bg-success">Avaliado</span>
                                            <?php } else { ?>
                                                <span class="badge text-bg-warning">Não avaliado</span>
                                            <?php } ?>
                                        </td>
                                    </tr>
                            <?php }
                            } else {
                                echo '<tr><td colspan="3">Nenhum aluno encontrado neste subgrupo.</td></tr>'; } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
    <footer>
        <div class="card footer-home rounded-0"><div class="card-body"></div></div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
