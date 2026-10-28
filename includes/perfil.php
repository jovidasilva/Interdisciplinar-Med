<?php
require_once __DIR__ . '/app.php';
exigir_acesso();

$user = db_um($conn, 'SELECT nome, email, telefone, registro, login, tipo, periodo FROM usuarios WHERE idusuario = ?', 'i', [usuario_id()]);
if (!$user) {
    redirecionar(url('cadastro_e_login/logout.php'));
}

// Mantém o que a pessoa digitou se a validação falhou.
$email = $_SESSION['form_data']['email'] ?? $user['email'];
$telefone = $_SESSION['form_data']['telefone'] ?? $user['telefone'];
unset($_SESSION['form_data']);

layout_inicio('Meu perfil');
page_header('Meu perfil', 'Seus dados de contato e de acesso ao sistema.');
?>
<div class="row g-4">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header"><h2>Dados pessoais</h2></div>
            <div class="card-body">
                <dl class="detail-list mb-4">
                    <div><dt>Nome</dt><dd><?= e($user['nome']) ?></dd></div>
                    <div><dt>Tipo de acesso</dt><dd><?= e(TIPOS_USUARIO[(int) $user['tipo']] ?? '—') ?></dd></div>
                    <div><dt><?= (int) $user['tipo'] === TIPO_ALUNO ? 'RA' : 'Registro' ?></dt><dd><?= e($user['registro'] ?: '—') ?></dd></div>
                    <?php if ((int) $user['tipo'] === TIPO_ALUNO): ?>
                        <div><dt>Período</dt><dd><?= $user['periodo'] ? (int) $user['periodo'] . 'º' : '—' ?></dd></div>
                    <?php endif; ?>
                </dl>
                <form action="alterar-dados.php" method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="alterar_dados_contato">
                    <div class="form-grid">
                        <div>
                            <label for="email" class="form-label">E-mail</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?= e($email) ?>" autocomplete="email" required>
                        </div>
                        <div>
                            <label for="telefone" class="form-label">Telefone</label>
                            <input type="tel" class="form-control" id="telefone" name="telefone" value="<?= e($telefone) ?>" autocomplete="tel" required>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check2"></i> Salvar contato</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card">
            <div class="card-header"><h2>Login</h2></div>
            <div class="card-body">
                <form method="POST" action="alterar-dados.php">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="alterar_login">
                    <div class="mb-3">
                        <label class="form-label" for="login_atual">Login atual</label>
                        <input type="text" class="form-control" id="login_atual" value="<?= e($user['login']) ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="login_novo">Novo login</label>
                        <input type="text" class="form-control" id="login_novo" name="login_novo" autocomplete="username" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label" for="senha_confirmacao">Senha atual (para confirmar)</label>
                        <input type="password" class="form-control" id="senha_confirmacao" name="senha_atual" autocomplete="current-password" required>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-outline-primary">Alterar login</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h2>Senha</h2></div>
            <div class="card-body">
                <form method="POST" action="alterar-dados.php">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="alterar_senha">
                    <div class="mb-3">
                        <label class="form-label" for="senha_antiga">Senha atual</label>
                        <input type="password" class="form-control" id="senha_antiga" name="senha_atual" autocomplete="current-password" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label" for="senha_nova">Nova senha</label>
                        <input type="password" class="form-control" id="senha_nova" name="senha_nova" minlength="<?= SENHA_MIN_LEN ?>" autocomplete="new-password" required>
                        <div class="form-text">Mínimo de <?= SENHA_MIN_LEN ?> caracteres.</div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-outline-primary">Alterar senha</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php layout_fim(); ?>
