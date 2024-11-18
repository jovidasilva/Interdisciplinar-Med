<?php
session_start();
include('../../../cfg/config.php');

if (empty($_SESSION["login"])) {
    echo "<script>location.href='../../index.php';</script>";
    exit();
}

// Capturando os filtros do formulário
$filterDia = $_GET['dia'] ?? '';
$filterUnidade = $_GET['unidade'] ?? '';
$filterSubgrupo = $_GET['subgrupo'] ?? '';
$filterModulo = $_GET['modulo'] ?? '';
$filterPreceptor = $_GET['preceptor'] ?? '';

// Carregar opções para os selects do banco de dados
$unidades = $conn->query("SELECT DISTINCT idunidade, nome_unidade FROM unidades");
$subgrupos = $conn->query("SELECT DISTINCT idsubgrupo, nome_subgrupo FROM subgrupos");
$modulos = $conn->query("SELECT DISTINCT idmodulo, nome_modulo FROM modulos");
$preceptores = $conn->query("SELECT DISTINCT idusuario, nome FROM usuarios WHERE tipo = 1");

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Horários</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../../css/style.css">
    <style>
        /* Adiciona uma margem superior ao formulário de filtros */
        form.row.mb-4 {
            margin-top: 30px; /* Ajuste o valor conforme necessário */
        }
    </style>
