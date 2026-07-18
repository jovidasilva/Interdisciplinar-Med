<?php
session_start();
if (empty($_SESSION["login"]) || !in_array($_SESSION['tipo'] ?? null, [2, 3], true)) {
    echo "<script>location.href='../../index.php';</script>";
    exit();
}
require_once __DIR__ . '/../../../cfg/config.php';
require_once __DIR__ . '/../../../includes/csrf.php';

// Uma linha por (subgrupo, rodízio) — um subgrupo participa de até 3
// rodízios (um por módulo em rotação), então isso é esperado e tratado
// abaixo agrupando por ID (não por nome, que se repete a cada nova geração
// de rodízio e escondia gerações antigas/novas uma da outra).
$queryGrupos = "SELECT g.idgrupo, g.nome_grupo, s.idsubgrupo, s.nome_subgrupo,
                       r.idrodizio, r.periodo, r.inicio, r.fim, m.nome_modulo
                FROM grupos g
                JOIN subgrupos s ON g.idgrupo = s.idgrupo
                LEFT JOIN rodizios_subgrupos rs ON s.idsubgrupo = rs.idsubgrupo
                LEFT JOIN rodizios r ON rs.idrodizio = r.idrodizio
                LEFT JOIN modulos m ON r.idmodulo = m.idmodulo
                ORDER BY g.idgrupo DESC, s.nome_subgrupo, r.inicio";

$resultGrupos = $conn->query($queryGrupos);

$grupos = [];
while ($row = $resultGrupos->fetch_assoc()) {
    $idgrupo = $row['idgrupo'];
    $idsubgrupo = $row['idsubgrupo'];

    if (!isset($grupos[$idgrupo])) {
        $grupos[$idgrupo] = [
            'nome_grupo' => $row['nome_grupo'],
            'subgrupos' => [],
        ];
    }

    if (!isset($grupos[$idgrupo]['subgrupos'][$idsubgrupo])) {
        $grupos[$idgrupo]['subgrupos'][$idsubgrupo] = [
            'nome_subgrupo' => $row['nome_subgrupo'],
            'rodizios' => [],
        ];
    }

    if ($row['idrodizio'] !== null) {
        $grupos[$idgrupo]['subgrupos'][$idsubgrupo]['rodizios'][] = [
            'nome_modulo' => $row['nome_modulo'],
            'periodo' => $row['periodo'],
            'inicio' => $row['inicio'],
            'fim' => $row['fim'],
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grupos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../../css/style.css?v=<?php echo ASSET_VERSION; ?>">
</head>

<body>

    <header>
        <?php include('../../../includes/navbar.php'); ?>
        <?php include('../../../includes/menu-lateral-coordenacao.php'); ?>
    </header>

    <main>
        <div class="container mt-3">
            <div class="card">
                <div class="card-body">
                    <h1>Grupos</h1>

                    <?php if (empty($grupos)): ?>
                        <p class="text-muted">Nenhum grupo cadastrado ainda. Grupos são criados automaticamente ao gerar um rodízio.
                    <?php else: ?>
                        <table class="table table-striped mt-3">
                            <tbody>
                                <?php foreach ($grupos as $idgrupo => $grupo): ?>
                                    <tr data-bs-toggle="collapse" data-bs-target="#grupo<?= (int) $idgrupo ?>"
                                        class="accordion-toggle" style="cursor: pointer;">
                                        <td><strong>Grupo <?= htmlspecialchars($grupo['nome_grupo']) ?></strong></td>
                                        <td class="text-muted">#<?= (int) $idgrupo ?></td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" class="p-0">
                                            <div id="grupo<?= (int) $idgrupo ?>" class="collapse">
                                                <table class="table table-striped mb-0">
                                                    <thead>
                                                        <tr>
                                                            <th>Subgrupo</th>
                                                            <th>Módulos / rodízios</th>
                                                            <th>Ação</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($grupo['subgrupos'] as $idsubgrupo => $subgrupo): ?>
                                                            <tr>
                                                                <td><?= htmlspecialchars($subgrupo['nome_subgrupo']) ?></td>
                                                                <td>
                                                                    <?php if (empty($subgrupo['rodizios'])): ?>
                                                                        <span class="text-muted">Nenhum rodízio associado.</span>
                                                                    <?php else: ?>
                                                                        <ul class="mb-0 ps-3">
                                                                            <?php foreach ($subgrupo['rodizios'] as $rz): ?>
                                                                                <li>
                                                                                    <?= htmlspecialchars($rz['nome_modulo']) ?>
                                                                                    (<?= htmlspecialchars(date('d/m/Y', strtotime($rz['inicio']))) ?>
                                                                                    a
                                                                                    <?= htmlspecialchars(date('d/m/Y', strtotime($rz['fim']))) ?>)
                                                                                </li>
                                                                            <?php endforeach; ?>
                                                                        </ul>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td>
                                                                    <form method="POST" action="ver-alunos.php">
                                                                        <?php echo csrf_field(); ?>
                                                                        <input type="hidden" name="idsubgrupo" value="<?= (int) $idsubgrupo ?>">
                                                                        <input type="hidden" name="nome_subgrupo" value="<?= htmlspecialchars($subgrupo['nome_subgrupo']) ?>">
                                                                        <button type="submit" class="btn btn-info btn-sm">Ver Alunos</button>
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
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
