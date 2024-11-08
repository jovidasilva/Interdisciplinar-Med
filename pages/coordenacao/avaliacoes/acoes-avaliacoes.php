<?php

include("../../../cfg/config.php");

$acao = $_GET['acao'] ?? null;

switch ($acao) {

    case 'editar':

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['idpergunta'], $_POST['titulo'], $_POST['descricao'])) {

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

        $idpergunta = (int) $_GET["idpergunta"];

        $sql = "DELETE FROM perguntas_avaliacoes WHERE idpergunta = ?";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param("i", $idpergunta);

        if ($stmt->execute()) {


            echo "<script>location.href='?page=listar-perguntas';</script>";

        } else {

            echo "<script>alert('Não foi possível excluir a pergunta.');</script>";

        }

        $stmt->close();

        break;

    case "adicionar":

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['titulo'], $_POST['descricao'])) {
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
} elseif ($acao == 'excluir') {
    ?>
    <form action="" method="post">
        <input type="hidden" name="idpergunta" value="<?= htmlspecialchars($_GET["idpergunta"]); ?>">
        <button type="submit" class="btn btn-danger" name="excluir">Excluir Pergunta</button>
        <a href="avaliacoes.php" class="btn btn-secondary">Cancelar</a>
    </form>
    <?php
} elseif ($acao == 'adicionar') {
    ?>
    <h3>Adicionar Nova Pergunta</h3>
    <form action="" method="post">
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