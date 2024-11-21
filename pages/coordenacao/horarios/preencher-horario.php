<?php
include('../../../cfg/config.php');
include('acoes-horario.php');

// Verifica se o usuário está logado
checkLogin();

$horarioData = null;
// Verifica se o ID do horário foi passado na URL
if (isset($_GET['id'])) {
    $idHorario = $_GET['id'];
    $horarioData = getHorarioData($conn, $idHorario);
}

if (isset($_GET['action'])) {
    $action = $_GET['action'];

    switch ($action) {
        case 'getDepartamentos':
            $idUnidade = $_GET['idunidade'];
            echo getDepartamentos($conn, $idUnidade);
            break;

        case 'getModulos':
            $idDepartamento = $_GET['iddepartamento'];
            echo getModulos($conn, $idDepartamento);
            break;

        case 'getSubgrupos':
            $idModulo = $_GET['idmodulo'];
            echo getSubgrupos($conn, $idModulo);
            break;

        case 'getPreceptores':
            $idModulo = $_GET['idmodulo'];
            $idUnidade = $_GET['idunidade'];
            echo getPreceptores($conn, $idModulo, $idUnidade);
            break;
    }
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = saveOrUpdateHorario($conn, $_POST);
    if ($result['success']) {
        echo "<script>alert('" . $result['message'] . "'); location.href='horarios.php';</script>";
    } else {
        echo "<script>alert('" . $result['message'] . "');</script>";
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
        <h2><?php echo isset($horarioData['idhorario']) ? 'Editar Horário' : 'Preencher Horário'; ?></h2>
        <form method="POST" action="">
            <!-- Campo Oculto para o ID do Horário -->
            <input type="hidden" name="idHorario" value="<?php echo $horarioData['idhorario'] ?? ''; ?>">

            <div class="mb-3">
                <label for="idUnidade" class="form-label">Unidade</label>
                <select name="idUnidade" id="idUnidade" class="form-select" required>
                    <option value="">Selecione a Unidade</option>
                    <?php
                    $unidades = getUnidades($conn);
                    foreach ($unidades as $unidade) {
                        $selected = ($horarioData['idunidade'] ?? '') == $unidade['idunidade'] ? 'selected' : '';
                        echo "<option value='" . $unidade['idunidade'] . "' $selected>" . $unidade['nome_unidade'] . "</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="idDepartamento" class="form-label">Departamento</label>
                <select name="idDepartamento" id="idDepartamento" class="form-select" required>
                    <option value="">Selecione o Departamento</option>
                    <?php
                    if (isset($horarioData['idunidade'])) {
                        $departamentos = getDepartamentosByUnidade($conn, $horarioData['idunidade']);
                        foreach ($departamentos as $departamento) {
                            $selected = ($horarioData['iddepartamento'] ?? '') == $departamento['iddepartamento'] ? 'selected' : '';
                            echo "<option value='" . $departamento['iddepartamento'] . "' $selected>" . $departamento['nome_departamento'] . "</option>";
                        }
                    }
                    ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="idModulo" class="form-label">Módulo</label>
                <select name="idModulo" id="idModulo" class="form-select" required>
                    <option value="">Selecione o Módulo</option>
                    <?php
                    if (isset($horarioData['iddepartamento'])) {
                        $modulos = getModulosByDepartamento($conn, $horarioData['iddepartamento']);
                        foreach ($modulos as $modulo) {
                            $selected = ($horarioData['idmodulo'] ?? '') == $modulo['idmodulo'] ? 'selected' : '';
                            echo "<option value='" . $modulo['idmodulo'] . "' $selected>" . $modulo['nome_modulo'] . "</option>";
                        }
                    }
                    ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="subgrupo" class="form-label">Subgrupo</label>
                <select name="subgrupo" id="subgrupo" class="form-select" required>
                    <option value="">Selecione o Subgrupo</option>
                    <?php
                    if (isset($horarioData['idmodulo'])) {
                        $subgrupos = getSubgruposByModulo($conn, $horarioData['idmodulo']);
                        foreach ($subgrupos as $subgrupo) {
                            $selected = ($horarioData['idsubgrupo'] ?? '') == $subgrupo['idsubgrupo'] ? 'selected' : '';
                            echo "<option value='" . $subgrupo['idsubgrupo'] . "' $selected>" . $subgrupo['nome_subgrupo'] . "</option>";
                        }
                    }
                    ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="idPreceptor" class="form-label">Preceptor</label>
                <select name="idPreceptor" id="idPreceptor" class="form-select" required>
                    <option value="">Selecione o Preceptor</option>
                    <?php
                    if (isset($horarioData['idmodulo']) && isset($horarioData['idunidade'])) {
                        $preceptores = getPreceptoresByModuloUnidade($conn, $horarioData['idmodulo'], $horarioData['idunidade']);
                        foreach ($preceptores as $preceptor) {
                            $selected = ($horarioData['idpreceptor'] ?? '') == $preceptor['idusuario'] ? 'selected' : '';
                            echo "<option value='" . $preceptor['idusuario'] . "' $selected>" . $preceptor['nome'] . "</option>";
                        }
                    }
                    ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="horaInicio" class="form-label">Hora de Início</label>
                <input type="time" name="horaInicio" id="horaInicio" class="form-control" required value="<?php echo htmlspecialchars($horarioData['hora_inicio'] ?? ''); ?>">
            </div>

            <div class="mb-3">
                <label for="horaFim" class="form-label">Hora de Fim</label>
                <input type="time" name="horaFim" id="horaFim" class="form-control" required value="<?php echo htmlspecialchars($horarioData['hora_fim'] ?? ''); ?>">
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
                if (idUnidade) {
                    $.ajax({
                        url: 'preencher-horario.php',
                        type: 'GET',
                        data: { action: 'getDepartamentos', idunidade: idUnidade },
                        success: function(data) {
                            var departamentos = JSON.parse(data);
                            // Limpar o campo de seleção de departamentos antes de adicionar novos
                            $('#idDepartamento').html('<option value="">Selecione o Departamento</option>')
                            $.each(departamentos, function(index, departamento) {
                                $('#idDepartamento').append('<option value="'+departamento.iddepartamento+'">'+departamento.nome_departamento+'</option>');
                            });
                        }
                    });
                }
            });

            $('#idDepartamento').change(function() {
                var idDepartamento = $(this).val();
                if (idDepartamento) {
                    $.ajax({
                        url: 'preencher-horario.php',
                        type: 'GET',
                        data: { action: 'getModulos', iddepartamento: idDepartamento },
                        success: function(data) {
                            var modulos = JSON.parse(data);
                            // Limpar o campo de seleção de módulos antes de adicionar novos
                            $('#idModulo').html('<option value="">Selecione o Módulo</option>');

                            $.each(modulos, function(index, modulo) {
                                $('#idModulo').append('<option value="'+modulo.idmodulo+'">'+modulo.nome_modulo+'</option>');
                            });
                        }
                    });
                }
            });

            $('#idModulo').change(function() {
                var idModulo = $(this).val();
                if (idModulo) {
                    $.ajax({
                        url: 'preencher-horario.php',
                        type: 'GET',
                        data: { action: 'getSubgrupos', idmodulo: idModulo },
                        success: function(data) {
                            var subgrupos = JSON.parse(data);
                            // Limpar o campo de seleção de subgrupos antes de adicionar novos
                            $('#subgrupo').html('<option value="">Selecione o Subgrupo</option>');

                            $.each(subgrupos, function(index, subgrupo) {
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
                            var preceptores = JSON.parse(data);
                            // Limpar o campo de seleção de preceptores antes de adicionar novos
                            $('#idPreceptor').html('<option value="">Selecione o Preceptor</option>');

                            $.each(preceptores, function(index, preceptor) {
                                $('#idPreceptor').append('<option value="'+preceptor.idusuario+'">'+preceptor.nome+'</option>');
                            });
                        }
                    });
                }
            });

            // Preencher os campos no carregamento da página se estivermos editando um horário
            <?php if (isset($horarioData)) { ?>
                // Disparar os eventos 'change' para preencher os campos relacionados
                $('#idUnidade').trigger('change');
                setTimeout(function() {
                    $('#idDepartamento').val('<?php echo $horarioData['iddepartamento']; ?>').trigger('change');
                    setTimeout(function() {
                        $('#idModulo').val('<?php echo $horarioData['idmodulo']; ?>').trigger('change');
                        setTimeout(function() {
                            $('#subgrupo').val('<?php echo $horarioData['idsubgrupo']; ?>');
                            $('#idPreceptor').val('<?php echo $horarioData['idpreceptor']; ?>');
                        }, 500);
                    }, 500);
                }, 500);
            <?php } ?>
        });
    </script>
</body>
</html>