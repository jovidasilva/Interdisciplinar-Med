<?php
require('../../../cfg/config.php');
switch ($_REQUEST['acao']) {
    case 'cadastrar':
        if (!empty($_POST['nome_unidade']) && !empty($_POST['endereco_unidade'])) {
            $nome_unidade = $conn->real_escape_string($_POST['nome_unidade']);
            $endereco_unidade = $conn->real_escape_string($_POST['endereco_unidade']);
            $sql = "INSERT INTO unidades (nome_unidade, endereco_unidade) VALUES ('$nome_unidade', '$endereco_unidade')";
            $res = $conn->query($sql);
            if ($res == true) {
                echo "<script>alert('Unidade cadastrada com sucesso!');</script>";
            } else {
                echo "<script>alert('Não foi possível cadastrar a unidade.');</script>";
            }
            echo "<script>location.href='unidades.php';</script>";
        } else {
            echo "<script>alert('Os campos nome e endereço da unidade são obrigatórios.');</script>";
        }
        break;
    case 'editar':
        $idunidade = intval($_POST['idunidade']);
        $nome_unidade = $conn->real_escape_string($_POST['nome_unidade']);
        $endereco_unidade = $conn->real_escape_string($_POST['endereco_unidade']);

        $sql = "UPDATE unidades SET nome_unidade='$nome_unidade', endereco_unidade='$endereco_unidade' WHERE idunidade=$idunidade";
        if ($conn->query($sql) === TRUE) {
            echo "<script>alert('Unidade alterada com sucesso!');</script>";
        } else {
            echo "<script>alert('Não foi possível editar a unidade.');</script>";
        }
        echo "<script>location.href='unidades.php';</script>";
        break;
    case 'excluir':
        if (isset($_REQUEST['idunidade'])) {
            $idunidade = intval($_REQUEST['idunidade']);
            $sql = "DELETE FROM unidades WHERE idunidade = $idunidade";
            if ($conn->query($sql) === TRUE) {
                echo "<script>alert('Unidade excluída com sucesso!');</script>";
            } else {
                echo "<script>alert('Não foi possível excluir a unidade.');</script>";
            }
            echo "<script>location.href='unidades.php';</script>";
        } else {
        }
        break;
}
