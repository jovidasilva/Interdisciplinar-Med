<?php
include('../cfg/config.php');
session_start();

if (isset($_SESSION["tipo"])) {
    $tipo = $_SESSION["tipo"];

    if ($tipo == 0) {
        echo "<script>location.href='" . BASE_URL . "/pages/aluno/home.php';</script>";
    } elseif ($tipo == 1) {
        echo "<script>location.href='" . BASE_URL . "/pages/preceptor/home.php';</script>";
    } elseif ($tipo == 2 || $tipo == 3) {
        echo "<script>location.href='" . BASE_URL . "/pages/coordenacao/home.php';</script>";
    } else {
        echo "<script>location.href='" . BASE_URL . "/cadastro_e_login/novocadastro.php';</script>";
    }
} else {
    echo "<script>alert('Login expirado!');location.href='" . BASE_URL . "/pages/coordenacao/home.php';</script>";
    exit();
}
var_dump($tipo);
