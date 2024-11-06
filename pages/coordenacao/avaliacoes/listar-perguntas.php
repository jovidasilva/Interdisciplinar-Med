<?php

include('../../../cfg/config.php');

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


<h2>Gerenciar Perguntas de Avaliação <button class="btn btn-secondary" onclick="location.href='?page=avaliacoes'">Voltar</button></h2>

<div class="list-group my-3">
    <?php while ($row = $result->fetch_assoc()): ?>
        <div class="list-group-item">
            <h5><?php echo htmlspecialchars($row['titulo']); ?></h5>
            <p><?php echo htmlspecialchars($row['descricao']); ?></p>
            <p>Opções de Resposta: Insuficiente, Regular, Bom, Excelente</p>

            <a href="?excluir=<?php echo $row['idpergunta']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir esta pergunta?')">Excluir</a>

            <a href="?page=editar-avaliacoes&idpergunta=<?php echo $row['idpergunta']; ?>&titulo=<?php echo urlencode($row['titulo']); ?>&descricao=<?php echo urlencode($row['descricao']); ?>" class="btn btn-primary btn-sm">Editar</a>
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