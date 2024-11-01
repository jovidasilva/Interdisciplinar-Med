<?php
require_once '../../../cfg/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $idusuario = isset($_POST['idusuario']) ? intval($_POST['idusuario']) : null;

    if (!$idusuario) {
        echo "<script>alert('Erro: Usuário não encontrado.'); location.href='realizar-avaliacao.php';</script>";
        exit();
    }

    $total_pontuacao = 0;
    $total_perguntas = 0;

    foreach ($_POST as $key => $value) {
        if (strpos($key, 'pergunta_') === 0) {
            $total_pontuacao += $value;
            $total_perguntas++;
        }
    }

    $media = $total_perguntas > 0 ? $total_pontuacao / $total_perguntas : 0;
    $data_avaliacao = date('Y-m-d H:i:s');

    $query = "INSERT INTO avaliacoes (idusuario, nota, data_avaliacao) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iis", $idusuario, $media, $data_avaliacao);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo "<script>alert('Avaliação registrada com sucesso!');</script>";
    } else {
        echo "<script>alert('Erro ao registrar a avaliação.');</script>";
    }

    $stmt->close();
}
?>

<script>location.href='avaliacoes.php';</script>
