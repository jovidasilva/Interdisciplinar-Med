<?php
require_once __DIR__ . '/cfg/config.php';
require_once __DIR__ . '/includes/csrf.php';
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css?v=<?php echo ASSET_VERSION; ?>">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="auth-page">
    <div class="card-login" style="width: 300px;">
        <h1>Acesso</h1>
        <form action="cadastro_e_login/login.php" method="POST">
            <?php echo csrf_field(); ?>
            <input type="text" name=login placeholder="login" required>
            <input type="password" name="senha" placeholder="senha" required>
            <button type="submit">Login</button>
        </form>
        <a href="cadastro_e_login/esqueci-senha.php" class="d-block mb-2">Esqueci minha senha</a>
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

if (isset($_GET['alert']) && $_GET['alert'] == '3') {
    $minutos = isset($_GET['min']) ? max(1, intval($_GET['min'])) : 15;
    $mensagem = 'Muitas tentativas de login. Tente novamente em ' . $minutos . ' minuto(s).';
    echo "<script>
        Swal.fire({
            position: 'top',
            text: " . json_encode($mensagem, JSON_HEX_TAG) . ",
            icon: 'warning',
            confirmButtonText: 'Ok'
        }).then(function() {
            window.location.href = window.location.pathname;
        });
    </script>";
}
?>