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