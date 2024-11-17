<?php
session_start();
if (empty($_SESSION["login"])) {
    echo "<script>location.href='../../../index.php';</script>";
    exit();
}
include('../../../cfg/config.php');

$iddepartamento = isset($_GET['iddepartamento']) ? intval($_GET['iddepartamento']) : 0;
$idunidade = isset($_GET['idunidade']) ? intval($_GET['idunidade']) : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modulos']) && isset($_POST['acao'])) {
    $modulos = $_POST['modulos'];
    $conn->begin_transaction();

    try {
        if ($_POST['acao'] === 'associar') {
            $stmt = $conn->prepare("INSERT INTO modulos_departamentos (iddepartamento, idmodulo) VALUES (?, ?)");
        } elseif ($_POST['acao'] === 'dessassociar') {
            $stmt = $conn->prepare("DELETE FROM modulos_departamentos WHERE iddepartamento = ? AND idmodulo = ?");
        }

        foreach ($modulos as $idmodulo) {
            $stmt->bind_param("ii", $iddepartamento, $idmodulo);
            $stmt->execute();
        }

        $conn->commit();
        echo "<script>alert('Ação concluída com sucesso!');</script>";
    } catch (Exception $e) {
        $conn->rollback();
        echo "<script>alert('Erro ao realizar ação: " . $e->getMessage() . "');</script>";
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
    <script>
        function toggleCheckboxes(selectAllCheckbox, checkboxClass) {
            const checkboxes = document.querySelectorAll(`.${checkboxClass}`);
            checkboxes.forEach(checkbox => checkbox.checked = selectAllCheckbox.checked);
        }
    </script>
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

            <div class="row mt-5">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Módulos Associados ao Departamento</h5>
                            <div class="form-check">
                                <input type="checkbox" id="selectAllAssociados" class="form-check-input" onchange="toggleCheckboxes(this, 'modulo-associado')">
                                <label for="selectAllAssociados" class="form-check-label">Selecionar Todos</label>
                            </div>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="acao" value="dessassociar">
                                <?php
                                $sql = "SELECT m.idmodulo, m.nome_modulo 
                                        FROM modulos m 
                                        JOIN modulos_departamentos md ON m.idmodulo = md.idmodulo
                                        WHERE md.iddepartamento = $iddepartamento";
                                $res = $conn->query($sql);

                                if ($res->num_rows > 0) {
                                    while ($row = $res->fetch_assoc()) {
                                        echo "<div class='form-check'>
                                                <input type='checkbox' name='modulos[]' value='" . $row['idmodulo'] . "' class='form-check-input modulo-associado'>
                                                <label class='form-check-label'>" . htmlspecialchars($row['nome_modulo']) . "</label>
                                              </div>";
                                    }
                                    echo "<button type='submit' class='btn btn-danger mt-3'>Desassociar Módulos</button>";
                                } else {
                                    echo "<p>Nenhum módulo associado.</p>";
                                }
                                ?>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Módulos Disponíveis na Unidade</h5>
                            <div class="form-check">
                                <input type="checkbox" id="selectAllNaoAssociados" class="form-check-input" onchange="toggleCheckboxes(this, 'modulo-nao-associado')">
                                <label for="selectAllNaoAssociados" class="form-check-label">Selecionar Todos</label>
                            </div>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="acao" value="associar">
                                <?php
                                $sql = "SELECT m.idmodulo, m.nome_modulo 
                                        FROM modulos m 
                                        JOIN unidades_modulos um ON m.idmodulo = um.idmodulo
                                        WHERE um.idunidade = $idunidade AND m.idmodulo NOT IN 
                                        (SELECT idmodulo FROM modulos_departamentos WHERE iddepartamento = $iddepartamento)";
                                $res = $conn->query($sql);

                                if ($res->num_rows > 0) {
                                    while ($row = $res->fetch_assoc()) {
                                        echo "<div class='form-check'>
                                                <input type='checkbox' name='modulos[]' value='" . $row['idmodulo'] . "' class='form-check-input modulo-nao-associado'>
                                                <label class='form-check-label'>" . htmlspecialchars($row['nome_modulo']) . "</label>
                                              </div>";
                                    }
                                    echo "<button type='submit' class='btn btn-primary mt-3'>Associar Módulos</button>";
                                } else {
                                    echo "<p>Nenhum módulo disponível.</p>";
                                }
                                ?>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>