<?php
require_once __DIR__ . '/../includes/app.php';
require_once __DIR__ . '/../includes/validacao.php';

$dados = ['nome' => '', 'email' => '', 'telefone' => '', 'registro' => '', 'login' => ''];
$erros = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify_or_die();

    foreach ($dados as $campo => $_) {
        $dados[$campo] = trim((string) ($_POST[$campo] ?? ''));
    }
    $senha = (string) ($_POST['senha'] ?? '');

    if (in_array('', $dados, true)) {
        $erros[] = 'Preencha todos os campos.';
    }
    if ($dados['email'] !== '' && !validar_email($dados['email'])) {
        $erros[] = 'Informe um e-mail válido.';
    }
    if (mb_strlen($senha) < SENHA_MIN_LEN) {
        $erros[] = 'A senha deve ter pelo menos ' . SENHA_MIN_LEN . ' caracteres.';
    }
    if ($dados['login'] !== '' && db_um($conn, 'SELECT 1 FROM usuarios WHERE login = ?', 's', [$dados['login']])) {
        $erros[] = 'Este login já está em uso. Escolha outro.';
    }

    if (!$erros) {
        db_exec(
            $conn,
            'INSERT INTO usuarios (nome, email, telefone, registro, login, senha, tipo) VALUES (?, ?, ?, ?, ?, ?, ?)',
            'ssssssi',
            [$dados['nome'], $dados['email'], $dados['telefone'], $dados['registro'], $dados['login'], password_hash($senha, PASSWORD_DEFAULT), TIPO_PENDENTE]
        );
        redirecionar('../index.php', 'Cadastro enviado! A coordenação precisa liberar seu acesso.');
    }
}

layout_auth_inicio('Solicitar cadastro', 'Depois de enviar, a coordenação libera seu acesso.', true);
?>
<?php if ($erros): ?>
    <div class="alert alert-danger"><?php foreach ($erros as $erro): ?><div><?= e($erro) ?></div><?php endforeach; ?></div>
<?php endif; ?>
<form action="" method="POST">
    <?= csrf_field() ?>
    <div class="mb-3">
        <label for="nome" class="form-label">Nome completo</label>
        <input type="text" name="nome" id="nome" class="form-control" value="<?= e($dados['nome']) ?>" autocomplete="name" required>
    </div>
    <div class="form-grid mb-3">
        <div>
            <label for="email" class="form-label">E-mail</label>
            <input type="email" name="email" id="email" class="form-control" value="<?= e($dados['email']) ?>" autocomplete="email" required>
        </div>
        <div>
            <label for="telefone" class="form-label">Telefone</label>
            <input type="tel" name="telefone" id="telefone" class="form-control" value="<?= e($dados['telefone']) ?>" autocomplete="tel" placeholder="(99) 99999-9999" required>
        </div>
    </div>
    <div class="mb-3">
        <label for="registro" class="form-label">RA ou CRM</label>
        <input type="text" name="registro" id="registro" class="form-control" value="<?= e($dados['registro']) ?>" required>
    </div>
    <div class="form-grid mb-3">
        <div>
            <label for="login" class="form-label">Login</label>
            <input type="text" name="login" id="login" class="form-control" value="<?= e($dados['login']) ?>" autocomplete="username" required>
        </div>
        <div>
            <label for="senha" class="form-label">Senha</label>
            <input type="password" name="senha" id="senha" class="form-control" minlength="<?= SENHA_MIN_LEN ?>" autocomplete="new-password" required>
            <div class="form-text">Mínimo de <?= SENHA_MIN_LEN ?> caracteres.</div>
        </div>
    </div>
    <button type="submit" class="btn btn-primary btn-lg w-100 mt-2">Enviar cadastro</button>
</form>
<p class="auth-footer">Já tem acesso? <a href="../index.php">Entrar</a></p>
<?php layout_auth_fim(); ?>
