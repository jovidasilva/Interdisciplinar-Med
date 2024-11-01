<?php
// Inicia a sessão para verificar o login do usuário
session_start();
include('../../cfg/config.php');

// Verifica se o usuário está logado
if (empty($_SESSION["login"])) {
    // Redireciona para a página inicial se o usuário não estiver logado
    echo "<script>location.href='../../index.php';</script>";
    exit();
}

// Obtém o ID, título e descrição da pergunta da URL, caso estejam definidos
$id = $_GET['idpergunta'] ?? null;
$titulo = $_GET['titulo'] ?? '';
$descricao = $_GET['descricao'] ?? '';

// Verifica se o formulário foi enviado com dados válidos
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['idpergunta'], $_POST['titulo'], $_POST['descricao'])) {
    $id = (int)$_POST['idpergunta'];
    $titulo = $_POST['titulo'];
    $descricao = $_POST['descricao'];

    // Prepara a consulta SQL para atualizar a pergunta no banco de dados
    $query = "UPDATE perguntas_avaliacoes SET titulo = ?, descricao = ? WHERE idpergunta = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ssi', $titulo, $descricao, $id);

    // Executa a atualização e verifica se foi bem-sucedida
    if ($stmt->execute()) {
        // Redireciona para `avaliacoes.php` após a atualização
        header("Location: avaliacoes.php");
        exit();
    } else {
        // Exibe mensagem de erro se a atualização falhar
        echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Erro!',
                    text: 'Erro ao atualizar a pergunta. Tente novamente.',
                    showConfirmButton: false,
                    timer: 1500
                });
              </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Pergunta de Avaliação</title>

    <!-- CSS do Bootstrap e SweetAlert2 para estilos e alertas -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.4/dist/sweetalert2.min.css">
</head>

<body>
    <main class="container my-4">
        <h2>Editar Pergunta de Avaliação</h2>
        
        <!-- Formulário para editar a pergunta -->
        <form action="" method="post">
            <!-- Campo oculto para enviar o ID da pergunta -->
            <input type="hidden" name="idpergunta" value="<?php echo htmlspecialchars($id); ?>">
            
            <!-- Campo para editar o título da pergunta -->
            <div class="mb-3">
                <label for="titulo" class="form-label">Título da Pergunta</label>
                <input type="text" class="form-control" id="titulo" name="titulo" value="<?php echo htmlspecialchars($titulo); ?>" required>
            </div>

            <!-- Campo para editar a descrição da pergunta -->
            <div class="mb-3">
                <label for="descricao" class="form-label">Descrição da Pergunta</label>
                <input type="text" class="form-control" id="descricao" name="descricao" value="<?php echo htmlspecialchars($descricao); ?>" required>
            </div>

            <!-- Botões para salvar as alterações ou cancelar a edição -->
            <button type="submit" class="btn btn-primary">Salvar Alterações</button>
            <a href="avaliacoes.php" class="btn btn-secondary">Cancelar</a>
        </form>
    </main>

    <!-- Scripts do SweetAlert2 para alertas de feedback -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.4/dist/sweetalert2.all.min.js"></script>
</body>
</html>

