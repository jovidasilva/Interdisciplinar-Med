<?php
require_once __DIR__ . '/app.php';
require_once __DIR__ . '/validacao.php';
exigir_acesso();
exigir_post();

// Sempre altera o próprio usuário (o id vem da sessão, nunca do formulário).
$idusuario = usuario_id();
$action = $_POST['action'] ?? '';

/** Confere a senha atual do usuário logado. */
function senha_confere(mysqli $conn, int $idusuario, string $senha): bool
{
    $row = db_um($conn, 'SELECT senha FROM usuarios WHERE idusuario = ?', 'i', [$idusuario]);
    return $row && password_verify($senha, (string) $row['senha']);
}

if ($action === 'alterar_dados_contato') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $telefone = trim((string) ($_POST['telefone'] ?? ''));
    if (!validar_email($email) || $telefone === '') {
        $_SESSION['form_data'] = ['email' => $email, 'telefone' => $telefone];
        redirecionar('perfil.php', 'Informe um e-mail válido e um telefone.', 'error');
    }
    db_exec($conn, 'UPDATE usuarios SET email = ?, telefone = ? WHERE idusuario = ?', 'ssi', [$email, $telefone, $idusuario]);
    redirecionar('perfil.php', 'Dados de contato atualizados.');
}

if ($action === 'alterar_login') {
    $loginNovo = trim((string) ($_POST['login_novo'] ?? ''));
    if (!senha_confere($conn, $idusuario, (string) ($_POST['senha_atual'] ?? ''))) {
        redirecionar('perfil.php', 'Senha atual incorreta.', 'error');
    }
    if ($loginNovo === '') {
        redirecionar('perfil.php', 'Informe o novo login.', 'error');
    }
    if (db_um($conn, 'SELECT 1 FROM usuarios WHERE login = ? AND idusuario <> ?', 'si', [$loginNovo, $idusuario])) {
        redirecionar('perfil.php', 'Este login já está em uso.', 'error');
    }
    db_exec($conn, 'UPDATE usuarios SET login = ? WHERE idusuario = ?', 'si', [$loginNovo, $idusuario]);
    $_SESSION['login'] = $loginNovo;
    redirecionar('perfil.php', 'Login alterado.');
}

if ($action === 'alterar_senha') {
    $senhaNova = (string) ($_POST['senha_nova'] ?? '');
    if (!senha_confere($conn, $idusuario, (string) ($_POST['senha_atual'] ?? ''))) {
        redirecionar('perfil.php', 'Senha atual incorreta.', 'error');
    }
    if (mb_strlen($senhaNova) < SENHA_MIN_LEN) {
        redirecionar('perfil.php', 'A nova senha deve ter pelo menos ' . SENHA_MIN_LEN . ' caracteres.', 'error');
    }
    db_exec($conn, 'UPDATE usuarios SET senha = ? WHERE idusuario = ?', 'si', [password_hash($senhaNova, PASSWORD_DEFAULT), $idusuario]);
    redirecionar('perfil.php', 'Senha alterada.');
}

redirecionar('perfil.php', 'Ação inválida.', 'error');
