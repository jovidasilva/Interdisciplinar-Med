<?php
include('../../../cfg/config.php');

if (isset($_POST['alunos']) && isset($_GET['idmodulo'])) {
    $idmodulo = $_GET['idmodulo'];
    $alunos = $_POST['alunos'];

    $stmt = $conn->prepare("INSERT INTO modulo_alunos (idmodulo, idusuario) VALUES (?, ?)");
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
