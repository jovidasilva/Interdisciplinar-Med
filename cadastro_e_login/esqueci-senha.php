<?php
require_once __DIR__ . '/../includes/app.php';
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
    redirecionar('esqueci-senha.php', 'Se o login existir, você receberá um e-mail com instruções para redefinir sua senha.', 'info');
}

layout_auth_inicio('Esqueci minha senha', 'Informe seu login. Se ele existir, enviaremos um link de redefinição para o e-mail cadastrado.');
?>
<form action="esqueci-senha.php" method="POST">
    <?= csrf_field() ?>
    <div class="mb-3">
        <label for="login" class="form-label">Login</label>
        <input type="text" name="login" id="login" class="form-control form-control-lg" autocomplete="username" required autofocus>
    </div>
    <button type="submit" class="btn btn-primary btn-lg w-100">Enviar instruções</button>
</form>
<p class="auth-footer"><a href="../index.php"><i class="bi bi-arrow-left"></i> Voltar para o login</a></p>
<?php layout_auth_fim(); ?>
