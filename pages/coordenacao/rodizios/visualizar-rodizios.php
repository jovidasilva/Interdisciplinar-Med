<?php
require '../../../cfg/config.php';

// Consulta para obter os rodízios com módulos e grupos
$query = "
SELECT 
    r.idrodizio, 
    r.inicio, 
    r.fim, 
    m.nome_modulo AS modulo, 
    IFNULL(g.nome_grupo, 'não atribuído') AS grupo
FROM 
    rodizios r
LEFT JOIN 
    rodizios_subgrupos rs ON r.idrodizio = rs.idrodizio
LEFT JOIN 
    subgrupos sg ON rs.idsubgrupo = sg.idsubgrupo
LEFT JOIN 
    grupos g ON sg.idgrupo = g.idgrupo
JOIN 
    modulos m ON r.idmodulo = m.idmodulo
GROUP BY 
    r.idrodizio, m.nome_modulo, g.nome_grupo
ORDER BY 
    r.inicio, m.nome_modulo, g.nome_grupo
";

$result = $conn->query($query);

$rodizios_formatados = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $inicio = $row['inicio'];
        $fim = $row['fim'];
        $modulo = $row['modulo'];
        $grupo = $row['grupo'] ?: 'não atribuído';  // Define "não atribuído" quando grupo não existe

        $periodo_key = $inicio . ' até ' . $fim;

        if (!isset($rodizios_formatados[$periodo_key])) {
            $rodizios_formatados[$periodo_key] = [
                'inicio' => $inicio,
                'fim' => $fim,
                'modulos' => []
            ];
        }

        $rodizios_formatados[$periodo_key]['modulos'][] = [
            'modulo' => $modulo,
            'grupo' => $grupo
        ];
    }
}

// Consulta para obter a lista de módulos (para uso em filtros, caso necessário)
$query_modulos = "SELECT DISTINCT nome_modulo FROM modulos ORDER BY nome_modulo";
$result_modulos = $conn->query($query_modulos);

$filtro_modulo = isset($_POST['filtro']) ? $_POST['filtro'] : '';
?>

<div class="container mt-3">
    <h1>Rodízios Ativos <button onclick="location.href='?page=gerar-rodizios'" class="btn btn-success">Gerar Rodízios</button></h1>
    <?php
    if (empty($rodizios_formatados)) {
        echo "<p>Nenhum rodízio encontrado.</p>";
    } else {
        foreach ($rodizios_formatados as $periodo => $info) {
            echo "<h3>Rodízio: $periodo</h3>";
            foreach ($info['modulos'] as $modulo) {
                echo "<p>Módulo: {$modulo['modulo']} - Grupo: {$modulo['grupo']}</p>";
            }
        }
    }
    ?>
</div>

<?php $conn->close(); ?>
