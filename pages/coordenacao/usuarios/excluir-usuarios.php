<?php
include('../../../cfg/config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['usuarios'])) {
    $usuarios = $_POST['usuarios'];

    $placeholders = implode(',', array_fill(0, count($usuarios), '?'));
    $sql = "DELETE FROM usuarios WHERE idusuario IN ($placeholders)";
    $stmt = $conn->prepare($sql);

    $stmt->bind_param(str_repeat('i', count($usuarios)), ...$usuarios);

    if ($stmt->execute()) {
        echo "<script>location.href='usuarios.php?page=listar-usuarios&alert=3';</script>";
    } else {
        echo "<script>location.href='usuarios.php?page=listar-usuarios&alert=4';</script>";
    }
    $stmt->close();
    exit();
}
