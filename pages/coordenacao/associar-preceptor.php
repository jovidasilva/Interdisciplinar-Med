<?php
session_start();
include('../../cfg/config.php');

if (empty($_SESSION["login"])) {
    echo "<script>location.href='../../index.php';</script>";
    exit();
}

// Buscar todos os preceptores
$sqlPreceptores = "SELECT u.*, un.nome_unidade FROM usuarios u 
                   LEFT JOIN preceptores_unidades pu ON u.idusuario = pu.idusuario 
                   LEFT JOIN unidades un ON pu.idunidade = un.idunidade 
                   WHERE u.tipo = 1";
$resPreceptores = $conn->query($sqlPreceptores);

// Buscar todas as unidades
$sqlUnidades = "SELECT idunidade, nome_unidade FROM unidades";
$resUnidades = $conn->query($sqlUnidades);

// Associar ou desassociar preceptores
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $preceptoresSelecionados = $_POST['preceptores'] ?? [];
    $idUnidade = $_POST['idunidade'] ?? null;
    $acao = $_POST['acao'] ?? '';
    $idPreceptor = $_POST['idPreceptor'] ?? null;
    $modulosSelecionados = $_POST['modulos'] ?? [];

    if ($acao === 'associar' && !empty($preceptoresSelecionados) && $idUnidade) {
        foreach ($preceptoresSelecionados as $idPreceptor) {
            // Associar preceptor à unidade
            $stmt = $conn->prepare("INSERT INTO preceptores_unidades (idusuario, idunidade) VALUES (?, ?)");
            $stmt->bind_param("ii", $idPreceptor, $idUnidade);
            $stmt->execute();
        }
        echo "<script>alert('Preceptores associados com sucesso!'); location.href='associar-preceptor.php';</script>";
        exit();
    }

    if ($acao === 'desassociar' && !empty($preceptoresSelecionados)) {
        foreach ($preceptoresSelecionados as $idPreceptor) {
            // Desassociar o preceptor de todas as unidades
            $stmt = $conn->prepare("DELETE FROM preceptores_unidades WHERE idusuario = ?");
            $stmt->bind_param("i", $idPreceptor);
            $stmt->execute();

            // Desassociar o preceptor de todos os módulos
            $stmt = $conn->prepare("DELETE FROM preceptores_modulos WHERE idusuario = ?");
            $stmt->bind_param("i", $idPreceptor);
            $stmt->execute();
        }
        echo "<script>alert('Preceptores desassociados com sucesso!'); location.href='associar-preceptor.php';</script>";
        exit();
    }

    if ($acao === 'associar-modulos' && !empty($idPreceptor)) {
        // Desassociar todos os módulos do preceptor antes de associar os novos
        $stmt = $conn->prepare("DELETE FROM preceptores_modulos WHERE idusuario = ?");
        $stmt->bind_param("i", $idPreceptor);
        $stmt->execute();

        foreach ($modulosSelecionados as $idModulo) {
            // Associar módulos ao preceptor
            $stmt = $conn->prepare("INSERT INTO preceptores_modulos (idusuario, idmodulo) VALUES (?, ?)");
            $stmt->bind_param("ii", $idPreceptor, $idModulo);
            $stmt->execute();
        }
        echo "<script>alert('Módulos associados com sucesso!'); location.href='associar-preceptor.php';</script>";
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idPreceptor = $_POST['idPreceptor'] ?? null;
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'dissociar' && !empty($idPreceptor)) {
        // Desassociar o preceptor de todas as unidades
        $stmt = $conn->prepare("DELETE FROM preceptores_unidades WHERE idusuario = ?");
        $stmt->bind_param("i", $idPreceptor);
        $stmt->execute();

        // Desassociar o preceptor de todos os módulos
        $stmt = $conn->prepare("DELETE FROM preceptores_modulos WHERE idusuario = ?");
        $stmt->bind_param("i", $idPreceptor);
        $stmt->execute();

        echo "<script>alert('Preceptor desassociado com sucesso!'); location.href='listar-preceptor.php';</script>";
        exit();
    }
}

