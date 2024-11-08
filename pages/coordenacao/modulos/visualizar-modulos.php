<?php
include('../../../cfg/config.php');

if (isset($_GET['idmodulo'])) {
    $idmodulo = intval($_GET['idmodulo']);

    $stmt = $conn->prepare("SELECT nome_modulo, periodo FROM modulos WHERE idmodulo = ?");
    $stmt->bind_param("i", $idmodulo);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $modulo = $result->fetch_assoc();
    } else {
        echo "Módulo não encontrado.";
        exit();
    }

    $semestreAtual = $modulo['periodo'];

    $alunosNaoAssociados = $conn->query("SELECT idusuario, nome FROM usuarios WHERE ativo = 1 AND tipo = 0 AND periodo = '$semestreAtual' AND idusuario NOT IN (SELECT idusuario FROM modulos_alunos WHERE idmodulo = $idmodulo)");
    $numAlunosNaoAssociados = $alunosNaoAssociados->num_rows;

    $alunosAssociados = $conn->query("SELECT u.idusuario, u.nome, u.registro FROM usuarios u JOIN modulos_alunos ma ON u.idusuario = ma.idusuario WHERE ma.idmodulo = $idmodulo");
    $numAlunosAssociados = $alunosAssociados->num_rows;
} else {
    echo "ID do módulo não informado ou inválido.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Visualizar Módulo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../../css/style.css">
    <style>
        body{
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
            <?php echo htmlspecialchars($modulo['nome_modulo']); ?> (Período: <?php echo htmlspecialchars($modulo['periodo']); ?>)
            <button onclick="location.href='?page=upload-alunos&idmodulo=<?php echo $idmodulo; ?>'" class="btn btn-secondary">Enviar lista de alunos</button>
            <button class="btn btn-secondary" onclick="location.href='?page=listar-modulos'">Voltar</button>
        </h3>
        <div class="container-card">
            <form method="POST" action="desassociar-alunos.php?idmodulo=<?php echo $idmodulo; ?>">
                <div class="card">
                    <div class="card-header">
                        <h5>Alunos Matriculados no Módulo</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($numAlunosAssociados > 0): ?>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" id="selectAll" onclick="toggleCheckboxes(this, 'aluno-associado')"> Selecionar Todos</th>
                                        <th>Nome</th>
                                        <th>RA</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($aluno = $alunosAssociados->fetch_assoc()): ?>
                                        <tr>
                                            <td><input type="checkbox" class="aluno-associado" name="alunosDesassociar[]" value="<?php echo htmlspecialchars($aluno['idusuario']); ?>"></td>
                                            <td><?php echo htmlspecialchars($aluno['nome']); ?></td>
                                            <td><?php echo htmlspecialchars($aluno['registro']); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                            <button type="submit" class="btn btn-primary mt-3">Desassociar Aluno(s)</button>
                        <?php else: ?>
                            <p>Nenhum aluno encontrado.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </form>

            <div class="card">
                <div class="card-header">
                    <h5>Alunos Não Matriculados e Disponíveis</h5>
                    <div class="form-check">
                        <input type="checkbox" id="selectAllNaoAssociados" class="form-check-input" onchange="toggleCheckboxes(this, 'aluno-nao-associado')">
                        <label for="selectAllNaoAssociados" class="form-check-label">Selecionar Todos</label>
                    </div>
                </div>
                <div class="card-body">
                    <form method="POST" action="associar-alunos.php?idmodulo=<?php echo $idmodulo; ?>">
                        <?php if ($numAlunosNaoAssociados > 0): ?>
                            <?php while ($aluno = $alunosNaoAssociados->fetch_assoc()): ?>
                                <div class="form-check">
                                    <input type="checkbox" name="alunos[]" value="<?php echo $aluno['idusuario']; ?>" class="form-check-input aluno-nao-associado" id="aluno-<?php echo $aluno['idusuario']; ?>">
                                    <label class="form-check-label" for="aluno-<?php echo $aluno['idusuario']; ?>">
                                        <?php echo htmlspecialchars($aluno['nome']); ?>
                                    </label>
                                </div>
                            <?php endwhile; ?>
                            <button type="submit" class="btn btn-primary mt-3">Associar Alunos</button>
                        <?php else: ?>
                            <p>Nenhum aluno disponível.</p>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

</html>