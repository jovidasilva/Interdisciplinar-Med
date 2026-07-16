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
<?php
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
