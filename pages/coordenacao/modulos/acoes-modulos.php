<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['login']) || !in_array($_SESSION['tipo'] ?? null, [2, 3], true)) {
    header('Location: ' . str_repeat('../', 3) . 'index.php');
    exit();
}
if (!isset($conn)) {
    require_once __DIR__ . '/' . str_repeat('../', 3) . 'cfg/config.php';
}
require_once __DIR__ . '/' . str_repeat('../', 3) . 'includes/csrf.php';
?>
<?php
switch ($_REQUEST['acao']) {
    case 'cadastrar':
        csrf_verify_or_die();
        if (!empty($_POST['nome_modulo'])) {
            $nome_modulo = $conn->real_escape_string($_POST['nome_modulo']);
            $periodo = $conn->real_escape_string($_POST['periodo']);
            $sql = "INSERT INTO modulos (nome_modulo, periodo) VALUES ('$nome_modulo', '$periodo')";
            $res = $conn->query($sql);
            if ($res == true) {
                echo "<script>alert('Módulo cadastrado com sucesso!');</script>";
            } else {
                echo "<script>alert('Não foi possivel cadastrar.');</script>";
            }
            echo "<script>location.href='modulos.php';</script>";
        } else {
            echo "<script>alert('O campo nome do módulo é obrigatório.');</script>";
        }
        break;
    case 'editar':
        csrf_verify_or_die();
        $idmodulo = intval($_POST['idmodulo']);
        $nome_modulo = $conn->real_escape_string($_POST['nome_modulo']);
        $periodo = $conn->real_escape_string($_POST['periodo']);

        $sql = "UPDATE modulos SET nome_modulo = ?, periodo = ? WHERE idmodulo = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param('ssi', $nome_modulo, $periodo, $idmodulo);
            if ($stmt->execute()) {
                echo "<script>alert('Módulo alterado com sucesso!');</script>";
            } else {
                echo "<script>alert('Não foi possível editar.');</script>";
            }
            $stmt->close();
        } else {
            echo "<script>alert('Erro na preparação da consulta.');</script>";
        }

        echo "<script>location.href='modulos.php';</script>";
        break;

        case 'excluir':
            if (isset($_REQUEST['idmodulo'])) {
                $idmodulo = intval($_REQUEST['idmodulo']);
    
                // delete de unidades_modulos
                $sql = "DELETE FROM unidades_modulos WHERE idmodulo = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $idmodulo);
                $stmt->execute();
                $stmt->close();
    
                // delete de modulo
                $sql = "DELETE FROM modulos WHERE idmodulo = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $idmodulo);
                if ($stmt->execute()) {
                    echo "<script>alert('Módulo excluído com sucesso!');</script>";
                } else {
                    echo "<script>alert('Não foi possível excluir o módulo.');</script>";
                }
                $stmt->close();
    
                echo "<script>location.href='modulos.php';</script>";
            }
            break;
}
