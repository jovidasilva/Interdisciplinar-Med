<?php
include('../../../cfg/config.php');

if (isset($_GET['idunidade']) && is_numeric($_GET['idunidade'])) {
    $idunidade = intval($_GET['idunidade']);

    $stmt = $conn->prepare("SELECT idunidade, nome_unidade FROM unidades WHERE idunidade = ?");
    $stmt->bind_param("i", $idunidade);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $unidade = $result->fetch_assoc();
    } else {
        echo "Unidade não encontrada.";
        exit();
    }

    $modulosAssociados = $conn->query("SELECT m.idmodulo, m.nome_modulo FROM modulos m JOIN unidades_modulos um ON m.idmodulo = um.idmodulo WHERE um.idunidade = $idunidade");
    $nummodulosAssociados = $modulosAssociados->num_rows;

    $modulosNaoAssociados = $conn->query("SELECT idmodulo, nome_modulo FROM modulos WHERE idmodulo NOT IN (SELECT idmodulo FROM unidades_modulos WHERE idunidade = $idunidade)");
    $nummodulosNaoAssociados = $modulosNaoAssociados->num_rows;
} else {
    echo "ID da unidade não informado ou inválido.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Visualizar Unidade</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../../css/style.css">
    <style>
        body {
            overflow-y: hidden;
        }

        .container-card {
            display: flex;
            gap: 20px;
        }

        .card {
            width: 800px;
            padding: 10px;
            margin: 10px;
            overflow-y: auto;
            max-height: 650px;
        }
    </style>
    <script>
        function toggleCheckboxes(selectAllCheckbox, checkboxClass) {
            const checkboxes = document.querySelectorAll(`.${checkboxClass}`);
            checkboxes.forEach(checkbox => checkbox.checked = selectAllCheckbox.checked);
        }
    </script>
</head>

<body>
    <div class="container mt-3">
        <h3>
            <?php echo htmlspecialchars($unidade['nome_unidade']); ?>
            <button class="btn btn-secondary" onclick="location.href='?page=listar-unidades'">Voltar</button>
        </h3>
        <div class="container-card">
            <div class="card">
                <div class="card-header">
                    <h5>Módulos Associados à Unidade</h5>
                    <div class="form-check">
                        <input type="checkbox" id="selectAllAssociados" class="form-check-input"
                            onchange="toggleCheckboxes(this, 'modulo-associado')">
                        <label for="selectAllAssociados" class="form-check-label">Selecionar Todos</label>
                    </div>
                </div>
                <div class="card-body">
                    <form method="POST" action="?page=dessassociar-modulos&idunidade=<?php echo $idunidade; ?>">
                        <?php if ($nummodulosAssociados > 0): ?>
                            <?php while ($modulo = $modulosAssociados->fetch_assoc()): ?>
                                <div class="form-check">
                                    <input type="checkbox" name="modulos[]" value="<?php echo $modulo['idmodulo']; ?>"
                                        class="form-check-input modulo-associado"
                                        id="modulo-<?php echo $modulo['idmodulo']; ?>">
                                    <label class="form-check-label" for="modulo-<?php echo $modulo['idmodulo']; ?>">
                                        <?php echo htmlspecialchars($modulo['nome_modulo']); ?>
                                    </label>
                                </div>
                            <?php endwhile; ?>
                            <button type="submit" class="btn btn-danger mt-3">Desassociar Módulos</button>
                        <?php else: ?>
                            <p>Nenhum módulo associado.</p>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5>Módulos Não Associados à Unidade</h5>
                    <div class="form-check">
                        <input type="checkbox" id="selectAllNaoAssociados" class="form-check-input"
                            onchange="toggleCheckboxes(this, 'modulo-nao-associado')">
                        <label for="selectAllNaoAssociados" class="form-check-label">Selecionar Todos</label>
                    </div>
                </div>
                <div class="card-body">
                    <form method="POST" action="?page=associar-modulos&idunidade=<?php echo $idunidade; ?>">
                        <?php if ($nummodulosNaoAssociados > 0): ?>
                            <?php while ($modulo = $modulosNaoAssociados->fetch_assoc()): ?>
                                <div class="form-check">
                                    <input type="checkbox" name="modulos[]" value="<?php echo $modulo['idmodulo']; ?>"
                                        class="form-check-input modulo-nao-associado"
                                        id="modulo-<?php echo $modulo['idmodulo']; ?>">
                                    <label class="form-check-label" for="modulo-<?php echo $modulo['idmodulo']; ?>">
                                        <?php echo htmlspecialchars($modulo['nome_modulo']); ?>
                                    </label>
                                </div>
                            <?php endwhile; ?>
                            <button type="submit" class="btn btn-primary mt-3">Associar Módulos</button>
                        <?php else: ?>
                            <p>Nenhum módulo disponível.</p>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

</html>