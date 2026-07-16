<?php
// Carrega variáveis de um arquivo .env (se existir) para não depender só
// de variáveis de ambiente já exportadas pelo sistema/container.
$envPath = __DIR__ . '/../.env';
if (file_exists($envPath)) {
    foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if ($line[0] === '#' || strpos($line, '=') === false) {
            continue;
        }
        [$key, $value] = array_map('trim', explode('=', $line, 2));
        if (!getenv($key)) {
            putenv("$key=$value");
        }
    }
}

function env(string $key, string $default): string
{
    $value = getenv($key);
    return $value !== false ? $value : $default;
}

if (!defined('HOST')) {
    define('HOST', env('DB_HOST', 'localhost'));
}

if (!defined('USER')) {
    define('USER', env('DB_USER', 'root'));
}

if (!defined('PASS')) {
    define('PASS', env('DB_PASS', ''));
}

if (!defined('BASE')) {
    define('BASE', env('DB_NAME', 'proj_internato'));
}

if (!defined('BASE_URL')) {
    define('BASE_URL', env('BASE_URL', '/Interdisciplinar-Med/'));
}

$conn = new mysqli(HOST, USER, PASS, BASE, (int) env('DB_PORT', '3306'));
if ($conn->connect_error) {
    error_log("Erro de conexão com o banco de dados: " . $conn->connect_error);
    die("Erro ao conectar ao banco de dados. Tente novamente mais tarde.");
}
