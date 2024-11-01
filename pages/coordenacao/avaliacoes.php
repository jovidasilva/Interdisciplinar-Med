<?php
// Inicia a sessão para verificar a autenticação do usuário
session_start();
include('../../cfg/config.php');

// Verifica se o usuário está logado, caso contrário, redireciona para a página inicial
if (empty($_SESSION["login"])) {
    echo "<script>location.href='../../index.php';</script>";
    exit();
}

// Função para adicionar uma nova pergunta quando o formulário é enviado
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["nova_pergunta"])) {
    $titulo = $_POST["titulo"];
    $descricao = $_POST["descricao"];

    // Insere a nova pergunta no banco de dados com o tipo de resposta e ativo por padrão
    $sql = "INSERT INTO perguntas_avaliacoes (titulo, descricao, tipo_resposta, ativo) VALUES (?, ?, 'escala', 1)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $titulo, $descricao);
    $stmt->execute();
    $stmt->close();
}

// Função para excluir uma pergunta com base no ID passado pela URL
if (isset($_GET["excluir"])) {
    $idpergunta = (int)$_GET["excluir"];

    // Exclui a pergunta do banco de dados
    $sql = "DELETE FROM perguntas_avaliacoes WHERE idpergunta = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idpergunta);
    $stmt->execute();
    $stmt->close();
}

// Busca todas as perguntas existentes no banco para exibição
$sql = "SELECT * FROM perguntas_avaliacoes";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Perguntas de Avaliação</title>

    <!-- Importa CSS do Bootstrap, ícones e SweetAlert2 para estilos e alertas -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.4/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="../../css/style.css">
</head>

<body>
    <header>
        <?php include('../../includes/navbar.php'); ?>
        <?php include('../../includes/menu-lateral-coordenacao.php'); ?>
    </header>

    <main class="container my-4">
        <h2>Gerenciar Perguntas de Avaliação</h2>
        
        <!-- Lista de Perguntas Existentes -->
        <div class="list-group my-3">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="list-group-item">
                    <!-- Exibe o título e descrição da pergunta com as opções de resposta padrão -->
                    <h5><?php echo htmlspecialchars($row['titulo']); ?></h5>
                    <p><?php echo htmlspecialchars($row['descricao']); ?></p>
                    <p>Opções de Resposta: Insuficiente, Regular, Bom, Excelente</p>

                    <!-- Botão para excluir a pergunta com confirmação -->
                    <a href="?excluir=<?php echo $row['idpergunta']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir esta pergunta?')">Excluir</a>

                    <!-- Botão para editar a pergunta que leva para a página de edição -->
                    <a href="editar-avaliacoes.php?idpergunta=<?php echo $row['idpergunta']; ?>&titulo=<?php echo urlencode($row['titulo']); ?>&descricao=<?php echo urlencode($row['descricao']); ?>" class="btn btn-primary btn-sm">Editar</a>
                </div>
            <?php endwhile; ?>
        </div>

        <!-- Formulário para Adicionar Nova Pergunta -->
        <h3>Adicionar Nova Pergunta</h3>
        <form action="" method="post">
            <!-- Campo para título da pergunta -->
            <div class="mb-3">
                <label for="titulo" class="form-label">Título da Pergunta</label>
                <input type="text" class="form-control" id="titulo" name="titulo" required>
            </div>

            <!-- Campo para descrição da pergunta -->
            <div class="mb-3">
                <label for="descricao" class="form-label">Descrição da Pergunta</label>
                <input type="text" class="form-control" id="descricao" name="descricao" required>
            </div>

            <!-- Botão para enviar o formulário e adicionar a pergunta -->
            <button type="submit" name="nova_pergunta" class="btn btn-success">Adicionar Pergunta</button>
        </form>
    </main>

    <footer>
        <div class="card footer-home rounded-0">
            <div class="card-body"></div>
        </div>
    </footer>

    <!-- Scripts do Bootstrap e SweetAlert2 para funcionalidades e alertas -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.4/dist/sweetalert2.all.min.js"></script>
</body>
</html>
