<?php
require('../../../cfg/config.php');
switch ($_REQUEST['acao']) {
    case 'cadastrar':
        if (!empty($_POST['nome_unidade'])) {
            $nome_unidade = $conn->real_escape_string($_POST['nome_unidade']);
            $sql = "INSERT INTO unidades (nome_unidade) VALUES ('$nome_unidade')";
            $res = $conn->query($sql);
            if ($res == true) {
                echo "<script>alert('Módulo cadastrado com sucesso!');</script>";
            } else {
                echo "<script>alert('Não foi possivel cadastrar.');</script>";
            }
            echo "<script>location.href='unidades.php';</script>";
        } else {
            echo "<script>alert('O campo nome do módulo é obrigatório.');</script>";
        }
        break;
    case 'editar':
        $id_unidade = intval($_POST['id_unidade']);
        $nome_unidade = $conn->real_escape_string($_POST['nome_unidade']);

        $sql = "UPDATE unidades SET nome_unidade='$nome_unidade' WHERE id_unidade=$id_unidade";
        if ($conn->query($sql) === TRUE) {
            echo "<script>alert('Módulo alterado com sucesso!');</script>";
        } else {
            echo "<script>alert('Não foi possivel editar.');</script>";
        }
        echo "<script>location.href='unidades.php';</script>";
        break;
    case 'excluir':
        if (isset($_REQUEST['id_unidade'])) {
            $id_unidade = intval($_REQUEST['id_unidade']);
            $sql = "DELETE FROM unidades WHERE id_unidade = $id_unidade";
            if ($conn->query($sql) === TRUE) {
                echo "<script>alert('Módulo excluído com sucesso!');</script>";
            } else {
                echo "<script>alert('Não foi possível excluir o módulo.');</script>";
            }
            echo "<script>location.href='unidades.php';</script>";
        } else {
        }
        break;
}
