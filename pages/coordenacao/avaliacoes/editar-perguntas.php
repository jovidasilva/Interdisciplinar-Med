<?php
include('../../../cfg/config.php');

$id = $_GET['idpergunta'] ?? null;
$titulo = $_GET['titulo'] ?? '';
$descricao = $_GET['descricao'] ?? '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['idpergunta'], $_POST['titulo'], $_POST['descricao'])) {
    $id = (int)$_POST['idpergunta'];
    $titulo = $_POST['titulo'];
    $descricao = $_POST['descricao'];

    $query = "UPDATE perguntas_avaliacoes SET titulo = ?, descricao = ? WHERE idpergunta = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ssi', $titulo, $descricao, $id);

    if ($stmt->execute()) {
        echo "<script>location.href='avaliacoes.php';</script>";
    } else {
        echo "<script>alert('Não foi possivel concluir a alteração.');</script>";
    }
}
?>

<h2>Editar Pergunta de Avaliação</h2>
<form action="" method="post">
    <input type="hidden" name="idpergunta" value="<?php echo htmlspecialchars($id); ?>">
    <div class="mb-3">
        <label for="titulo" class="form-label">Título da Pergunta</label>
        <input type="text" class="form-control" id="titulo" name="titulo" value="<?php echo htmlspecialchars($titulo); ?>" required>
    </div>
    <div class="mb-3">
        <label for="descricao" class="form-label">Descrição da Pergunta</label>
        <input type="text" class="form-control" id="descricao" name="descricao" value="<?php echo htmlspecialchars($descricao); ?>" required>
    </div>
    <button type="submit" class="btn btn-primary">Salvar Alterações</button>
    <a href="avaliacoes.php" class="btn btn-secondary">Cancelar</a>
</form>