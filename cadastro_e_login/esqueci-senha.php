<?php
session_start();
require_once __DIR__ . '/../cfg/config.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/mailer.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify_or_die();

    $login = trim($_POST['login'] ?? '');

    if ($login !== '') {
        $stmt = $conn->prepare('SELECT idusuario, nome, email FROM usuarios WHERE login = ? AND ativo = 1');
        $stmt->bind_param('s', $login);
        $stmt->execute();
        $res = $stmt->get_result();
        $usuario = $res->fetch_object();
        $stmt->close();

        // Só tenta gerar/enviar o token se o login existir e tiver um e-mail
        // cadastrado. A mensagem final ao usuário é sempre a mesma,
        // independente do resultado, para não permitir enumeração de logins.
        if ($usuario && !empty($usuario->email)) {
            $token = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $token);
            $expiraEm = date('Y-m-d H:i:s', time() + 3600); // 1 hora

            $stmtInsert = $conn->prepare(
                'INSERT INTO password_reset_tokens (idusuario, token_hash, expira_em) VALUES (?, ?, ?)'
            );
            $stmtInsert->bind_param('iss', $usuario->idusuario, $tokenHash, $expiraEm);
            $stmtInsert->execute();
            $stmtInsert->close();

            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $link = $scheme . '://' . $host . BASE_URL . '/cadastro_e_login/redefinir-senha.php?token=' . $token;

            $nomeSeguro = htmlspecialchars($usuario->nome ?? '', ENT_QUOTES);
            $linkSeguro = htmlspecialchars($link, ENT_QUOTES);

            $corpo = "
                <p>Olá, {$nomeSeguro}.</p>
                <p>Recebemos uma solicitação para redefinir a senha da sua conta no sistema de Internato Médico.</p>
                <p>Clique no link abaixo para escolher uma nova senha. Este link é válido por 1 hora:</p>
                <p><a href=\"{$linkSeguro}\">{$linkSeguro}</a></p>
                <p>Se você não solicitou essa alteração, apenas ignore este e-mail.</p>
            ";

            enviar_email($usuario->email, 'Redefinição de senha - Internato Médico', $corpo);
        }
    }

    // Mensagem sempre idêntica, exista ou não o login informado.
    header('Location: esqueci-senha.php?enviado=1');
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Esqueci minha senha</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css?v=<?php echo ASSET_VERSION; ?>">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="auth-page">
    <div class="card-login">
        <h3>Esqueci minha senha</h3>
        <p class="info">Informe o login usado para acessar o sistema. Se ele existir, você receberá um e-mail com
            instruções para redefinir sua senha.</p>
        <form action="esqueci-senha.php" method="POST">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label for="login">Login</label>
                <input type="text" name="login" id="login" class="form-control" required>
            </div>
            <button type="submit">Enviar instruções</button>
        </form>
        <a href="../index.php" class="btn btn-secondary w-100">Voltar para o login</a>
    </div>
</body>

</html>

<?php if (isset($_GET['enviado']) && $_GET['enviado'] == '1'): ?>
    <script>
        Swal.fire({
            position: 'top',
            title: 'Verifique seu e-mail',
            text: 'Se o login existir, você receberá um e-mail com instruções para redefinir sua senha.',
            icon: 'info',
            confirmButtonText: 'Ok'
        }).then(function () {
            window.location.href = window.location.pathname;
        });
    </script>
<?php endif; ?>
