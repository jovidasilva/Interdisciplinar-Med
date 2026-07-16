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
if (isset($_POST['alunosDesassociar']) && isset($_GET['idmodulo'])) {
    $idmodulo = $_GET['idmodulo'];
    $alunos = $_POST['alunosDesassociar'];

    $stmt = $conn->prepare("DELETE FROM modulos_alunos WHERE idmodulo = ? AND idusuario = ?");
    foreach ($alunos as $idusuario) {
        $stmt->bind_param("ii", $idmodulo, $idusuario);
        $stmt->execute();
    }
    $stmt->close();

    header("Location: " . $_SERVER['HTTP_REFERER'] . "?idmodulo=" . $idmodulo);
    exit();
} else {
    echo "Nenhum aluno selecionado.";
}
$conn->close();
