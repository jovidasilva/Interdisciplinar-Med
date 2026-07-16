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
require_once('../../../cfg/config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['usuarios'])) {
    $usuarios = $_POST['usuarios'];

    foreach ($usuarios as $usuario) {
        $sql = "SELECT ativo FROM usuarios WHERE idusuario = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $usuario);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $novo_status = ($row['ativo'] == '1') ? '0' : '1'; // Alterna entre 0 e 1

            // Atualiza o status
            $update_sql = "UPDATE usuarios SET ativo = ? WHERE idusuario = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ii", $novo_status, $usuario);
            $update_stmt->execute();
        }
    }

    
    echo "<script>location.href='?page=listar-usuarios'</script>";
    exit();
}
?>

