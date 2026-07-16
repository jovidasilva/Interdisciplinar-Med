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
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $acao = $_POST['acao'];
    $idmodulo = $_POST['idmodulo'];

    if ($acao == 'associar') {
        if (!empty($_POST['preceptores'])) {
            $preceptores = $_POST['preceptores'];
            foreach ($preceptores as $preceptor) {
                $sql = "INSERT INTO preceptores_modulos (idusuario, idmodulo) VALUES ('$preceptor', '$idmodulo')";
                $conn->query($sql);
            }
        }
        echo "<script>location.href='?page=visualizar-preceptor&idmodulo=" . $idmodulo . "';</script>";
        exit;
    } elseif ($acao == 'desassociar') {
        if (!empty($_POST['preceptores'])) {
            $preceptores = $_POST['preceptores'];
            foreach ($preceptores as $preceptor) {
                $sql = "DELETE FROM preceptores_modulos WHERE idusuario = '$preceptor' AND idmodulo = '$idmodulo'";
                $conn->query($sql);
            }
        }
        echo "<script>location.href='?page=visualizar-preceptor&idmodulo=" . $idmodulo . "';</script>";
        exit;
    }
}
