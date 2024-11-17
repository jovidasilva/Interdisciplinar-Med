<?php
include('../../../cfg/config.php');

$queryGrupos = "SELECT g.nome_grupo, s.nome_subgrupo, r.periodo, r.inicio, r.fim, m.nome_modulo, s.idsubgrupo 
                FROM grupos g 
                JOIN subgrupos s ON g.idgrupo = s.idgrupo 
                JOIN rodizios_subgrupos rs ON s.idsubgrupo = rs.idsubgrupo 
                JOIN rodizios r ON rs.idrodizio = r.idrodizio 
                JOIN modulos m ON r.idmodulo = m.idmodulo 
                WHERE 1=1";

$stmtGrupos = $conn->prepare($queryGrupos);
$stmtGrupos->execute();
$resultGrupos = $stmtGrupos->get_result();

$grupos = array();
while ($row = $resultGrupos->fetch_assoc()) {
    $nomeGrupo = $row['nome_grupo'];
    $nomeSubgrupo = $row['nome_subgrupo'];

    if (!isset($grupos[$nomeGrupo])) {
        $grupos[$nomeGrupo] = array();
    }

    if (!isset($grupos[$nomeGrupo][$nomeSubgrupo])) {
        $grupos[$nomeGrupo][$nomeSubgrupo] = array(
            'nome_modulo' => $row['nome_modulo'],
            'periodo' => $row['periodo'],
            'inicio' => $row['inicio'],
            'fim' => $row['fim'],
            'idsubgrupo' => $row['idsubgrupo']
        );
    }
}
?>

<h1>Grupos</h1>
<table class="table table-striped mt-3">
    <tbody>
        <?php foreach ($grupos as $grupo => $subgrupos): ?>
            <tr data-bs-toggle="collapse" data-bs-target="#grupo<?= htmlspecialchars($grupo) ?>" class="accordion-toggle">
                <td><?= htmlspecialchars($grupo) ?></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td colspan="6">
                    <div id="grupo<?= htmlspecialchars($grupo) ?>" class="collapse">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Subgrupo</th>
                                    <th>Período</th>
                                    <th>Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($subgrupos as $subgrupoNome => $subgrupo): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($subgrupoNome) ?></td>
                                        <td><?= htmlspecialchars($subgrupo['periodo']) ?></td>
                                        <td>
                                            <form method="POST" action="?page=ver-alunos">
                                                <input type="hidden" name="idsubgrupo" value="<?= $subgrupo['idsubgrupo'] ?>">
                                                <input type="hidden" name="nome_subgrupo" value="<?= $subgrupoNome ?>">
                                                <button type="submit" class="btn btn-info">Ver
                                                    Alunos</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php
$stmtGrupos->close();
$conn->close();
?>