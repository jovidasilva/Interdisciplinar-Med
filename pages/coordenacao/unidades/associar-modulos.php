<?php
session_start();
include('../../../cfg/config.php');

if (!isset($_GET['idunidade']) || !is_numeric($_GET['idunidade'])) {
    $_SESSION['msg'] = 'ID da unidade não informado ou inválido.';
    header('Location: unidades.php?page=visualizar-unidade&idunidade=' . $_GET['idunidade']);
    exit();
}

$idunidade = intval($_GET['idunidade']);

// Verifica se existem módulos selecionados
if (!isset($_POST['modulos']) || empty($_POST['modulos'])) {
    $_SESSION['msg'] = 'Nenhum módulo foi selecionado.';
    header('Location: unidades.php?page=visualizar-unidade&idunidade=' . $idunidade);
    exit();
}

$modulos = $_POST['modulos'];
$sucesso = 0;
$falha = 0;

// Inicia a transação
$conn->begin_transaction();

foreach ($modulos as $idmodulo) {
    $idmodulo = intval($idmodulo);

    // Verifica se o módulo já está associado
    $verificar = $conn->prepare("SELECT * FROM unidades_modulos WHERE idunidade = ? AND idmodulo = ?");
    $verificar->bind_param("ii", $idunidade, $idmodulo);
    $verificar->execute();
    $resultado = $verificar->get_result();

    if ($resultado->num_rows > 0) {
        $falha++;
        continue;
    }

    // Insere a associação
    $stmt = $conn->prepare("INSERT INTO unidades_modulos (idunidade, idmodulo) VALUES (?, ?)");
    $stmt->bind_param("ii", $idunidade, $idmodulo);

    if ($stmt->execute()) {
        $sucesso++;
    } else {
        error_log("Erro ao associar módulo ID $idmodulo: " . $stmt->error);
        $falha++;
    }
}

// Confirma ou desfaz a transação
if ($falha === 0) {
    $conn->commit();
    $_SESSION['msg'] = 'Módulos associados com sucesso!';
} else {
    $conn->rollback();
    $_SESSION['msg'] = 'Erro ao associar módulos. Sucessos: ' . $sucesso . ', Falhas: ' . $falha;
}

// Redireciona para unidades.php com os parâmetros corretos
header('Location: unidades.php?page=visualizar-unidade&idunidade=' . $idunidade);
exit();
?>