</head>
<body>
    <header>
        <?php include('../../../includes/navbar.php'); ?>
        <?php include('../../../includes/menu-lateral-coordenacao.php'); ?>
    </header>
    <main class="container mt-4">
        <h2>Horários</h2>
        
        <!-- Formulário de Filtro -->
        <form method="GET" class="row mb-4">
            <div class="col-md-2">
                <label for="dia" class="form-label">Dia da Semana</label>
                <select name="dia" id="dia" class="form-select">
                    <option value="">Todos</option>
                    <option value="Segunda" <?php if ($filterDia == 'Segunda') echo 'selected'; ?>>Segunda</option>
                    <option value="Terça" <?php if ($filterDia == 'Terça') echo 'selected'; ?>>Terça</option>
                    <option value="Quarta" <?php if ($filterDia == 'Quarta') echo 'selected'; ?>>Quarta</option>
                    <option value="Quinta" <?php if ($filterDia == 'Quinta') echo 'selected'; ?>>Quinta</option>
                    <option value="Sexta" <?php if ($filterDia == 'Sexta') echo 'selected'; ?>>Sexta</option>
                    <option value="Sábado" <?php if ($filterDia == 'Sábado') echo 'selected'; ?>>Sábado</option>
                    <option value="Domingo" <?php if ($filterDia == 'Domingo') echo 'selected'; ?>>Domingo</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="unidade" class="form-label">Unidade</label>
                <select name="unidade" id="unidade" class="form-select">
                    <option value="">Todas</option>
                    <?php while ($row = $unidades->fetch_assoc()): ?>
                        <option value="<?php echo $row['nome_unidade']; ?>" <?php if ($filterUnidade == $row['nome_unidade']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($row['nome_unidade']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label for="subgrupo" class="form-label">Subgrupo</label>
                <select name="subgrupo" id="subgrupo" class="form-select">
                    <option value="">Todos</option>
                    <?php while ($row = $subgrupos->fetch_assoc()): ?>
                        <option value="<?php echo $row['nome_subgrupo']; ?>" <?php if ($filterSubgrupo == $row['nome_subgrupo']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($row['nome_subgrupo']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label for="modulo" class="form-label">Módulo</label>
                <select name="modulo" id="modulo" class="form-select">
                    <option value="">Todos</option>
                    <?php while ($row = $modulos->fetch_assoc()): ?>
                        <option value="<?php echo $row['nome_modulo']; ?>" <?php if ($filterModulo == $row['nome_modulo']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($row['nome_modulo']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label for="preceptor" class="form-label">Preceptor</label>
                <select name="preceptor" id="preceptor" class="form-select">
                    <option value="">Todos</option>
                    <?php while ($row = $preceptores->fetch_assoc()): ?>
                        <option value="<?php echo $row['nome']; ?>" <?php if ($filterPreceptor == $row['nome']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($row['nome']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-2 align-self-end">
                <button type="submit" class="btn btn-primary">Filtrar</button>
            </div>
        </form>

        <?php
        // Construindo a consulta com os filtros
        $query = "SELECT h.idhorario, 
                         u.nome_unidade, 
                         d.nome_departamento, 
                         m.nome_modulo, 
                         p.nome AS preceptor_nome, 
                         h.dia_semana, 
                         h.hora_inicio, 
                         h.hora_fim, 
                         sg.nome_subgrupo 
                  FROM horarios h
                  JOIN unidades u ON h.idunidade = u.idunidade
                  JOIN modulos m ON h.idmodulo = m.idmodulo
                  JOIN departamentos d ON h.iddepartamento = d.iddepartamento
                  JOIN usuarios p ON h.idpreceptor = p.idusuario
                  JOIN subgrupos sg ON h.idsubgrupo = sg.idsubgrupo
                  WHERE 1=1";

        // Adicionando os filtros à consulta
        if ($filterDia) {
            $query .= " AND h.dia_semana = '" . mysqli_real_escape_string($conn, $filterDia) . "'";
        }
        if ($filterUnidade) {
            $query .= " AND u.nome_unidade LIKE '%" . mysqli_real_escape_string($conn, $filterUnidade) . "%'";
        }
        if ($filterSubgrupo) {
            $query .= " AND sg.nome_subgrupo LIKE '%" . mysqli_real_escape_string($conn, $filterSubgrupo) . "%'";
        }
        if ($filterModulo) {
            $query .= " AND m.nome_modulo LIKE '%" . mysqli_real_escape_string($conn, $filterModulo) . "%'";
        }
        if ($filterPreceptor) {
            $query .= " AND p.nome LIKE '%" . mysqli_real_escape_string($conn, $filterPreceptor) . "%'";
        }

        // Ordenação
        $query .= " ORDER BY FIELD(h.dia_semana, 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo'), h.hora_inicio";

        $result = mysqli_query($conn, $query);
        
        if (mysqli_num_rows($result) > 0) {
            echo '<table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Unidade</th>
                            <th>Departamento</th>
                            <th>Módulo</th>
                            <th>Preceptor</th>
                            <th>Dia da Semana</th>
                            <th>Hora de Início</th>
                            <th>Hora de Fim</th>
                            <th>Subgrupo</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>';
            
            while ($row = mysqli_fetch_assoc($result)) {
                echo '<tr>
                        <td>' . $row['nome_unidade'] . '</td>
                        <td>' . $row['nome_departamento'] . '</td>
                        <td>' . $row['nome_modulo'] . '</td>
                        <td>' . $row['preceptor_nome'] . '</td>
                        <td>' . $row['dia_semana'] . '</td>
                        <td>' . $row['hora_inicio'] . '</td>
                        <td>' . $row['hora_fim'] . '</td>
                        <td>' . $row['nome_subgrupo'] . '</td>
                        <td>
                            <a href="preencher-horario.php?id=' . $row['idhorario'] . '" class="btn btn-primary btn-sm">Editar</a>
                            <a href="excluir-horario.php?id=' . $row['idhorario'] . '" class="btn btn-danger btn-sm" onclick="return confirm(\'Tem certeza que deseja excluir este horário?\')">Excluir</a>
                        </td>
                      </tr>';
            }
            
            echo '</tbody></table>';
        } else {
            echo '<div class="d-flex flex-column align-items-center mt-4">
                    <p class="text-center mb-3">Não há horários correspondentes aos critérios de filtro.</p>
                  </div>';
        }
        ?>
        <div class="d-flex flex-column align-items-center mt-4">
            <p class="text-center mb-3" id="no-results-message" style="display: none;">Não há horários correspondentes aos critérios de filtro.</p>
            <a href="preencher-horario.php" class="btn btn-success">Adicionar Novo Horário</a>
        </div>
    </main>
    <footer>
        <div class="card footer-home rounded-0">
            <div class="card-body">
            </div>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
