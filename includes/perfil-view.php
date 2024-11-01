<?php
include('../cfg/config.php');

$stmt = $conn->prepare("SELECT nome, email, telefone, registro FROM usuarios WHERE idusuario = ?");
$stmt->bind_param("i", $_SESSION['login']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    echo "Usuário não encontrado.";
    exit;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuário</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <div class="container mt-5">
        <h2>Informações do Usuário</h2>


        <form action="editar_usuario.php" method="POST">
            <h3>Dados de contato</h3>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="telefone" class="form-label">Telefone</label>
                <input type="text" class="form-control" id="telefone" name="telefone" value="<?php echo htmlspecialchars($user['telefone']); ?>" required>
            </div>
            <button type="submit" class="btn btn-success">Salvar Alterações</button>
            <a onclick="history.back()" class="btn btn-secondary">Voltar</a>
        </form>
    </div>
</body>

</html>