<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $acao = $_POST['acao'];
    $idunidade = $_POST['idunidade'];

    if ($acao == 'associar') {
        if (!empty($_POST['preceptores'])) {
            $preceptores = $_POST['preceptores'];
            foreach ($preceptores as $preceptor) {
                $sql = "INSERT INTO preceptores_unidades (idusuario, idunidade) VALUES ('$preceptor', '$idunidade')";
                $conn->query($sql);
            }
        }
        echo "<script>location.href='?page=visualizar-preceptor&idunidade=" . $idunidade . "';</script>";
        exit;
    } elseif ($acao == 'desassociar') {
        if (!empty($_POST['preceptores'])) {
            $preceptores = $_POST['preceptores'];
            foreach ($preceptores as $preceptor) {
                $sql = "DELETE FROM preceptores_unidades WHERE idusuario = '$preceptor' AND idunidade = '$idunidade'";
                $conn->query($sql);
            }
        }
        echo "<script>location.href='?page=visualizar-preceptor&idunidade=" . $idunidade . "';</script>";
        exit;
    }
}