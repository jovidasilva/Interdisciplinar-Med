<?php
// Helper de proteção CSRF. Inclua este arquivo (require_once) em qualquer
// página que renderize um <form method="POST"> ou que processe um POST,
// depois que a sessão já estiver iniciada (session_start()).

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!function_exists('csrf_token')) {
    /**
     * Retorna o token CSRF da sessão atual, gerando um novo se ainda não existir.
     */
    function csrf_token(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Retorna o HTML de um <input type="hidden"> pronto para colocar dentro de
     * qualquer <form method="POST">.
     */
    function csrf_field(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES) . '">';
    }
}

if (!function_exists('csrf_verify')) {
    /**
     * Verifica se o token enviado no POST bate com o da sessão.
     */
    function csrf_verify(): bool
    {
        return isset($_POST['csrf_token'])
            && isset($_SESSION['csrf_token'])
            && is_string($_POST['csrf_token'])
            && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
    }
}

if (!function_exists('csrf_verify_or_die')) {
    /**
     * Verifica o token e interrompe a requisição com 403 se for inválido.
     * Chame isso logo no início de qualquer handler que processe um POST.
     */
    function csrf_verify_or_die(): void
    {
        if (!csrf_verify()) {
            http_response_code(403);
            die('Requisição inválida ou expirada (token CSRF ausente/incorreto). Volte e tente novamente.');
        }
    }
}
