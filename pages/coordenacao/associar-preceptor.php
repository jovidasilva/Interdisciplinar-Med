<?php
session_start();
include('../../cfg/config.php');

if (empty($_SESSION["login"])) {
    echo "<script>location.href='../../index.php';</script>";
    exit();
}

// Verificar ID do preceptor via GET ou POST
$idPreceptor = $_GET['id'] ?? $_POST['idPreceptor'] ?? null;

if (!$idPreceptor) {
    echo "ID do preceptor não informado.";
    exit();
}

// Consultar a unidade associada
$sqlUnidadeAssociada = "SELECT idunidade FROM preceptores_unidades WHERE idusuario = ?";
$stmtUnidade = $conn->prepare($sqlUnidadeAssociada);
$stmtUnidade->bind_param("i", $idPreceptor);
$stmtUnidade->execute();
$resUnidadeAssociada = $stmtUnidade->get_result();
$unidadeAssociada = $resUnidadeAssociada->fetch_object();
$idUnidadeAtual = $unidadeAssociada->idunidade ?? null;

// Consultar os módulos associados
$sqlModulosAssociados = "SELECT m.idmodulo, m.nome_modulo FROM modulos m JOIN preceptores_modulos pm ON m.idmodulo = pm.idmodulo WHERE pm.idusuario = ?";
$stmtModulosAssociados = $conn->prepare($sqlModulosAssociados);
$stmtModulosAssociados->bind_param("i", $idPreceptor);
$stmtModulosAssociados->execute();
$modulosAssociados = $stmtModulosAssociados->get_result();

// Consultar os módulos não associados que pertencem à unidade atual
$modulosNaoAssociados = [];
if ($idUnidadeAtual) {
    $sqlModulosNaoAssociados = "SELECT m.idmodulo, m.nome_modulo FROM modulos m JOIN unidades_modulos um ON m.idmodulo = um.idmodulo WHERE um.idunidade = ? AND m.idmodulo NOT IN (SELECT idmodulo FROM preceptores_modulos WHERE idusuario = ?)";
    $stmtModulosNaoAssociados = $conn->prepare($sqlModulosNaoAssociados);
    $stmtModulosNaoAssociados->bind_param("ii", $idUnidadeAtual, $idPreceptor);
    $stmtModulosNaoAssociados->execute();
    $modulosNaoAssociados = $stmtModulosNaoAssociados->get_result();
}

// Verificar se foi enviado o formulário de associação, desassociação ou dissociação de unidade
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $modulosSelecionados = $_POST['modulos'] ?? [];
    $acao = $_POST['acao'] ?? '';
    $idUnidade = $_POST['idunidade'] ?? null;

    if ($acao === 'associar' && !empty($modulosSelecionados)) {
        // Associar módulos
        foreach ($modulosSelecionados as $idModulo) {
            $stmt = $conn->prepare("INSERT INTO preceptores_modulos (idusuario, idmodulo) VALUES (?, ?)");
            $stmt->bind_param("ii", $idPreceptor, $idModulo);
            $stmt->execute();
        }
        echo "<script>alert('Módulos associados com sucesso!'); location.href='associar-preceptor.php?id=$idPreceptor';</script>";
        exit();
    }

    if ($acao === 'desassociar' && !empty($modulosSelecionados)) {
        // Desassociar módulos
        foreach ($modulosSelecionados as $idModulo) {
            $stmt = $conn->prepare("DELETE FROM preceptores_modulos WHERE idusuario = ? AND idmodulo = ?");
            $stmt->bind_param("ii", $idPreceptor, $idModulo);
            $stmt->execute();
        }
        echo "<script>alert('Módulos desassociados com sucesso!'); location.href='associar-preceptor.php?id=$idPreceptor';</script>";
        exit();
    }

    if ($acao === 'associar-unidade' && $idUnidade) {
        // Alterar a unidade e remover módulos associados
        $sqlVerifica = "SELECT * FROM preceptores_unidades WHERE idusuario = ?";
        $stmtVerifica = $conn->prepare($sqlVerifica);
        $stmtVerifica->bind_param("i", $idPreceptor);
        $stmtVerifica->execute();
        $resVerifica = $stmtVerifica->get_result();

        if ($resVerifica->num_rows > 0) {
            $stmtUpdate = $conn->prepare("UPDATE preceptores_unidades SET idunidade = ? WHERE idusuario = ?");
            $stmtUpdate->bind_param("ii", $idUnidade, $idPreceptor);
            $stmtUpdate->execute();
        } else {
            $stmtInsert = $conn->prepare("INSERT INTO preceptores_unidades (idusuario, idunidade) VALUES (?, ?)");
            $stmtInsert->bind_param("ii", $idPreceptor, $idUnidade);
            $stmtInsert->execute();
        }

        // Remover todos os módulos associados
        $stmtRemoveModulos = $conn->prepare("DELETE FROM preceptores_modulos WHERE idusuario = ?");
        $stmtRemoveModulos->bind_param("i", $idPreceptor);
        $stmtRemoveModulos->execute();

        echo "<script>alert('Unidade alterada. Todos os módulos foram desassociados.'); location.href='associar-preceptor.php?id=$idPreceptor';</script>";
        exit();
    }

    if ($acao === 'dissociar') {
        // Remover todos os módulos associados ao preceptor
        $sqlRemoveModulos = "DELETE FROM preceptores_modulos WHERE idusuario = ?";
        $stmtModulos = $conn->prepare($sqlRemoveModulos);
        $stmtModulos->bind_param("i", $idPreceptor);
        $stmtModulos->execute();

        // Remover a unidade associada ao preceptor
        $sqlRemoveUnidade = "DELETE FROM preceptores_unidades WHERE idusuario = ?";
        $stmtUnidade = $conn->prepare($sqlRemoveUnidade);
        $stmtUnidade->bind_param("i", $idPreceptor);
        $stmtUnidade->execute();

        echo "<script>alert('Unidade e todos os módulos desassociados com sucesso!'); location.href='listar-preceptor.php';</script>";
        exit();
    }

    echo "<script>alert('Nenhum módulo selecionado!'); location.href='associar-preceptor.php?id=$idPreceptor';</script>";
    exit();
}

