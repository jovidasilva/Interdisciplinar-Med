<?php
session_start();
include('../../../cfg/config.php');

if (isset($_POST['idunidade']) && isset($_POST['idmodulo']) && is_numeric($_POST['idunidade']) && is_numeric($_POST['idmodulo'])) {
    $idunidade = intval($_POST['idunidade']);
    $idmodulo = intval($_POST['idmodulo']);

    // Verifica se o módulo já está associado
    $verificar = $conn->prepare("SELECT * FROM unidades_modulos WHERE idunidade = ? AND idmodulo = ?");
    $verificar->bind_param("ii", $idunidade, $idmodulo);
    $verificar->execute();
    $resultado = $verificar->get_result();

    if ($resultado->num_rows === 0) {
        $_SESSION['msg'] = 'Módulo não está associado.';
        header('Location: unidades.php?page=visualizar-unidade&idunidade=' . $idunidade);
        exit();
    }

    // Desassocia o módulo
    $stmt = $conn->prepare("DELETE FROM unidades_modulos WHERE idunidade = ? AND idmodulo = ?");
    $stmt->bind_param("ii", $idunidade, $idmodulo);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $_SESSION['msg'] = 'Módulo desassociado com sucesso!';
    } else {
        $_SESSION['msg'] = 'Erro ao desassociar módulo.';
    }

    $stmt->close();
    $conn->close();

    // Redireciona de volta para unidades.php com os parâmetros corretos
    header('Location: unidades.php?page=visualizar-unidade&idunidade=' . $idunidade);
    exit();
} else {
    $_SESSION['msg'] = 'ID da unidade ou do módulo não informado ou inválido.';
    header('Location: unidades.php?page=visualizar-unidade&idunidade=' . $_POST['idunidade']);
    exit();
}
?>