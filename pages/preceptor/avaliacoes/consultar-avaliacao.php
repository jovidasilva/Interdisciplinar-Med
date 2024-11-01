<?php
session_start();
if (empty($_SESSION["login"])) {
    echo "<script>location.href='../../index.php';</script>";
    exit();
}

require_once '../../cfg/config.php';

$id_usuario = $_GET['id']; // Obtém o ID do usuário da URL
$query = "SELECT a.nota, a.data_avaliacao, m.nome AS modulo_nome 
          FROM avaliacoes a 
          JOIN modulos m ON a.idmodulo = m.idmodulos 
          WHERE a.idusuario = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultar Avaliação</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../css/stylecoord.css">
</head>

<body>
    <header>
        <nav class="navbar navbar-home">
            <div class="container-fluid">
                <a href="../redirecionar.php" class="nodec">
                    <h2 class="navbar-brand">SISTEMA MEDICINA</h2>
                </a>
                <div class="d-flex align-items-center">
                    <span class="navbar-text me-3">Olá, Coordenador <?php echo htmlspecialchars($_SESSION["nome"]); ?></span>
                    <div class="user-img-container dropdown">
                        <img src="../../img/user.png" alt="User" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="perfil.php">Perfil</a></li>
                            <li><a class="dropdown-item" href='../../cadastro_e_login/logout.php'>Sair</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <main>
        <div class="card">
            <div class="card-body">
                <h3>Resultado da Avaliação</h3>
                <?php if ($result->num_rows > 0): 
                    $row = $result->fetch_assoc(); ?>
                    <p><strong>Módulo:</strong> <?php echo htmlspecialchars($row['modulo_nome']); ?></p>
                    <p><strong>Nota:</strong> <?php echo htmlspecialchars($row['nota']); ?></p>
                    <p><strong>Data da Avaliação:</strong> <?php echo htmlspecialchars($row['data_avaliacao']); ?></p>
                <?php else: ?>
                    <p>Nenhuma avaliação encontrada.</p>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer>
        <div class="card footer-home rounded-0">
            <div class="card-body">
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
</body>

</html>
