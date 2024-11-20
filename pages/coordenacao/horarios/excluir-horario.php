<?php
session_start();
include('../../../cfg/config.php');

if (empty($_SESSION["login"])) {
    echo "<script>location.href='../../index.php';</script>";
    exit();
}

if (isset($_GET['id'])) {
    $idHorario = $_GET['id'];

    $query = "DELETE FROM horarios WHERE idhorario = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $idHorario);

    if ($stmt->execute()) {
        echo "<script>alert('Horário excluído com sucesso!'); location.href='horarios.php';</script>";
    } else {
        echo "<script>alert('Erro ao excluir o horário: " . $stmt->error . "'); location.href='horarios.php';</script>";
    }

    $stmt->close();
}
?>