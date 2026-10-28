<?php
require_once __DIR__ . '/includes/app.php';

if (!empty($_SESSION['idusuario'])) {
    redirecionar(home_do_usuario());
}

layout_auth_inicio('Entrar', 'Acesse com seu login institucional.');
?>
<form action="cadastro_e_login/login.php" method="POST" novalidate>
    <?= csrf_field() ?>
    <div class="mb-3">
        <label for="login" class="form-label">Login</label>
        <input type="text" name="login" id="login" class="form-control form-control-lg" autocomplete="username" required autofocus>
    </div>
    <div class="mb-2">
        <div class="d-flex justify-content-between align-items-baseline">
            <label for="senha" class="form-label">Senha</label>
            <a href="cadastro_e_login/esqueci-senha.php" class="small">Esqueci minha senha</a>
        </div>
        <input type="password" name="senha" id="senha" class="form-control form-control-lg" autocomplete="current-password" required>
    </div>
    <button type="submit" class="btn btn-primary btn-lg w-100 mt-3">Entrar</button>
</form>
<p class="auth-footer">Ainda não tem acesso? <a href="cadastro_e_login/cadastro.php">Solicite seu cadastro</a></p>
<?php layout_auth_fim(); ?>
