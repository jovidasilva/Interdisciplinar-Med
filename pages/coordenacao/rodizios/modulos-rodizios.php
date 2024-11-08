<?php
include('../../../cfg/config.php');

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
        echo '<p>Erro ao preparar a consulta: ' . $conn->error . '</p>';
    }
}

$conn->close();
