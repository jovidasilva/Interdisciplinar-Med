<?php
session_start();
include('../../../cfg/config.php');

if (empty($_SESSION["login"])) {
    echo "<script>location.href='../../index.php';</script>";
    exit();
}

$horarioData = null;
if (isset($_GET['id'])) {
    $idHorario = $_GET['id'];
    $query = "SELECT h.*, d.nome_departamento, m.nome_modulo, sg.nome_subgrupo, u.nome AS preceptor_nome 
              FROM horarios h
              LEFT JOIN departamentos d ON h.iddepartamento = d.iddepartamento
              LEFT JOIN modulos m ON h.idmodulo = m.idmodulo
              LEFT JOIN subgrupos sg ON h.idsubgrupo = sg.idsubgrupo
              LEFT JOIN usuarios u ON h.idpreceptor = u.idusuario
              WHERE h.idhorario = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $idHorario);
    $stmt->execute();
    $result = $stmt->get_result();
    $horarioData = $result->fetch_assoc();
}

if (isset($_GET['action'])) {
    $action = $_GET['action'];

    switch ($action) {
        case 'getDepartamentos':
            $idUnidade = $_GET['idunidade'];
            $query = "SELECT DISTINCT iddepartamento, nome_departamento FROM departamentos WHERE idunidade = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $idUnidade);
            if (!$stmt->execute()) {
                echo json_encode(['error' => $stmt->error]);
                exit();
            }
            $result = $stmt->get_result();

            $departamentos = array();
            while ($row = $result->fetch_assoc()) {
                $departamentos[] = $row;
            }

            echo json_encode($departamentos);
            break;

        case 'getModulos':
            $idDepartamento = $_GET['iddepartamento'];
            $query = "SELECT DISTINCT m.idmodulo, m.nome_modulo 
                      FROM modulos m 
                      JOIN modulos_departamentos md ON m.idmodulo = md.idmodulo 
                      WHERE md.iddepartamento = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $idDepartamento);
            if (!$stmt->execute()) {
                echo json_encode(['error' => $stmt->error]);
                exit();
            }
            $result = $stmt->get_result();

            $modulos = array();
            while ($row = $result->fetch_assoc()) {
                $modulos[] = $row;
            }

            echo json_encode($modulos);
            break;

        case 'getSubgrupos':
            $idModulo = $_GET['idmodulo'];
            $query = "SELECT DISTINCT sg.idsubgrupo, sg.nome_subgrupo 
                      FROM subgrupos sg
                      JOIN rodizios_subgrupos rs ON sg.idsubgrupo = rs.idsubgrupo
                      JOIN rodizios r ON rs.idrodizio = r.idrodizio
                      WHERE r.idmodulo = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $idModulo);
            if (!$stmt->execute()) {
                echo json_encode(['error' => $stmt->error]);
                exit();
            }
            $result = $stmt->get_result();

            $subgrupos = array();
            while ($row = $result->fetch_assoc()) {
                $subgrupos[] = $row;
            }

            echo json_encode($subgrupos);
            break;

        case 'getPreceptores':
            $idModulo = $_GET['idmodulo'];
            $idUnidade = $_GET['idunidade'];
            $query = "SELECT DISTINCT u.idusuario, u.nome 
                      FROM usuarios u 
                      JOIN preceptores_modulos pm ON u.idusuario = pm.idusuario 
                      JOIN preceptores_unidades pu ON u.idusuario = pu.idusuario 
                      WHERE pm.idmodulo = ? AND pu.idunidade = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ii", $idModulo, $idUnidade);
            if (!$stmt->execute()) {
                echo json_encode(['error' => $stmt->error]);
                exit();
            }
            $result = $stmt->get_result();

            $preceptores = array();
            while ($row = $result->fetch_assoc()) {
                $preceptores[] = $row;
            }

            echo json_encode($preceptores);
            break;
    }
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idUnidade = $_POST['idUnidade'];
    $idModulo = $_POST['idModulo'];
    $subgrupo = $_POST['subgrupo'];
    $idPreceptor = $_POST['idPreceptor'];
    $horaInicio = $_POST['horaInicio'];
    $horaFim = $_POST['horaFim'];
    $diaSemana = $_POST['diaSemana'];
    $idDepartamento = $_POST['idDepartamento'];

    // Verificar se um horário semelhante já existe
    $query = "SELECT idhorario FROM horarios 
              WHERE idunidade = ? AND idmodulo = ? AND dia_semana = ? AND hora_inicio = ? AND hora_fim = ? AND idhorario <> ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iisssi", $idUnidade, $idModulo, $diaSemana, $horaInicio, $horaFim, $idHorario);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('Um horário semelhante já existe.'); history.back();</script>";
        exit();
    }

    if (isset($idHorario)) {
        // Atualizar o horário existente
        $sql = "UPDATE horarios 
                SET idunidade = ?, idmodulo = ?, idpreceptor = ?, dia_semana = ?, hora_inicio = ?, hora_fim = ?, idsubgrupo = ?, iddepartamento = ? 
                WHERE idhorario = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiisssiii", $idUnidade, $idModulo, $idPreceptor, $diaSemana, $horaInicio, $horaFim, $subgrupo, $idDepartamento, $idHorario);
    } else {
        // Inserir um novo horário
        $sql = "INSERT INTO horarios (idunidade, idmodulo, idpreceptor, dia_semana, hora_inicio, hora_fim, idsubgrupo, iddepartamento) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiisssii", $idUnidade, $idModulo, $idPreceptor, $diaSemana, $horaInicio, $horaFim, $subgrupo, $idDepartamento);
    }

    if ($stmt->execute()) {
        echo "<script>alert('Horário atualizado com sucesso!'); location.href='horarios.php';</script>";
    } else {
        echo "<script>alert('Erro ao atualizar horário: " . $stmt->error . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preencher Horário</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../../css/style.css">
</head>
<body>
    <header>
        <?php include('../../../includes/navbar.php'); ?>
        <?php include('../../../includes/menu-lateral-coordenacao.php'); ?>
    </header>
    <main class="container mt-4">
        <h2>Preencher Horário</h2>
        <form method="POST">
            <div class="mb-3">
                <label for="idUnidade" class="form-label">Unidade</label>
                <select name="idUnidade" id="idUnidade" class="form-select" required>
                    <option value="">Selecione a Unidade</option>
                    <?php
                    // Carregar unidades da base de dados
                    $query = "SELECT DISTINCT idunidade, nome_unidade FROM unidades";
                    $result = $conn->query($query);
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<option value='" . $row['idunidade'] . "'>" . $row['nome_unidade'] . "</option>";
                        }
                    }
                    ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="idDepartamento" class="form-label">Departamento</label>
                <select name="idDepartamento" id="idDepartamento" class="form-select" required>
                    <option value="">Selecione o Departamento</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="idModulo" class="form-label">Módulo</label>
                <select name="idModulo" id="idModulo" class="form-select" required>
                    <option value="">Selecione o Módulo</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="subgrupo" class="form-label">Subgrupo</label>
                <select name="subgrupo" id="subgrupo" class="form-select" required>
                    <option value="">Selecione o Subgrupo</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="idPreceptor" class="form-label">Preceptor</label>
                <select name="idPreceptor" id="idPreceptor" class="form-select" required>
                    <option value="">Selecione o Preceptor</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="horaInicio" class="form-label">Hora de Início</label>
                <input type="time" name="horaInicio" id="horaInicio" class="form-control" required value="<?php echo $horarioData['hora_inicio'] ?? ''; ?>">
            </div>
            <div class="mb-3">
                <label for="horaFim" class="form-label">Hora de Fim</label>
                <input type="time" name="horaFim" id="horaFim" class="form-control" required value="<?php echo $horarioData['hora_fim'] ?? ''; ?>">
            </div>
            <div class="mb-3">
                <label for="diaSemana" class="form-label">Dia da Semana</label>
                <select name="diaSemana" id="diaSemana" class="form-select" required>
                    <option value="Segunda" <?php echo (isset($horarioData['dia_semana']) && $horarioData['dia_semana'] == 'Segunda') ? 'selected' : ''; ?>>Segunda-feira</option>
                    <option value="Terca" <?php echo (isset($horarioData['dia_semana']) && $horarioData['dia_semana'] == 'Terca') ? 'selected' : ''; ?>>Terça-feira</option>
                    <option value="Quarta" <?php echo (isset($horarioData['dia_semana']) && $horarioData['dia_semana'] == 'Quarta') ? 'selected' : ''; ?>>Quarta-feira</option>
                    <option value="Quinta" <?php echo (isset($horarioData['dia_semana']) && $horarioData['dia_semana'] == 'Quinta') ? 'selected' : ''; ?>>Quinta-feira</option>
                    <option value="Sexta" <?php echo (isset($horarioData['dia_semana']) && $horarioData['dia_semana'] == 'Sexta') ? 'selected' : ''; ?>>Sexta-feira</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Salvar Horário</button>
            <a href="horarios.php" class="btn btn-secondary">Voltar</a>
        </form>
    </main>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
    $('#idUnidade').change(function() {
        var idUnidade = $(this).val();
        console.log('Unidade selecionada: ' + idUnidade); // Log para depuração
        if (idUnidade) {
            $.ajax({
                url: 'preencher-horario.php',
                type: 'GET',
                data: { action: 'getDepartamentos', idunidade: idUnidade },
                success: function(data) {
                    console.log('Resposta AJAX - Departamentos recebidos: ', data); // Log para depuração
                    var departamentos = JSON.parse(data);
                    console.log('Departamentos após parse JSON: ', departamentos); // Log para depuração

                    // Limpar o campo de seleção de departamentos antes de adicionar novos
                    $('#idDepartamento').html('<option value="">Selecione o Departamento</option>');

                    $.each(departamentos, function(index, departamento) {
                        console.log('Adicionando departamento: ', departamento); // Log para depuração
                        $('#idDepartamento').append('<option value="'+departamento.iddepartamento+'">'+departamento.nome_departamento+'</option>');
                    });
                }
            });
        }
    });

    $('#idDepartamento').change(function() {
        var idDepartamento = $(this).val();
        console.log('Departamento selecionado: ' + idDepartamento); // Log para depuração
        if (idDepartamento) {
            $.ajax({
                url: 'preencher-horario.php',
                type: 'GET',
                data: { action: 'getModulos', iddepartamento: idDepartamento },
                success: function(data) {
                    console.log('Resposta AJAX - Módulos recebidos: ', data); // Log para depuração
                    var modulos = JSON.parse(data);
                    console.log('Módulos após parse JSON: ', modulos); // Log para depuração

                    // Limpar o campo de seleção de módulos antes de adicionar novos
                    $('#idModulo').html('<option value="">Selecione o Módulo</option>');

                    $.each(modulos, function(index, modulo) {
                        console.log('Adicionando módulo: ', modulo); // Log para depuração
                        $('#idModulo').append('<option value="'+modulo.idmodulo+'">'+modulo.nome_modulo+'</option>');
                    });
                }
            });
        }
    });

    $('#idModulo').change(function() {
        var idModulo = $(this).val();
        console.log('Módulo selecionado: ' + idModulo); // Log para depuração
        if (idModulo) {
            $.ajax({
                url: 'preencher-horario.php',
                type: 'GET',
                data: { action: 'getSubgrupos', idmodulo: idModulo },
                success: function(data) {
                    console.log('Resposta AJAX - Subgrupos recebidos: ', data); // Log para depuração
                    var subgrupos = JSON.parse(data);
                    console.log('Subgrupos após parse JSON: ', subgrupos); // Log para depuração

                    // Limpar o campo de seleção de subgrupos antes de adicionar novos
                    $('#subgrupo').html('<option value="">Selecione o Subgrupo</option>');

                    $.each(subgrupos, function(index, subgrupo) {
                        console.log('Adicionando subgrupo: ', subgrupo); // Log para depuração
                        $('#subgrupo').append('<option value="'+subgrupo.idsubgrupo+'">'+subgrupo.nome_subgrupo+'</option>');
                    });
                }
            });

            var idUnidade = $('#idUnidade').val();
            $.ajax({
                url: 'preencher-horario.php',
                type: 'GET',
                data: { action: 'getPreceptores', idunidade: idUnidade, idmodulo: idModulo },
                success: function(data) {
                    console.log('Resposta AJAX - Preceptores recebidos: ', data); // Log para depuração
                    var preceptores = JSON.parse(data);
                    console.log('Preceptores após parse JSON: ', preceptores); // Log para depuração

                    // Limpar o campo de seleção de preceptores antes de adicionar novos
                    $('#idPreceptor').html('<option value="">Selecione o Preceptor</option>');

                    $.each(preceptores, function(index, preceptor) {
                        console.log('Adicionando preceptor: ', preceptor); // Log para depuração
                        $('#idPreceptor').append('<option value="'+preceptor.idusuario+'">'+preceptor.nome+'</option>');
                    });
                }
            });
        }
    });
});
    </script>
</body>
</html>