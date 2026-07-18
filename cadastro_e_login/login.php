<?php
session_start();

include('../cfg/config.php');
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/rate-limit.php';

csrf_verify_or_die();

$login = $_POST['login'];
$senha = $_POST['senha'];

$bloqueadoAte = login_esta_bloqueado($conn, $login);
if ($bloqueadoAte !== null) {
    $minutos = login_minutos_restantes($bloqueadoAte);
    echo "<script>location.href='../index.php?alert=3&min=" . intval($minutos) . "';</script>";
    exit();
}

$stmt = $conn->prepare("SELECT * FROM usuarios WHERE login = ?");
$stmt->bind_param("s", $login);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_object();

if ($row && password_verify($senha, $row->senha)) {
    login_registrar_sucesso($conn, $login);

    $_SESSION["login"] = $login;
    $_SESSION["nome"] = $row->nome;
    $_SESSION["tipo"] = $row->tipo;
    $_SESSION["idusuario"] = $row->idusuario;

    if ($row->tipo == 0) {
        echo "<script>location.href='../pages/aluno/home.php';</script>";
    } elseif ($row->tipo == 1) {
        echo "<script>location.href='../pages/preceptor/home.php';</script>";
    } elseif ($row->tipo == 2 || $row->tipo == 3) {
        echo "<script>location.href='../pages/coordenacao/home.php';</script>";
    } else {
        echo "<script>location.href='novocadastro.php';</script>";
    }
} else {
    login_registrar_falha($conn, $login);
    echo "<script>location.href='../index.php?alert=2';</script>";
}

$stmt->close();
$conn->close();
