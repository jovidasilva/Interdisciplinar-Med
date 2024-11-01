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
            width: 60vw;
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
            max-width: 70%;
            width: auto;
            height: auto;
            max-height: 40vh;
        }

        .card-login {
            position: relative;
            z-index: 2;
            padding: 2rem;
            width: 90vw;
            max-width: 300px;
            background-color: white;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
            border-radius: 8px;
            text-align: center;
            margin-left: auto;
            margin-right: 5vw;
        }

        .card-login input {
            margin-bottom: 1rem;
            width: 100%;
            padding: 0.5rem;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .card-login button {
            width: 100%;
            padding: 0.5rem;
            background-color: #299E12;
            color: white;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            margin-bottom: 1rem;
        }

        .card-login button:hover {
            background-color: #217b10;
        }

        .card-login .register-link {
            color: #299E12;
            font-size: 0.9rem;
            cursor: pointer;
            text-decoration: underline;
        }

        .card-login .register-link:hover {
            color: #217b10;
        }

        /* Responsividade */
        @media (max-width: 768px) {
            .box-green {
                width: 70vw;
                transform: skewX(-15deg);
            }

            .logo-container img {
                max-width: 60%;
                max-height: 30vh;
            }
        }

        @media (max-width: 480px) {
            .box-green {
                width: 80vw;
                transform: skewX(-10deg);
            }

            .logo-container {
                padding-top: 2vh;
                padding-left: 1vw;
            }

            .logo-container img {
                max-width: 50%;
                max-height: 20vh;
            }

            .card-login {
                width: 85vw;
                margin-right: 3vw;
            }
        }
    </style>
</head>

<body>
    <div class="box-green">
        <div class="logo-container">
            <img src="img/LogoCeuma.png" alt="Logo CEUMA">
        </div>
    </div>
    <div class="card-login">
        <h1>Acesso</h1>
        <form action="cadastro_e_login/login.php" method="POST">
            <input type="text" name="login" placeholder="Login" required>
            <input type="password" name="senha" placeholder="Senha" required>
            <button type="submit">Login</button>
        </form>
        <span class="register-link" onclick="location.href='cadastro_e_login/cadastro.php'">Ainda não possui um cadastro?</span>
    </div>
</body>

</html>