// Buscar unidades disponíveis
$sqlUnidades = "SELECT idunidade, nome_unidade FROM unidades";
$resUnidades = $conn->query($sqlUnidades);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Associar Módulos ao Preceptor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../css/style.css">
    <script>
        function toggleCheckboxes(selectAllCheckbox, checkboxClass) {
            const checkboxes = document.querySelectorAll(`.${checkboxClass}`);
            checkboxes.forEach(checkbox => checkbox.checked = selectAllCheckbox.checked);
        }
    </script>
</head>
<body>
    <header>
    <?php include('../../includes/navbar.php'); ?>
    <?php include('../../includes/menu-lateral-coordenacao.php'); ?>
    </header>
    <main>
        <div class="container mt-3">
            <h3>Gerenciar Unidade e Módulos para o Preceptor</h3>
            <button class="btn btn-secondary mb-3" onclick="location.href='listar-preceptor.php'">Voltar</button>

            <!-- Formulário para associar unidade -->
            <div class="card mb-4">
    <div class="card-header">
        <h5>Associar Unidade</h5>
    </div>
    <div class="card-body">
        <form method="POST">
            <input type="hidden" name="acao" value="associar-unidade">
            <input type="hidden" name="idPreceptor" value="<?php echo $idPreceptor; ?>">
            <div class="mb-3">
                <label for="idunidade" class="form-label">Unidade</label>
                <select name="idunidade" id="idunidade" class="form-select" required
                    <?php if ($idUnidadeAtual): ?>
                        onchange="return confirm('Alterar a unidade irá desassociar todos os módulos atuais. Deseja continuar?')"
                    <?php endif; ?>>
                    <option value="">Selecione uma unidade</option>
                    <?php while ($unidade = $resUnidades->fetch_assoc()): ?>
                        <option value="<?php echo $unidade['idunidade']; ?>" <?php echo $unidade['idunidade'] == $idUnidadeAtual ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($unidade['nome_unidade']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Associar Unidade</button>
        </form>
        <?php if ($idUnidadeAtual): ?>
            <form method="POST" style="display: inline;">
                <input type="hidden" name="acao" value="dissociar">
                <input type="hidden" name="idPreceptor" value="<?php echo $idPreceptor; ?>">
                <button type="submit" class="btn btn-danger mt-3" onclick="return confirm('Deseja realmente dissociar esta unidade e todos os módulos?');">Dissociar Unidade e Módulos</button>
            </form>
        <?php endif; ?>
    </div>
</div>
            <div class="d-flex gap-4">
                <!-- Módulos Associados -->
                <div class="card flex-fill">
                    <div class="card-header">
                        <h5>Módulos Associados</h5>
                        <div class="form-check">
                            <input type="checkbox" id="selectAllAssociados" class="form-check-input" onchange="toggleCheckboxes(this,  'modulo-associado')">
                            <label for="selectAllAssociados" class="form-check-label">Selecionar Todos</label>
                        </div>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="acao" value="desassociar">
                            <?php if ($modulosAssociados->num_rows > 0): ?>
                                <?php while ($modulo = $modulosAssociados->fetch_assoc()): ?>
                                    <div class="form-check">
                                        <input type="checkbox" name="modulos[]" value="<?php echo $modulo['idmodulo']; ?>" class="form-check-input modulo-associado">
                                        <label class="form-check-label"><?php echo htmlspecialchars($modulo['nome_modulo']); ?></label>
                                    </div>
                                <?php endwhile; ?>
                                <button type="submit" class="btn btn-danger mt-3">Desassociar Módulos</button>
                            <?php else: ?>
                                <p>Nenhum módulo associado.</p>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

                <!-- Módulos Não Associados -->
                <div class="card flex-fill">
    <div class="card-header">
        <h5>Módulos Não Associados</h5>
        <div class="form-check">
            <input type="checkbox" id="selectAllNaoAssociados" class="form-check-input" onchange="toggleCheckboxes(this, 'modulo-nao-associado')">
            <label for="selectAllNaoAssociados" class="form-check-label">Selecionar Todos</label>
        </div>
    </div>
    <div class="card-body">
        <form method="POST">
            <input type="hidden" name="acao" value="associar">
            <?php if (is_null($idUnidadeAtual)): ?>
                <p>O preceptor não está associado a uma unidade ainda.</p>
            <?php elseif ($modulosNaoAssociados->num_rows > 0): ?>
                <?php while ($modulo = $modulosNaoAssociados->fetch_assoc()): ?>
                    <div class="form-check">
                        <input type="checkbox" name="modulos[]" value="<?php echo $modulo['idmodulo'];?>" class="form-check-input modulo-nao-associado">
                        <label class="form-check-label"><?php echo htmlspecialchars($modulo['nome_modulo']); ?></label>
                    </div>
                <?php endwhile; ?>
                <button type="submit" class="btn btn-primary mt-3">Associar Módulos</button>
            <?php else: ?>
                <p>Nenhum módulo disponível.</p>
            <?php endif; ?>
        </form>
    </div>
</div>

