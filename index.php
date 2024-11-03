<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
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
            overflow: hidden;
            background-color: green;
            background-image: url('img/bg.jpg');
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
            background-position: center;
        }

        .card-login {
            position: relative;
            z-index: 2;
            padding: 2rem;
            width: 300px;
            color: #333;
            background-color: white;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
            border-radius: 8px;
            text-align: center;
            background: rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(10px);
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

        button {
            width: 100%;
            padding: 0.5rem;
            background-color: rgb(0, 94, 0);
            color: white;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            margin-bottom: 3px;
        }

        .card-login button:hover {
            background-color: rgb(0, 80, 0);
        }
    </style>
</head>

<body>
    <div class="card-login">
        <h1>Acesso</h1>
        <form action="cadastro_e_login/login.php" method="POST">
            <input type="text" name=login placeholder="login" required>
            <input type="password" name="senha" placeholder="senha" required>
            <button type="submit">Login</button>
        </form>
        <button onclick="location.href='cadastro_e_login/cadastro.php'">Cadastro</button>
    </div>
</body>

</html>

<?php
if (isset($_GET['alert']) && $_GET['alert'] == '1') {
    echo "<script>
        Swal.fire({
            position: 'top',
            title: 'Cadastrado!',
            text: 'Usuário cadastrado.',
            icon: 'info',
            confirmButtonText: 'Ok'
        }).then(function() {
            window.location.href = window.location.pathname;
        });
    </script>";
}

if (isset($_GET['alert']) && $_GET['alert'] == '2') {
    echo "<script>
        Swal.fire({
            position: 'top',
            text: 'Login ou Senha incorreto(s).',
            icon: 'error',
            confirmButtonText: 'Ok'
        }).then(function() {
            window.location.href = window.location.pathname;
        });
    </script>";
}
?>