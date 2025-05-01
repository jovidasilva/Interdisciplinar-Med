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
$modulos = $conn->query("SELECT DISTINCT m.idmodulo, m.nome_modulo FROM modulos m JOIN unidades_modulos um ON m.idmodulo = um.idmodulo");
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
     
        main.container {
            padding: 15px 0;
            margin-top: 20px;
            margin-bottom: 20px;
        }
        
        /* Estilos para o formulário de filtro */
        .filter-form {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 25px;
            border: 1px solid #e9ecef;
        }
        
        .filter-form label {
            font-weight: 500;
            color: #495057;
        }
        
        .filter-form select {
            border-radius: 5px;
            border: 1px solid #ced4da;
        }
        
      
        
        .filter-buttons {
            display: flex;
            gap: 10px;
        }
        
        /* Estilos para a tabela */
        .table-container {
            overflow-x: auto;
            margin-bottom: 20px;
        }
        
        .table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .table thead th {
            background-color: #157347;
            color: white;
            font-weight: 500;
            text-align: center;
            padding: 12px;
            white-space: nowrap;
        }
        
        .table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .table tbody tr:hover {
            background-color: #e9ecef;
        }
        
        .table td {
            padding: 10px;
            vertical-align: middle;
        }
        
        /* Estilos para os botões de ação */
        .action-buttons {
            display: flex;
            gap: 5px;
            justify-content: center;
        }
        
        .btn-add {
            background-color: #198754;
            border-color: #198754;
            color: white;
            padding: 8px 20px;
            border-radius: 5px;
            transition: all 0.3s;
        }
        
        .btn-add:hover {
            background-color: #157347;
            border-color: #146c43;
        }
        
        
        @media (max-width: 768px) {
            .filter-form .col-md-2 {
                margin-bottom: 15px;
            }
            
            .filter-buttons {
                justify-content: space-between;
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <header>
        <?php include('../../../includes/navbar.php'); ?>
        <?php include('../../../includes/menu-lateral-coordenacao.php'); ?>
    </header>
    <main class="container mt-4">
        <h2><i class="bi bi-calendar3"></i> Gerenciamento de Horários</h2>
        
        <!-- Formulário de Filtro -->
        <form method="GET" class="row mb-4 filter-form">
            <div class="row">
                <div class="col-12 mb-3">
                    <h5><i class="bi bi-funnel"></i> Filtros</h5>
                </div>
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
                <div class="filter-buttons">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Filtrar</button>
                    <a href="horarios.php" class="btn btn-outline-secondary"><i class="bi bi-x-circle"></i> Limpar</a>
                </div>
            </div>
            </div>
        </form>

        <?php
        // Construindo a consulta com os filtros
        $query = "SELECT h.idhorario, 
                         u.nome_unidade, 
                         m.nome_modulo, 
                         p.nome AS preceptor_nome, 
                         h.dia_semana, 
                         h.hora_inicio, 
                         h.hora_fim, 
                         sg.nome_subgrupo 
                  FROM horarios h
                  JOIN unidades u ON h.idunidade = u.idunidade
                  JOIN modulos m ON h.idmodulo = m.idmodulo
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
            echo '<div class="table-container">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th><i class="bi bi-building"></i> Unidade</th>
                            <th><i class="bi bi-journal-text"></i> Módulo</th>
                            <th><i class="bi bi-person"></i> Preceptor</th>
                            <th><i class="bi bi-calendar-day"></i> Dia</th>
                            <th><i class="bi bi-clock"></i> Hora Inicial</th>
                            <th><i class="bi bi-clock-history"></i> Hora Final</th>
                            <th><i class="bi bi-people"></i> Subgrupo</th>
                            <th><i class="bi bi-gear"></i> Ações</th>
                        </tr>
                    </thead>
                    <tbody>';
            
            while ($row = mysqli_fetch_assoc($result)) {
                echo '<tr>
                        <td>' . $row['nome_unidade'] . '</td>
                        <td>' . $row['nome_modulo'] . '</td>
                        <td>' . $row['preceptor_nome'] . '</td>
                        <td>' . $row['dia_semana'] . '</td>
                        <td>' . $row['hora_inicio'] . '</td>
                        <td>' . $row['hora_fim'] . '</td>
                        <td>' . $row['nome_subgrupo'] . '</td>
                        <td>
                            <div class="action-buttons">
                                <a href="preencher-horario.php?id=' . $row['idhorario'] . '" class="btn btn-primary btn-sm" title="Editar"><i class="bi bi-pencil"></i></a>
                                <a href="excluir-horario.php?id=' . $row['idhorario'] . '" class="btn btn-danger btn-sm" title="Excluir" onclick="return confirm(\'Tem certeza que deseja excluir este horário?\');"><i class="bi bi-trash"></i></a>
                            </div>
                        </td>
                      </tr>';
            }
            
            echo '</tbody></table>
                </div>';
        } else {
            echo '<div class="alert alert-info text-center" role="alert">
                    <i class="bi bi-info-circle me-2"></i> Não há horários correspondentes aos critérios de filtro.
                  </div>';
        }
        ?>
        <div class="d-flex justify-content-center mt-4">
            <a href="preencher-horario.php" class="btn btn-add"><i class="bi bi-plus-circle me-2"></i>Adicionar Novo Horário</a>
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
