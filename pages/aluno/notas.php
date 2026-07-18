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
    <title>Minhas Notas - Aluno</title>
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
                    <h3>Minhas Notas</h3>
                    <?php
                    $idusuario = $_SESSION['idusuario'] ?? null;
                    $avaliacoes = [];
                    $somaNotas = 0;

                    if ($idusuario) {
                        $query = "SELECT a.nota, a.data_avaliacao, m.nome_modulo, p.nome AS preceptor_nome
                                  FROM avaliacoes a
                                  JOIN modulos m ON a.idmodulo = m.idmodulo
                                  JOIN usuarios p ON a.idpreceptor = p.idusuario
                                  WHERE a.idaluno = ?
                                  ORDER BY a.data_avaliacao DESC";
                        $stmt = $conn->prepare($query);
                        if (!$stmt) {
                            error_log("Erro na consulta (aluno/notas.php): " . $conn->error);
                            die("Erro ao processar a consulta. Tente novamente mais tarde.");
                        }
                        $stmt->bind_param("i", $idusuario);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $avaliacoes = $result->fetch_all(MYSQLI_ASSOC);
                    }

                    $totalAvaliacoes = count($avaliacoes);
                    $media = 0;
                    if ($totalAvaliacoes > 0) {
                        foreach ($avaliacoes as $av) {
                            $somaNotas += (float) $av['nota'];
                        }
                        $media = $somaNotas / $totalAvaliacoes;
                    }
                    ?>

                    <?php if ($totalAvaliacoes === 0): ?>
                        <div class="alert alert-info">Você ainda não recebeu nenhuma avaliação.</div>
                    <?php else: ?>
                        <div class="mb-3">
                            <span class="badge bg-primary fs-6">Média Geral: <?php echo number_format($media, 2, ',', '.'); ?></span>
                            <span class="badge bg-secondary fs-6">Total de Avaliações: <?php echo $totalAvaliacoes; ?></span>
                        </div>
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Módulo</th>
                                    <th>Preceptor</th>
                                    <th>Nota</th>
                                    <th>Data</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($avaliacoes as $av): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($av['nome_modulo']); ?></td>
                                        <td><?php echo htmlspecialchars($av['preceptor_nome']); ?></td>
                                        <td><?php echo htmlspecialchars($av['nota']); ?></td>
                                        <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($av['data_avaliacao']))); ?></td>
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