<?php
require_once __DIR__ . '/../includes/app.php';

/**
 * Busca um token de reset válido (não usado e não expirado) pelo valor em
 * texto puro recebido na URL/formulário. Retorna o registro (com idusuario)
 * ou null se o token for inválido/expirado.
 */
function buscar_token_valido(mysqli $conn, string $tokenTextoPuro): ?object
{
    if ($tokenTextoPuro === '') {
        return null;
    }

    $tokenHash = hash('sha256', $tokenTextoPuro);

    $stmt = $conn->prepare(
        'SELECT idtoken, idusuario, expira_em FROM password_reset_tokens
         WHERE token_hash = ? AND usado = 0 AND expira_em >= NOW()
         LIMIT 1'
    );
    $stmt->bind_param('s', $tokenHash);
    $stmt->execute();
    $res = $stmt->get_result();
    $registro = $res->fetch_object();
    $stmt->close();

    return $registro ?: null;
}

$token = trim($_GET['token'] ?? $_POST['token'] ?? '');
$tokenValido = buscar_token_valido($conn, $token);
$erros = [];
$sucesso = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify_or_die();

    if (!$tokenValido) {
        $erros[] = 'Este link de redefinição de senha é inválido ou já expirou.';
    } else {
        $senha = (string) ($_POST['senha'] ?? '');
        $confirmarSenha = (string) ($_POST['confirmar_senha'] ?? '');

        if (mb_strlen($senha) < SENHA_MIN_LEN) {
            $erros[] = 'A nova senha deve ter pelo menos ' . SENHA_MIN_LEN . ' caracteres.';
        }

        if ($senha !== $confirmarSenha) {
            $erros[] = 'A confirmação de senha não coincide com a nova senha.';
        }

        if (empty($erros)) {
            $novaSenhaHash = password_hash($senha, PASSWORD_DEFAULT);

            $stmtUpdate = $conn->prepare('UPDATE usuarios SET senha = ? WHERE idusuario = ?');
            $stmtUpdate->bind_param('si', $novaSenhaHash, $tokenValido->idusuario);
            $stmtUpdate->execute();
            $stmtUpdate->close();

            // Marca este token (e qualquer outro token pendente do mesmo
            // usuário) como usado, para que links antigos deixem de funcionar.
            $stmtUsado = $conn->prepare(
                'UPDATE password_reset_tokens SET usado = 1 WHERE idusuario = ? AND usado = 0'
            );
            $stmtUsado->bind_param('i', $tokenValido->idusuario);
            $stmtUsado->execute();
            $stmtUsado->close();

            $sucesso = true;
        }
    }
}

layout_auth_inicio('Redefinir senha');
?>
<?php if ($erros): ?>
    <div class="alert alert-danger"><?php foreach ($erros as $erro): ?><div><?= e($erro) ?></div><?php endforeach; ?></div>
<?php endif; ?>

<?php if ($sucesso): ?>
    <div class="alert alert-success">Senha redefinida com sucesso! Você já pode entrar com a nova senha.</div>
    <a href="../index.php" class="btn btn-primary btn-lg w-100">Ir para o login</a>
<?php elseif (!$tokenValido): ?>
    <div class="alert alert-warning">Este link de redefinição de senha é inválido ou já expirou.</div>
    <a href="esqueci-senha.php" class="btn btn-primary btn-lg w-100">Solicitar novo link</a>
<?php else: ?>
    <form action="redefinir-senha.php" method="POST">
        <?= csrf_field() ?>
        <input type="hidden" name="token" value="<?= e($token) ?>">
        <div class="mb-3">
            <label for="senha" class="form-label">Nova senha</label>
            <input type="password" name="senha" id="senha" class="form-control form-control-lg" minlength="<?= SENHA_MIN_LEN ?>" autocomplete="new-password" required>
            <div class="form-text">Mínimo de <?= SENHA_MIN_LEN ?> caracteres.</div>
        </div>
        <div class="mb-3">
            <label for="confirmar_senha" class="form-label">Confirmar nova senha</label>
            <input type="password" name="confirmar_senha" id="confirmar_senha" class="form-control form-control-lg" minlength="<?= SENHA_MIN_LEN ?>" autocomplete="new-password" required>
        </div>
        <button type="submit" class="btn btn-primary btn-lg w-100">Redefinir senha</button>
    </form>
    <p class="auth-footer"><a href="../index.php">Cancelar</a></p>
<?php endif; ?>
<?php layout_auth_fim(); ?>
