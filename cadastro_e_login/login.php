<?php
require_once __DIR__ . '/../includes/app.php';
require_once __DIR__ . '/../includes/rate-limit.php';

exigir_post();

$login = trim((string) ($_POST['login'] ?? ''));
$senha = (string) ($_POST['senha'] ?? '');

if ($login === '' || $senha === '') {
    redirecionar('../index.php', 'Informe login e senha.', 'error');
}

$bloqueadoAte = login_esta_bloqueado($conn, $login);
if ($bloqueadoAte !== null) {
    $minutos = login_minutos_restantes($bloqueadoAte);
    redirecionar('../index.php', "Muitas tentativas de login. Tente novamente em {$minutos} minuto(s).", 'warning');
}

$usuario = db_um($conn, 'SELECT idusuario, nome, senha, tipo, ativo FROM usuarios WHERE login = ?', 's', [$login]);

if (!$usuario || !password_verify($senha, (string) $usuario['senha'])) {
    login_registrar_falha($conn, $login);
    redirecionar('../index.php', 'Login ou senha incorretos.', 'error');
}

if ((int) $usuario['ativo'] !== 1) {
    redirecionar('../index.php', 'Sua conta está desativada. Procure a coordenação.', 'warning');
}

login_registrar_sucesso($conn, $login);

// Novo ID de sessão a cada login (evita fixação de sessão).
session_regenerate_id(true);
$_SESSION['login'] = $login;
$_SESSION['nome'] = $usuario['nome'];
$_SESSION['tipo'] = (int) $usuario['tipo'];
$_SESSION['idusuario'] = (int) $usuario['idusuario'];

redirecionar(home_do_usuario());
