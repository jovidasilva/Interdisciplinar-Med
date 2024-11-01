<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
            background-color: green;
            transform: skewX(-30deg);
            transform-origin: top left;
            z-index: 1;
        }

        .card-login {
            position: relative;
            z-index: 2;
            padding: 2rem;
            width: 300px;
            background-color: white;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
            border-radius: 8px;
            text-align: center;
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
            background-color: green;
            color: white;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            margin-bottom: 3px;
        }

        .card-login button:hover {
            background-color: darkgreen;
        }
    </style>
</head>

<body>
    <div class="box-green"></div>
    <div class="card-login">
        <h1>Acesso</h1>
        <form action="cadastro_e_login/login.php" method="POST">
            <input type="text" name=login placeholder="login">
            <input type="password" name="senha" placeholder="senha">
            <button type="submit">Login</button>
        </form>
        <button onclick="location.href='cadastro_e_login/cadastro.php'">Cadastro</button>
    </div>
</body>

</html>