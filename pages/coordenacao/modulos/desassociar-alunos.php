<?php
include('../../../cfg/config.php');

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
