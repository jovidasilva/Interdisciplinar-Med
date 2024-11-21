<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include('../../../cfg/config.php');

if (empty($_SESSION["login"])) {
    echo json_encode([
        "success" => false,
        "message" => "Usuário não autenticado. Redirecionando para a página de login.",
        "redirect" => "../../index.php"
    ]);
    exit();
}

// Verificar se o ID da unidade foi passado na URL
if (isset($_GET['idunidade']) && is_numeric($_GET['idunidade'])) {
    $idunidade = intval($_GET['idunidade']);

    // Buscar a unidade pelo ID
    $stmt = $conn->prepare("SELECT idunidade, nome_unidade FROM unidades WHERE idunidade = ?");
    $stmt->bind_param("i", $idunidade);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $unidade = $result->fetch_assoc();
    } else {
        echo json_encode(["success" => false, "message" => "Unidade não encontrada."]);
        exit();
    }

    // Buscar todos os preceptores
    $sqlPreceptores = "SELECT u.*, pu.idunidade, un.nome_unidade FROM usuarios u 
                       LEFT JOIN preceptores_unidades pu ON u.idusuario = pu.idusuario 
                       LEFT JOIN unidades un ON pu.idunidade = un.idunidade 
                       WHERE u.tipo = 1";
    $resPreceptores = $conn->query($sqlPreceptores);

    // Buscar módulos associados e não associados
    $modulosAssociados = $conn->query("SELECT m.idmodulo, m.nome_modulo FROM modulos m JOIN unidades_modulos um ON m.idmodulo = um.idmodulo WHERE um.idunidade = $idunidade");
    $nummodulosAssociados = $modulosAssociados->num_rows;

    $modulosNaoAssociados = $conn->query("SELECT idmodulo, nome_modulo FROM modulos WHERE idmodulo NOT IN (SELECT idmodulo FROM unidades_modulos WHERE idunidade = $idunidade)");
    $nummodulosNaoAssociados = $modulosNaoAssociados->num_rows;
} else {
    echo json_encode(["success" => false, "message" => "ID da unidade não informado ou inválido."]);
    exit();
}

