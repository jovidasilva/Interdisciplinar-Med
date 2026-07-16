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
