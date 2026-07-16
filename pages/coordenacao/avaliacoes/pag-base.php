<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['login']) || !in_array($_SESSION['tipo'] ?? null, [2, 3], true)) {
    header('Location: ' . str_repeat('../', 3) . 'index.php');
    exit();
}
if (!isset($conn)) {
    require_once __DIR__ . '/' . str_repeat('../', 3) . 'cfg/config.php';
}
?>
<div>
    <button onclick="location.href='?page=listar-perguntas'" class="btn btn-primary">Ver perguntas das avaliações</button> 
    <button onclick="location.href='?page=listar-avaliacoes'" class="btn btn-danger">Ver avaliações realizadas</button> 
    <button onclick="location.href='?page=notas'" class="btn btn-success">Ver notas</button>
</div>