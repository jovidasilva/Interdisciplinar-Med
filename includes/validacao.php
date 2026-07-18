<?php
// Funções de validação puras (sem efeitos colaterais, sem dependência de
// sessão, banco de dados ou superglobais de requisição). Extraídas de
// alterar-dados.php para permitir testes unitários isolados com PHPUnit,
// sem precisar inicializar sessão real ou conexão com MySQL.

if (!function_exists('validar_email')) {
    function validar_email($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) && preg_match('/@.+\./', $email);
    }
}
