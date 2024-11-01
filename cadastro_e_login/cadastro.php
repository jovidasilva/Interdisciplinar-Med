<?php
session_start();
include('../cfg/config.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $telefone = $_POST['telefone'];
    $registro = $_POST['registro'];
    $login = $_POST['login'];
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, telefone, registro, login, senha, tipo) VALUES (?, ?, ?, ?, ?, ?, -1)");
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: white;
            overflow: hidden;
        }

        .box-green {
            position: absolute;
            top: 0;
            left: 0;
            width: 65%;
            height: 100%;
            background-color: #1d780b;
            transform: skewX(-30deg);
            transform-origin: top left;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 15px -10px 20px rgba(0, 0, 0, 0.5); /* Sombra para criar efeito de profundidade */
        }

        .logo-container {
            transform: skewX(30deg);
            display: flex;
            align-items: flex-start;
            justify-content: flex-start;
            height: 100%;
            padding-top: 100px;
            padding-left: 20px;
        }

        .logo-container img {
            width: 550px;
        }

        .card-login {
    position: relative;
    z-index: 2;
    padding: 2rem;
    width: 500px;
    background-color: white;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
    border-radius: 8px;
    text-align: center;
    margin-left: 1500px; /* Ajuste este valor para mover o container para a direita */
}


        .card-login form {
            text-align: left;
        }

        .card-login h1 {
            margin-bottom: 1.5rem;
            font-weight: bold;
        }

        .card-login input {
            margin-bottom: 1rem;
            width: 100%;
            padding: 0.5rem;
            border-radius: 5px;
            border: 1px solid #ccc;
            
        }

        .card-login .btn-primary {
            width: 48%;
            padding: 0.5rem;
            background-color: #299E12;
            color: white;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
        }

        .card-login .btn-primary:hover {
            background-color: #217b10;
        }

        .card-login .btn-secondary {
            width: 48%;
            padding: 0.5rem;
            color: #6c757d;
            background-color: #e9ecef;
            border: 1px solid #6c757d;
            border-radius: 5px;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            cursor: pointer;
        }

        .card-login .btn-secondary:hover {
            background-color: #d6d8db;
            border-color: #5a6268;
        }
    </style>
</head>

<body>
    <div class="box-green">
        <div class="logo-container">
            <img src="../img/LogoCeuma.png" alt="Logo CEUMA">
        </div>
    </div>
    <div class="card-login">
        <h3 id="cadastro-title">Realize seu cadastro</h3>
        <form action="" method="POST">
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
            <div class="mb-3 d-flex justify-content-between">
                <button type="submit" class="btn btn-primary">Cadastrar</button>
                <a href="../index.php" class="btn btn-secondary">Voltar</a>
            </div>
        </form>
    </div>
</body>

</html>
