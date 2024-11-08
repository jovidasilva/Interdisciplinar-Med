<?php
include('../../../cfg/config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['usuarios']) && isset($_POST['novo_tipo'])) {
    $novo_tipo = $_POST['novo_tipo'];
    $usuarios = $_POST['usuarios'];

    $sql = "UPDATE usuarios SET tipo = ? WHERE idusuario IN (" . implode(',', array_fill(0, count($usuarios), '?')) . ")";
    $stmt = $conn->prepare($sql);
    $params = array_merge([$novo_tipo], $usuarios);
    $types = str_repeat('i', count($params));
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        echo "<script>location.href='usuarios.php?page=listar-usuarios&alert=1';</script>";
    } else {
        echo "<script>location.href='usuarios.php?page=listar-usuarios&alert=2';</script>";
    }
    $stmt->close();
    exit();
}
