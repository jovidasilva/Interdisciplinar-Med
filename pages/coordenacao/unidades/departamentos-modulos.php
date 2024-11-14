<?php
session_start();
if (empty($_SESSION["login"])) {
    echo "<script>location.href='../../../index.php';</script>";
    exit();
}
include('../../../cfg/config.php');

$iddepartamento = isset($_GET['iddepartamento']) ? intval($_GET['iddepartamento']) : 0;
$idunidade = isset($_GET['idunidade']) ? intval($_GET['idunidade']) : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['idmodulo']) && isset($_POST['acao'])) {
    $idmodulo = intval($_POST['idmodulo']);
    if ($_POST['acao'] === 'associar') {
        $sql = "INSERT INTO modulos_departamentos (iddepartamento, idmodulo) VALUES ($iddepartamento, $idmodulo)";
    } elseif ($_POST['acao'] === 'dessassociar') {
        $sql = "DELETE FROM modulos_departamentos WHERE iddepartamento = $iddepartamento AND idmodulo = $idmodulo";
    }

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Ação concluída com sucesso!');</script>";
    } else {
        echo "<script>alert('Erro ao realizar ação: " . $conn->error . "');</script>";
    }
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Gerenciar Módulos do Departamento</title>
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
            <h1>Gerenciar Módulos do Departamento</h1>
            <a href="departamento.php?idunidade=<?php echo $idunidade; ?>" class="btn btn-secondary">Voltar</a>

            <h2 class="mt-5">Módulos Associados ao Departamento</h2>
            <table class="table table-secondary table-bordered">
                <thead>
                    <tr>
                        <th>Nome do Módulo</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT m.idmodulo, m.nome_modulo 
                            FROM modulos m 
                            JOIN modulos_departamentos md ON m.idmodulo = md.idmodulo
                            WHERE md.iddepartamento = $iddepartamento";
                    $res = $conn->query($sql);

                    if ($res->num_rows > 0) {
                        while ($row = $res->fetch_object()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row->nome_modulo) . "</td>";
                            echo "<td>
                                <form action='departamentos-modulos.php?iddepartamento=$iddepartamento&idunidade=$idunidade' method='POST' style='display:inline;'>
                                    <input type='hidden' name='idmodulo' value='" . $row->idmodulo . "'>
                                    <input type='hidden' name='acao' value='dessassociar'>
                                    <button type='submit' class='btn btn-danger' onclick=\"return confirm('Tem certeza que deseja desassociar?')\">Desassociar</button>
                                </form>
                            </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='2'>Nenhum módulo associado.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>

            <h2 class="mt-5">Módulos Disponíveis na Unidade</h2>
            <table class="table table-secondary table-bordered">
                <thead>
                    <tr>
                        <th>Nome do Módulo</th>
                        <th>Departamentos Associados</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT m.idmodulo, m.nome_modulo 
                            FROM modulos m 
                            JOIN unidades_modulos um ON m.idmodulo = um.idmodulo
                            WHERE um.idunidade = $idunidade AND m.idmodulo NOT IN 
                            (SELECT idmodulo FROM modulos_departamentos WHERE iddepartamento = $iddepartamento)";
                    $res = $conn->query($sql);

                    if ($res->num_rows > 0) {
                        while ($row = $res->fetch_object()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row->nome_modulo) . "</td>";

                            // Listar departamentos associados
                            $sql_dept = "SELECT d.nome_departamento 
                                         FROM departamentos d 
                                         JOIN modulos_departamentos md ON d.iddepartamento = md.iddepartamento
                                         WHERE md.idmodulo = $row->idmodulo";
                            $res_dept = $conn->query($sql_dept);
                            $departamentos = [];
                            while ($dept = $res_dept->fetch_object()) {
                                $departamentos[] = htmlspecialchars($dept->nome_departamento);
                            }
                            echo "<td>" . implode(", ", $departamentos) . "</td>";

                            echo "<td>
                                <form action='departamentos-modulos.php?iddepartamento=$iddepartamento&idunidade=$idunidade' method='POST' style='display:inline;'>
                                    <input type='hidden' name='idmodulo' value='" . $row->idmodulo . "'>
                                    <input type='hidden' name='acao' value='associar'>
                                    <button type='submit' class='btn btn-success'>Associar</button>
                                </form>
                            </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='3'>Nenhum módulo disponível na unidade.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </main>
</body>

</html>