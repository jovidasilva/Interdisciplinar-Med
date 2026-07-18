<?php
session_start();
include('../cfg/config.php');
require_once __DIR__ . '/../includes/csrf.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    csrf_verify_or_die();

    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $telefone = $_POST['telefone'];
    $registro = $_POST['registro'];
    $login = $_POST['login'];
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, telefone, registro, login, senha, tipo) VALUES (?, ?, ?, ?, ? , ?, -1)");
    $stmt->bind_param("ssssss", $nome, $email, $telefone, $registro, $login, $senha);

    if ($stmt->execute()) {
        echo "<script>location.href='../index.php?alert=1';</script>";
    } else {
        echo "<script>location.href='cadastro.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css?v=<?php echo ASSET_VERSION; ?>">
    <style>
        /* Ajustes específicos desta tela: card mais largo (mais campos) e
           botão de submit estreito, em vez do padrão de largura total. */
        .card-login {
            width: 500px;
        }

        .card-login button[type="submit"] {
            width: 20%;
        }
    </style>
</head>

<body class="auth-page">
    <div class="card-login">
        <h3 id="cadastro-title">Realize seu cadastro</h3>
        <form action="" method="POST">
            <?php echo csrf_field(); ?>
            <div class="mb-3">
                <label for="nome">Nome</label>
                <input type="text" name="nome" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="email">Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="telefone">Telefone</label>
                <input type="text" name="telefone" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="registro">RA/CRM</label>
                <input type="text" name="registro" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="login">Login</label>
                <input type="text" name="login" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="senha">Senha</label>
                <input type="password" name="senha" class="form-control" required>
            </div>
            <div class="mb-3 d-flex justify-content-center gap-2">
                <button type="submit">Cadastrar</button>
            </div>
        </form>
        <a href="../index.php" class="btn btn-secondary">Voltar</a>
    </div>
</body>

</html>