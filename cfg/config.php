<?php
if (!defined('HOST')) {
    define('HOST', 'localhost');
}

if (!defined('USER')) {
    define('USER', 'root');
}

if (!defined('PASS')) {
    define('PASS', 'root');
}

if (!defined('BASE')) {
    define('BASE', 'proj_internato');
}

if (!defined('BASE_URL')) {
    define('BASE_URL', '/Interdisciplinar-Med/');
}

$conn = new mysqli(HOST, USER, PASS, BASE);
if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}
