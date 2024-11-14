<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_GET['idunidade']) && is_numeric($_GET['idunidade'])) {
    $idunidade = intval($_GET['idunidade']);

    // Verifica se foram selecionados módulos para desassociação
    if (!empty($_POST['modulos']) && is_array($_POST['modulos'])) {
        // Prepara a exclusão dos módulos selecionados na tabela de associação
        $stmt = $conn->prepare("DELETE FROM unidades_modulos WHERE idunidade = ? AND idmodulo = ?");

        // Começa uma transação
        $conn->begin_transaction();

        try {
            foreach ($_POST['modulos'] as $idmodulo) {
                $stmt->bind_param("ii", $idunidade, $idmodulo);
                $stmt->execute();
            }
            // Confirma a transação
            $conn->commit();
            echo "Módulos desassociados com sucesso.";
        } catch (Exception $e) {
            // Se ocorrer um erro, desfaz a transação
            $conn->rollback();
            echo "Erro ao desassociar módulos: " . $e->getMessage();
        }

        $stmt->close();
    } else {
        echo "Nenhum módulo foi selecionado para desassociação.";
    }
} else {
    echo "ID da unidade não informado ou inválido.";
}

$conn->close();

echo "<script>location.href='?page=visualizar-unidade&idunidade=" . $idunidade . "';</script>";