// Associar ou desassociar preceptores e módulos via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ["success" => false, "message" => ""];
    $preceptoresSelecionados = $_POST['preceptores'] ?? [];
    $modulosSelecionados = $_POST['modulos'] ?? [];
    $idunidade = intval($_POST['idunidade'] ?? 0);
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'associar' && !empty($modulosSelecionados)) {
        foreach ($modulosSelecionados as $idModulo) {
            // Associar módulo à unidade
            $stmt = $conn->prepare("INSERT INTO unidades_modulos (idmodulo, idunidade) VALUES (?, ?)");
            $stmt->bind_param("ii", $idModulo, $idunidade);
            $stmt->execute();
        }
        $response["success"] = true;
        $response["message"] = 'Módulos associados com sucesso!';
    } elseif ($acao === 'desassociar' && !empty($modulosSelecionados)) {
        foreach ($modulosSelecionados as $idModulo) {
            // Desassociar módulo da unidade
            $stmt = $conn->prepare("DELETE FROM unidades_modulos WHERE idmodulo = ? AND idunidade = ?");
            $stmt->bind_param("ii", $idModulo, $idunidade);
            $stmt->execute();
        }
        $response["success"] = true;
        $response["message"] = 'Módulos desassociados com sucesso!';
    } elseif ($acao === 'associar' && !empty($preceptoresSelecionados)) {
        foreach ($preceptoresSelecionados as $idPreceptor) {
            // Desassociar o preceptor de qualquer unidade anterior
            $stmt = $conn->prepare("DELETE FROM preceptores_unidades WHERE idusuario = ?");
            $stmt->bind_param("i", $idPreceptor);
            $stmt->execute();

            // Associar preceptor à nova unidade
            $stmt = $conn->prepare("INSERT INTO preceptores_unidades (idusuario, idunidade) VALUES (?, ?)");
            $stmt->bind_param("ii", $idPreceptor, $idunidade);
            $stmt->execute();
        }
        $response["success"] = true;
        $response["message"] = 'Preceptores associados com sucesso!';
    } elseif ($acao === 'desassociar' && !empty($preceptoresSelecionados)) {
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
        $response["success"] = true;
        $response["message"] = 'Preceptores desassociados com sucesso!';
    } else {
        $response["message"] = 'Ação inválida ou nenhum item selecionado.';
    }

    echo json_encode($response);
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function toggleCheckboxes(selectAllCheckbox, checkboxClass) {
            const checkboxes = document.querySelectorAll(`.${checkboxClass}`);
            checkboxes.forEach(checkbox => checkbox.checked = selectAllCheckbox.checked);
        }

        function submitForm(event, form) {
            event.preventDefault();
            const formData = $(form).serialize();
            console.log('Dados do formulário:', formData);

            $.ajax({
                type: 'POST',
                url: 'visualizar-unidade.php?idunidade=<?php echo $idunidade; ?>',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    console.log('Resposta do servidor:', response);
                    if (response.success) {
                        alert(response.message);
                        location.reload(); // Recarregar a página para refletir as mudanças
                    } else {
                        alert(response.message || 'Ocorreu um erro ao processar sua solicitação.');
                        if (response.redirect) {
                            window.location.href = response.redirect;
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Erro na solicitação AJAX:', error);
                    console.log('Status:', status);
                    console.log('Resposta completa:', xhr.responseText);
                    alert('Ocorreu um erro ao processar sua solicitação.');
                }
            });
        }
    </script>
</head>

<body>
    <div class="container mt-3">
        <h3>
            <?php echo htmlspecialchars($unidade['nome_unidade']); ?>
            <button class="btn btn-secondary" onclick="location.href='unidades.php'">Voltar</button>
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
                    <form method="POST" onsubmit="submitForm(event, this)">
                        <input type="hidden" name="idunidade" value="<?php echo $idunidade; ?>">
                        <input type="hidden" name="acao" value="desassociar">
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
                    <form method="POST" onsubmit="submitForm(event, this)">
                        <input type="hidden" name="idunidade" value="<?php echo $idunidade; ?>">
                        <input type="hidden" name="acao" value="associar">
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
                            <p>Nenhum módulo disponível para associação.</p>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>

        <!-- Gerenciar Preceptores -->
        <div class="container-card">
            <!-- Preceptores Não Associados -->
            <div class="card">
                <div class="card-header">
                    <h5>Preceptores Não Associados</h5>
                    <?php
                    // Resetar o ponteiro do resultado para reutilizá-lo
                    mysqli_data_seek($resPreceptores, 0);
                    
                    $preceptoresNaoAssociados = [];
                    while ($preceptor = $resPreceptores->fetch_assoc()) {
                        if (is_null($preceptor['idunidade'])) {
                            $preceptoresNaoAssociados[] = $preceptor;
                        }
                    }
                    if (count($preceptoresNaoAssociados) > 0): ?>
                        <div class="form-check">
                            <input type="checkbox" id="selectAllNaoAssociadosPreceptores" class="form-check-input" onchange="toggleCheckboxes(this, 'preceptor-nao-associado')">
                            <label for="selectAllNaoAssociadosPreceptores" class="form-check-label">Selecionar Todos</label>
                        </div>
                    </div>
                    <div class="card-body">
                        <form method="POST" onsubmit="submitForm(event, this)">
                            <input type="hidden" name="idunidade" value="<?php echo $idunidade; ?>">
                            <input type="hidden" name="acao" value="associar">
                            <?php foreach ($preceptoresNaoAssociados as $preceptor): ?>
                                <div class="form-check">
                                    <input type="checkbox" name="preceptores[]" value="<?php echo $preceptor['idusuario']; ?>" class="form-check-input preceptor-nao-associado">
                                    <label class="form-check-label"><?php echo htmlspecialchars($preceptor['nome']); ?></label>
                                </div>
                            <?php endforeach; ?>
                            <button type="submit" class="btn btn-primary mt-3">Associar Preceptores</button>
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
                            <input type="checkbox" id="selectAllAssociadosPreceptores" class="form-check-input" onchange="toggleCheckboxes(this, 'preceptor-associado')">
                            <label for="selectAllAssociadosPreceptores" class="form-check-label">Selecionar Todos</label>
                        </div>
                    </div>
                    <div class="card-body">
                        <form method="POST" onsubmit="submitForm(event, this)">
                            <input type="hidden" name="idunidade" value="<?php echo $idunidade; ?>">
                            <input type="hidden" name="acao" value="desassociar">
                            <?php mysqli_data_seek($resPreceptores, 0); // Reset result set pointer ?>
                            <?php if ($resPreceptores->num_rows > 0): ?>
                                <?php while ($preceptor = $resPreceptores->fetch_assoc()): ?>
                                    <?php if (!is_null($preceptor['idunidade']) && $preceptor['idunidade'] == $idunidade): ?>
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
        </div>
    </div>
    <footer>
        <div class="card footer-home rounded-0">
            <div class="card-body">
            </div>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>