<?php
session_start();

if (empty($_POST["login"]) || empty($_POST["senha"])) {
    echo "<script>alert('Preencha todos os campos'); location.href='../index.php';</script>";
    exit();
}

include('../cfg/config.php');

$login = $_POST['login'];
$senha = $_POST['senha'];

$stmt = $conn->prepare("SELECT * FROM usuarios WHERE login = ?");
$stmt->bind_param("s", $login);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_object();

if ($row && password_verify($senha, $row->senha)) {
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
    echo "<script>alert('Login ou senha incorretos'); location.href='../index.php';</script>";
}

$stmt->close();
$conn->close();
