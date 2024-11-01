<?php

session_start();
include('../../cfg/config.php');

if (empty($_SESSION["login"])) {
    echo "<script>location.href='../../index.php';</script>";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["nova_pergunta"])) {
    $titulo = $_POST["titulo"];
    $descricao = $_POST["descricao"];

    $sql = "INSERT INTO perguntas_avaliacoes (titulo, descricao, tipo_resposta, ativo) VALUES (?, ?, 'escala', 1)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $titulo, $descricao);
    $stmt->execute();
    $stmt->close();
}

if (isset($_GET["excluir"])) {
    $idpergunta = (int)$_GET["excluir"];

    $sql = "DELETE FROM perguntas_avaliacoes WHERE idpergunta = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idpergunta);
    $stmt->execute();
    $stmt->close();
}

$sql = "SELECT * FROM perguntas_avaliacoes";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Perguntas de Avaliação</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../css/style.css">
</head>

<body>
    <header>
        <?php include('../../includes/navbar.php'); ?>
        <?php include('../../includes/menu-lateral-coordenacao.php'); ?>
    </header>

    <main>
        <div class="container mt-3">
            <div class="card">
                <div class="card-body">
                    <h2>Gerenciar Perguntas de Avaliação</h2>

                    <div class="list-group my-3">
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <div class="list-group-item">
                                <h5><?php echo htmlspecialchars($row['titulo']); ?></h5>
                                <p><?php echo htmlspecialchars($row['descricao']); ?></p>
                                <p>Opções de Resposta: Insuficiente, Regular, Bom, Excelente</p>

                                <a href="?excluir=<?php echo $row['idpergunta']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir esta pergunta?')">Excluir</a>

                                <a href="editar-avaliacoes.php?idpergunta=<?php echo $row['idpergunta']; ?>&titulo=<?php echo urlencode($row['titulo']); ?>&descricao=<?php echo urlencode($row['descricao']); ?>" class="btn btn-primary btn-sm">Editar</a>
                            </div>
                        <?php endwhile; ?>
                    </div>

                    <h3>Adicionar Nova Pergunta</h3>
                    <form action="" method="post">
                        <div class="mb-3">
                            <label for="titulo" class="form-label">Título da Pergunta</label>
                            <input type="text" class="form-control" id="titulo" name="titulo" required>
                        </div>

                        <div class="mb-3">
                            <label for="descricao" class="form-label">Descrição da Pergunta</label>
                            <input type="text" class="form-control" id="descricao" name="descricao" required>
                        </div>

                        <button type="submit" name="nova_pergunta" class="btn btn-success">Adicionar Pergunta</button>
                    </form>
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