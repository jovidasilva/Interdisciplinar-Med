<?php
session_start();
require_once('../../../cfg/config.php');

// Verifica autenticação
if (empty($_SESSION["login"])) {
    header("Location: ../../index.php");
    exit();
}

// Verificar permissão de acesso para preceptor
$usuarioId = $_SESSION['idusuario'] ?? null;
if (!$usuarioId) {
    die("Usuário não autenticado.");
}

// Buscar grupos do preceptor
$gruposPreceptor = [];
try {
    $query = "SELECT g.nome_grupo, s.nome_subgrupo 
              FROM grupos g
              JOIN subgrupos s ON g.idgrupo = s.idgrupo
              JOIN preceptores_subgrupos ps ON s.idsubgrupo = ps.idsubgrupo
              WHERE ps.idusuario = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $usuarioId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $gruposPreceptor[] = $row;
    }
} catch (Exception $e) {
    error_log("Erro ao buscar grupos: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grupos - Preceptor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body>
    <header>
        <?php include('../../includes/navbar.php'); ?>
        <?php include('../../includes/menu-lateral-preceptor.php'); ?>
    </header>
    
    <main class="container mt-4">
        <div class="card">
            <div class="card-header">
                <h2>Meus Grupos</h2>
            </div>
            <div class="card-body">
                <?php if (!empty($gruposPreceptor)): ?>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Grupo</th>
                                <th>Subgrupo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($gruposPreceptor as $grupo): ?>
                                <tr>
                                    <td><?= htmlspecialchars($grupo['nome_grupo']) ?></td>
                                    <td><?= htmlspecialchars($grupo['nome_subgrupo']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="alert alert-info">Nenhum grupo encontrado.</p>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer>
        <?php include('../../includes/footer.php'); ?>
    </footer>

    <script src="https://cdn.jsdelivr.net ```html
    /npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
``` ```php
    </html>