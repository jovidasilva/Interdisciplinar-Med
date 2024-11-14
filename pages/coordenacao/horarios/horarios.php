<?php
session_start();
include('../../../cfg/config.php');

if (empty($_SESSION["login"])) {
    echo "<script>location.href='../../index.php';</script>";
    exit();
}

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
</head>
<body>
    <header>
        <?php include('../../../includes/navbar.php'); ?>
        <?php include('../../../includes/menu-lateral-coordenacao.php'); ?>
    </header>
    <main class="container mt-4">
    <h2>Horários</h2>
    <?php
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
              JOIN modulos_departamentos md ON m.idmodulo = md.idmodulo
              JOIN departamentos d ON md.iddepartamento = d.iddepartamento
              JOIN usuarios p ON h.idpreceptor = p.idusuario
              JOIN subgrupos sg ON h.idsubgrupo = sg.idsubgrupo";
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
            echo "<tr>
                    <td>{$row['nome_unidade']}</td>
                    <td>{$row['nome_departamento']}</td>
                    <td>{$row['nome_modulo']}</td>
                    <td>{$row['preceptor_nome']}</td>
                    <td>{$row['dia_semana']}</td>
                    <td>{$row['hora_inicio']}</td>
                    <td>{$row['hora_fim']}</td>
                    <td>{$row['nome_subgrupo']}</td>
                    <td>
                        <a href='preencher-horario.php?id={$row['idhorario']}' class='btn btn-primary btn-sm'>Editar</a>
                        <a href='excluir-horario.php?id={$row['idhorario']}' class='btn btn-danger btn-sm' onclick='return confirm(\"Tem certeza que deseja excluir este horário?\")'>Excluir</a>
                    </td>
                  </tr>";
        }
        
        echo '</tbody></table>';
    } else {
        echo '<div class="d-flex justify-content-center align-items-center" style="height: 200px;">
                <p class="text-center">Não há horários disponíveis. Por favor, adicione um novo horário.</p>
              </div>';
    }
    ?>
    <div class="d-flex justify-content-center">
        <a href="preencher-horario.php" class="btn btn-success mt-3">Adicionar Novo Horário</a>
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
</body>
</html>