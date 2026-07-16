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
if (isset($_GET['periodo'])) {
    $periodo = $_GET['periodo'];

    $query = "SELECT idmodulo, nome_modulo FROM modulos WHERE periodo = ?";
    $stmt = $conn->prepare($query);

    if ($stmt) {
        $stmt->bind_param("i", $periodo);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            echo '<ul>';
            while ($row = $result->fetch_assoc()) {
                echo '<li data-idmodulo="' . $row['idmodulo'] . '">' . $row['nome_modulo'] . '</li>';
            }
            echo '</ul>';
        } else {
            echo '<p>Nenhum módulo disponível para este período</p>';
        }

        $stmt->close();
    } else {
        error_log("Erro ao preparar a consulta (modulos-rodizios.php): " . $conn->error);
        echo '<p>Erro ao processar a consulta. Tente novamente mais tarde.</p>';
    }
}

$conn->close();