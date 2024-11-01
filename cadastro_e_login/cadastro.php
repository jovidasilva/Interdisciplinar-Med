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
            width: 50vw;
            height: 100vh;
            background-color: #1d780b;
            transform: skewX(-25deg);
            transform-origin: top left;
            z-index: 1;
            display: flex;
            align-items: flex-start;
            justify-content: flex-start;
            box-shadow: 15px -10px 20px rgba(0, 0, 0, 0.5);
            padding: 1vh 6vw;
        }

        .logo-container {
            transform: skewX(25deg);
            display: flex;
            align-items: flex-start;
            justify-content: flex-start;
            height: auto;
        }

        .logo-container img {
            max-width: 50%;
            width: auto;
            height: auto;
            max-height: 30vh;
        }

        .card-login {
            position: relative;
            z-index: 2;
            padding: 1rem;
            width: 85vw;
            max-width: 400px;
            background-color: white;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
            border-radius: 8px;
            text-align: center;
            margin-left: auto;
            margin-right: 5vw;
            display: flex;
            flex-direction: column;
            gap: 0.6rem;
        }

        .card-login h1 {
            font-size: 1.2rem;
            font-weight: bold;
        }

        .card-login label {
            font-size: 0.85rem;
            font-weight: bold;
        }

        .card-login input {
            padding: 0.3rem;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 0.9rem;
            min-width: 0;
            flex-grow: 1;
        }

        .card-login button {
            width: 100%;
            padding: 0.3rem;
            background-color: #299E12;
            color: white;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            font-size: 0.9rem;
        }

        .card-login button:hover {
            background-color: #217b10;
        }

        .btn-secondary {
            width: 100%;
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            padding: 0.3rem;
            border-radius: 5px;
            display: inline-block;
            text-align: center;
            font-size: 0.9rem;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }

        /* Ajustes de Responsividade */
        @media (max-width: 768px) {
            .box-green {
                width: 60vw;
                transform: skewX(-15deg);
            }

            .logo-container img {
                max-width: 40%;
                max-height: 20vh;
            }

            .card-login {
                width: 90vw;
                padding: 0.6rem 0.8rem;
                margin-right: 3vw;
            }

            .card-login h1 {
                font-size: 1rem;
            }

            .card-login button, .btn-secondary {
                font-size: 0.8rem;
                padding: 0.25rem;
            }
        }

        @media (max-width: 480px) {
            .box-green {
                width: 70vw;
                transform: skewX(-10deg);
            }

            .logo-container img {
                max-width: 30%;
                max-height: 15vh;
            }

            .card-login {
                width: 95vw;
                padding: 0.5rem;
                margin-right: 1vw;
            }

            .card-login h1 {
                font-size: 0.9rem;
            }

            .card-login input, .card-login button, .btn-secondary {
                font-size: 0.75rem;
                padding: 0.2rem;
            }
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
        <h1>Realize seu cadastro</h1>
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
            <div class="mb-3 d-flex flex-column align-items-center gap-2">
                <button type="submit">Cadastrar</button>
                <a href="../index.php" class="btn btn-secondary">Voltar</a>
            </div>
        </form>
    </div>
</body>

</html>
