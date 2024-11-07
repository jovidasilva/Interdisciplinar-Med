<?php
include('../../../cfg/config.php');

if (isset($_POST['unidades']) && isset($_GET['idmodulo'])) {
    $idmodulo = $_GET['idmodulo'];
    $unidades = $_POST['unidades'];

    $stmt = $conn->prepare("INSERT INTO unidades_modulos (idmodulo, idunidade) VALUES (?, ?)");
    $stmt->bind_param("ii", $idmodulo, $idunidade);

    try {
        foreach ($unidades as $idunidade) {
            $stmt->execute();
        }
        $stmt->close();
        echo "<script>alert('Módulos associados com sucesso!');</script>";
        echo "<script>location.href='?page=visualizar-unidades&idunidade=" . end($unidades) . "';</script>";
    } catch (Exception $e) {
        echo "<script>alert('Erro ao associar módulos: " . $e->getMessage() . "');</script>";
        echo "<script>location.href='?page=visualizar-unidade&idunidade=" . end($unidades) . "';</script>";
    }
} else {
    echo "<script>alert('Erro ao associar módulos.');</script>";
    echo "<script>location.href='?page=visualizar-unidade&idunidade=';</script>";
}
