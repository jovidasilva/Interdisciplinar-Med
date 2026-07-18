<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['login']) || !in_array($_SESSION['tipo'] ?? null, [2, 3], true)) {
    header('Location: ' . str_repeat('../', 3) . 'index.php');
    exit();
}
if (!isset($conn)) {
    require_once __DIR__ . '/' . str_repeat('../', 3) . 'cfg/config.php';
}
require_once __DIR__ . '/' . str_repeat('../', 3) . 'includes/csrf.php';
?>
<?php

$acao = $_GET['acao'] ?? null;

switch ($acao) {

    case 'editar':

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['idpergunta'], $_POST['titulo'], $_POST['descricao'])) {

            csrf_verify_or_die();

            $idpergunta = (int) $_POST['idpergunta'];
            $titulo = $_POST['titulo'];
            $descricao = $_POST['descricao'];

            $query = "UPDATE perguntas_avaliacoes SET titulo = ?, descricao = ? WHERE idpergunta = ?";

            $stmt = $conn->prepare($query);

            $stmt->bind_param('ssi', $titulo, $descricao, $idpergunta);

            if ($stmt->execute()) {

                echo "<script>location.href='?page=listar-perguntas';</script>";

            } else {

                echo "<script>alert('Não foi possível concluir a alteração.');</script>";

            }

        } else {

            $idpergunta = (int) $_GET['idpergunta'];
            $query = "SELECT * FROM perguntas_avaliacoes WHERE idpergunta = ?";

            $stmt = $conn->prepare($query);

            $stmt->bind_param('i', $idpergunta);

            $stmt->execute();

            $result = $stmt->get_result();

            $row = $result->fetch_assoc();

            $titulo = $row['titulo'];
            $descricao = $row['descricao'];

        }

        break;

    case 'excluir':

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['idpergunta'])) {

            csrf_verify_or_die();

            $idpergunta = (int) $_POST['idpergunta'];

            $sql = "DELETE FROM perguntas_avaliacoes WHERE idpergunta = ?";

            $stmt = $conn->prepare($sql);

            $stmt->bind_param("i", $idpergunta);

            if ($stmt->execute()) {

                echo "<script>location.href='?page=listar-perguntas';</script>";

            } else {

                echo "<script>alert('Não foi possível excluir a pergunta.');</script>";

            }

            $stmt->close();

        } else {

            // GET: apenas exibe a tela de confirmação. A exclusão só
            // acontece quando o formulário abaixo é submetido via POST.
            $idpergunta = (int) ($_GET["idpergunta"] ?? 0);

        }

        break;

    case "adicionar":

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['titulo'], $_POST['descricao'])) {
            csrf_verify_or_die();

            $titulo = $_POST["titulo"];
            $descricao = $_POST["descricao"];

            $sql = "INSERT INTO perguntas_avaliacoes (titulo, descricao, tipo_resposta, ativo) VALUES (?, ?, 'escala', 1)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $titulo, $descricao);
            $stmt->execute();
            $stmt->close();
            echo "<script>alert('Pergunta adicionada com sucesso!');</script>";
            echo "<script>location.href='?page=listar-perguntas';</script>";
        }
        break;
}

if ($acao == 'editar') {
    ?>
    <h2>Editar Pergunta de Avaliação</h2>
    <form action="" method="post">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="idpergunta" value="<?= htmlspecialchars($idpergunta); ?>">
        <div class="mb-3">
            <label for="titulo" class="form-label">Título da Pergunta</label>
            <input type="text" class="form-control" id="titulo" name="titulo" value="<?= htmlspecialchars($titulo); ?>"
                required>
        </div>
        <div class="mb-3">
            <label for="descricao" class="form-label">Descrição da Pergunta</label>
            <textarea class="form-control" id="descricao" name="descricao"
                required><?= htmlspecialchars($descricao); ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
        <a href="avaliacoes.php" class="btn btn-secondary">Cancelar</a>
    </form>
    <?php
} elseif ($acao == 'excluir' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    ?>
    <h2>Confirmar Exclusão</h2>
    <p>Tem certeza que deseja excluir esta pergunta? Esta ação não pode ser revertida.</p>
    <form action="" method="post">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="idpergunta" value="<?= htmlspecialchars($idpergunta); ?>">
        <button type="submit" class="btn btn-danger" name="excluir">Excluir Pergunta</button>
        <a href="avaliacoes.php" class="btn btn-secondary">Cancelar</a>
    </form>
    <?php
} elseif ($acao == 'adicionar') {
    ?>
    <h3>Adicionar Nova Pergunta</h3>
    <form action="" method="post">
        <?php echo csrf_field(); ?>
        <div class="mb-3">
            <label for="titulo" class="form-label">Título da Pergunta</label>
            <input type="text" class="form-control" id="titulo" name="titulo" required>
        </div>

        <div class="mb-3">
            <label for="descricao" class="form-label">Descrição da Pergunta</label>
            <textarea class="form-control" id="descricao" name="descricao" required></textarea>
        </div>

        <button type="submit" name="nova_pergunta" class="btn btn-success">Adicionar Pergunta</button>
    </form>
    <?php
}

?>