// Lógica para buscar módulos do preceptor via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['idPreceptor'])) {
    $idPreceptor = $_GET['idPreceptor'];
    $sqlModulos = "
        SELECT m.idmodulo, m.nome_modulo, IF(pm.idmodulo IS NULL, 0, 1) AS associado
        FROM modulos m
        LEFT JOIN preceptores_modulos pm ON m.idmodulo = pm.idmodulo AND pm.idusuario = ?
    ";
    $stmtModulos = $conn->prepare($sqlModulos);
    $stmtModulos->bind_param("i", $idPreceptor);
    $stmtModulos->execute();
    $resModulos = $stmtModulos->get_result();

    $modulos = [];
    while ($modulo = $resModulos->fetch_assoc()) {
        $modulos[] = $modulo;
    }

    echo json_encode($modulos);
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Associar Preceptores</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../css/style.css">
    <style>
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

        function loadModulos(idPreceptor) {
            fetch(`associar-preceptor.php?idPreceptor=${idPreceptor}`)
                .then(response => response.json())
                .then(data => {
                    const modulosContainer = document.getElementById('modulos-container');
                    modulosContainer.innerHTML = '';
                    if (data.length > 0) {
                        data.forEach(modulo => {
                            const moduloElement = document.createElement('div');
                            moduloElement.classList.add('form-check');
                            moduloElement.innerHTML = `
                                <input type="checkbox" name="modulos[]" value="${modulo.idmodulo}" class="form-check-input modulo-associado" ${modulo.associado ? 'checked' : ''}>
                                <label class="form-check-label">${modulo.nome_modulo}</label>
                            `;
                            modulosContainer.appendChild(moduloElement);
                        });
                        document.getElementById('btn-associar-modulos').style.display = 'block';
                    } else {
                        modulosContainer.innerHTML = '<p>Nenhum módulo disponível.</p>';
                    }
                })
                .catch(error => console.error('Erro ao carregar módulos:', error));
        }

        document.addEventListener('DOMContentLoaded', function () {
            const preceptorSelect = document.getElementById('preceptorSelect');
            preceptorSelect.addEventListener('change', function () {
                const idPreceptor = this.value;
                if (idPreceptor) {
                    document.getElementById('idPreceptor').value = idPreceptor;
                    loadModulos(idPreceptor);
                } else {
                    document.getElementById('modulos-container').innerHTML = '<p>Selecione um preceptor para ver os módulos disponíveis.</p>';
                    document.getElementById('btn-associar-modulos').style.display = 'none';
                }
            });
        });
    </script>
