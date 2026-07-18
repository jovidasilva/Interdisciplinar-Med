<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['login']) || !in_array($_SESSION['tipo'] ?? null, [1], true)) {
    header('Location: ' . str_repeat('../', 3) . 'index.php');
    exit();
}
if (!isset($conn)) {
    require_once __DIR__ . '/' . str_repeat('../', 3) . 'cfg/config.php';
}

$id_usuario = isset($_GET['id']) ? intval($_GET['id']) : 0;
$result = null;

if ($id_usuario > 0) {
    $query = "SELECT a.nota, a.data_avaliacao, m.nome_modulo AS modulo_nome
              FROM avaliacoes a
              JOIN modulos m ON a.idmodulo = m.idmodulo
              WHERE a.idaluno = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
}

?>

<h3>Resultado da Avaliação</h3>
<?php if ($result && $result->num_rows > 0):
    $row = $result->fetch_assoc(); ?>
    <p><strong>Módulo:</strong> <?php echo htmlspecialchars($row['modulo_nome']); ?></p>
    <p><strong>Nota:</strong> <?php echo htmlspecialchars($row['nota']); ?></p>
    <p><strong>Data da Avaliação:</strong> <?php echo htmlspecialchars($row['data_avaliacao']); ?></p>
<?php else: ?>
    <p>Nenhuma avaliação encontrada.</p>
<?php endif; ?>
