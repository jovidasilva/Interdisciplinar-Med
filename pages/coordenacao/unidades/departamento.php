<?php
session_start();
if (empty($_SESSION["login"])) {
    echo "<script>location.href='../../../index.php';</script>";
    exit();
}
include('../../../cfg/config.php');

$idunidade = isset($_GET['idunidade']) ? intval($_GET['idunidade']) : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nome_departamento'])) {
    $nome_departamento = $conn->real_escape_string($_POST['nome_departamento']);
    $sql = "INSERT INTO departamentos (nome_departamento, idunidade) VALUES ('$nome_departamento', $idunidade)";
    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Departamento adicionado com sucesso!');</script>";
    } else {
        echo "<script>alert('Erro ao adicionar departamento: " . $conn->error . "');</script>";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['iddepartamento'])) {
    $iddepartamento = intval($_POST['iddepartamento']);
    $sql = "DELETE FROM departamentos WHERE iddepartamento = $iddepartamento AND idunidade = $idunidade";
    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Departamento removido com sucesso!');</script>";
    } else {
        echo "<script>alert('Erro ao remover departamento: " . $conn->error . "');</script>";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['idmodulo']) && isset($_POST['iddepartamento'])) {
    $idmodulo = intval($_POST['idmodulo']);
    $iddepartamento = intval($_POST['iddepartamento']);

    $sql = "UPDATE modulos SET iddepartamento = $iddepartamento WHERE idmodulo = $idmodulo";
    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Módulo associado ao departamento com sucesso!');</script>";
    } else {
        echo "<script>alert('Erro ao associar módulo ao departamento: " . $conn->error . "');</script>";
    }
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Gerenciar Departamentos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../../css/style.css">
</head>

<body>
    <header>
        <?php include('../../../includes/navbar.php'); ?>
        <?php include('../../../includes/menu-lateral-coordenacao.php'); ?>
    </header>
    <main>
        <div class="container mt-3">
            <h1>Adicionar Departamento</h1>
            <form action="departamento.php?idunidade=<?php echo $idunidade; ?>" method="POST">
                <div class="mb-3">
                    <label>Nome do Departamento</label>
                    <input type="text" name="nome_departamento" class="form-control" required>
                </div>
                <input type="hidden" name="idunidade" value="<?php echo $idunidade; ?>">
                <div class="mb-3">
                    <button type="submit" class="btn btn-success">Adicionar</button>
                    <a href="unidades.php" class="btn btn-secondary">Voltar</a>
                </div>
            </form>

            <h2 class="mt-5">Departamentos da Unidade</h2>
            <table class="table table-secondary table-bordered">
                <thead>
                    <tr>
                        <th>Nome do Departamento</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT * FROM departamentos WHERE idunidade = $idunidade";
                    $res = $conn->query($sql);

                    if ($res->num_rows > 0) {
                        while ($row = $res->fetch_object()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row->nome_departamento) . "</td>";
                            echo "<td>
                                <form action='departamento.php?idunidade=$idunidade' method='POST' style='display:inline;'>
                                    <input type='hidden' name='iddepartamento' value='" . $row->iddepartamento . "'>
                                    <input type='hidden' name='idunidade' value='" . $idunidade . "'>
                                    <button type='submit' class='btn btn-danger' onclick=\"return confirm('Tem certeza que deseja excluir?')\">Excluir</button>
                                </form>
                                <button onclick=\"location.href='departamentos-modulos.php?iddepartamento=" . $row->iddepartamento . "&idunidade=$idunidade';\" class='btn btn-info'>Gerenciar Módulos</button>
                            </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='2'>Nenhum departamento encontrado.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </main>
</body>

</html>