</head>
<body>
    <header>
        <?php include('../../includes/navbar.php'); ?>
        <?php include('../../includes/menu-lateral-coordenacao.php'); ?>
    </header>
    <main>
        <div class="container mt-3">
            <h3>Gerenciar Preceptores</h3>
            <button class="btn btn-secondary mb-3" onclick="location.href='listar-preceptor.php'">Voltar</button>

            <div class="container-card">
                <!-- Preceptores Não Associados -->
                <div class="card">
                    <div class="card-header">
                        <h5>Preceptores Não Associados</h5>
                        <?php
                        $preceptoresNaoAssociados = [];
                        while ($preceptor = $resPreceptores->fetch_assoc()) {
                            if (is_null($preceptor['nome_unidade'])) {
                                $preceptoresNaoAssociados[] = $preceptor;
                            }
                        }
                        if (count($preceptoresNaoAssociados) > 0): ?>
                            <div class="form-check">
                                <input type="checkbox" id="selectAllNaoAssociados" class="form-check-input" onchange="toggleCheckboxes(this, 'preceptor-nao-associado')">
                                <label for="selectAllNaoAssociados" class="form-check-label">Selecionar Todos</label>
                            </div>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="acao" value="associar">
                                <?php foreach ($preceptoresNaoAssociados as $preceptor): ?>
                                    <div class="form-check">
                                    <input type="checkbox" name="preceptores[]" value="<?php echo $preceptor['idusuario']; ?>" class="form-check-input preceptor-nao-associado">
                                        <label class="form-check-label"><?php echo htmlspecialchars($preceptor['nome']); ?></label>
                                    </div>
                                <?php endforeach; ?>
                                <div class="mt-3">
                                    <select name="idunidade" class="form-select" required>
                                        <option value="">Selecione uma unidade</option>
                                        <?php while ($unidade = $resUnidades->fetch_assoc()): ?>
                                            <option value="<?php echo $unidade['idunidade']; ?>"><?php echo htmlspecialchars($unidade['nome_unidade']); ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                    <button type="submit" class="btn btn-primary mt-3">Associar Preceptores</button>
                                </div>
                            <?php else: ?>
                                <p>Não há nenhum preceptor desassociado no momento.</p>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

                <!-- Preceptores Associados -->
                <div class="card">
                    <div class="card-header">
                        <h5>Preceptores Associados</h5>
                        <div class="form-check">
                            <input type="checkbox" id="selectAllAssociados" class="form-check-input" onchange="toggleCheckboxes(this, 'preceptor-associado')">
                            <label for="selectAllAssociados" class="form-check-label">Selecionar Todos</label>
                        </div>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="acao" value="desassociar">
                            <?php mysqli_data_seek($resPreceptores, 0); // Reset result set pointer ?>
                            <?php if ($resPreceptores->num_rows > 0): ?>
                                <?php while ($preceptor = $resPreceptores->fetch_assoc()): ?>
                                    <?php if (!is_null($preceptor['nome_unidade'])): ?>
                                        <div class="form-check">
                                            <input type="checkbox" name="preceptores[]" value="<?php echo $preceptor['idusuario']; ?>" class="form-check-input preceptor-associado">
                                            <label class="form-check-label"><?php echo htmlspecialchars($preceptor['nome']); ?> - <?php echo htmlspecialchars($preceptor['nome_unidade']); ?></label>
                                        </div>
                                    <?php endif; ?>
                                <?php endwhile; ?>
                                <button type="submit" class="btn btn-danger mt-3">Desassociar Preceptores</button>
                            <?php else: ?>
                                <p>Nenhum preceptor associado.</p>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>

            <div class="container-card">
                <!-- Módulos Associados ao Preceptor Selecionado -->
                <div class="card">
                    <div class="card-header">
                        <h5>Módulos Associados ao Preceptor</h5>
                        <div class="form-check">
                            <select id="preceptorSelect" class="form-select">
                                <option value="">Selecione um preceptor</option>
                                <?php mysqli_data_seek($resPreceptores, 0); // Reset result set pointer ?>
                                <?php while ($preceptor = $resPreceptores->fetch_assoc()): ?>
                                    <?php if (!is_null($preceptor['nome_unidade'])): ?>
                                        <option value="<?php echo $preceptor['idusuario']; ?>"><?php echo htmlspecialchars($preceptor['nome']); ?> - <?php echo htmlspecialchars($preceptor['nome_unidade']); ?></option>
                                    <?php endif; ?>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    <div class="card-body">
                        <form method="POST" id="form-modulos">
                            <input type="hidden" name="acao" value="associar-modulos">
                            <input type="hidden" name="idPreceptor" id="idPreceptor">
                            <div id="modulos-container">
                                <p>Selecione um preceptor para ver os módulos disponíveis.</p>
                            </div>
                            <button type="submit" class="btn btn-primary mt-3" id="btn-associar-modulos" style="display: none;">Associar Módulos</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <footer>
        <div class="card footer-home rounded-0">
            <div class="card-body">
            </div>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>
        function toggleCheckboxes(selectAllCheckbox, checkboxClass) {
            const checkboxes = document.querySelectorAll(`.${checkboxClass}`);
            checkboxes.forEach(checkbox => checkbox.checked = selectAllCheckbox.checked);
        }

        function loadModulos(idPreceptor) {
            fetch(`associar-preceptor.php?idPreceptor=${idPreceptor}`)
                .then(response => response.json())
                .then(data => {
                    const modulosContainer = document.getElementById('modulos-container');
                    modulosContainer.innerHTML = '';
                    if (data.length > 0) {
                        data.forEach(modulo => {
                            const moduloElement = document.createElement('div');
                            moduloElement.classList.add('form-check');
                            moduloElement.innerHTML = `
                                <input type="checkbox" name="modulos[]" value="${modulo.idmodulo}" class="form-check-input modulo-associado" ${modulo.associado ? 'checked' : ''}>
                                <label class="form-check-label">${modulo.nome_modulo}</label>
                            `;
                            modulosContainer.appendChild(moduloElement);
                        });
                        document.getElementById('btn-associar-modulos').style.display = 'block';
                    } else {
                        modulosContainer.innerHTML = '<p>Nenhum módulo disponível.</p>';
                    }
                })
                .catch(error => console.error('Erro ao carregar módulos:', error));
        }

        document.addEventListener('DOMContentLoaded', function () {
            const preceptorSelect = document.getElementById('preceptorSelect');
            preceptorSelect.addEventListener('change', function () {
                const idPreceptor = this.value;
                if (idPreceptor) {
                    document.getElementById('idPreceptor').value = idPreceptor;
                    loadModulos(idPreceptor);
                } else {
                    document.getElementById('modulos-container').innerHTML = '<p>Selecione um preceptor para ver os módulos disponíveis.</p>';
                    document.getElementById('btn-associar-modulos').style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>