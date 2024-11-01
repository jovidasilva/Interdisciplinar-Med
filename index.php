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
            width: 60%;
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
    align-items: flex-start; /* Alinha a logo ao topo */
    justify-content: flex-start; /* Alinha a logo à esquerda */
    height: 100%; /* Ocupa toda a altura para permitir alinhamento ao topo */
    padding-top: 100px; /* Ajuste o valor para definir a distância do topo */
    padding-left: 20px; /* Ajuste o valor para definir a distância da esquerda */
}


        .logo-container img {
            width: 550px;
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
            margin-left: 100px;
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
    margin-left: 1500px; /* Aumente este valor para mover mais para a direita */
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