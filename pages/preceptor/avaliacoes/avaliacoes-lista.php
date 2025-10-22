<?php
// Carrega alunos para avaliação (baseado no preceptor)
$idpreceptor = $_SESSION['idusuario'] ?? null;
if (!$idpreceptor) {
    echo '<div class="alert alert-danger">Usuário não autenticado.</div>';
    return;
}

$alunos = [];
// Query simplificada: usa horarios para relacionar preceptor com alunos e módulos
$sqlAlunos = "SELECT DISTINCT u.idusuario AS aluno_id, u.nome, u.registro, sg.nome_subgrupo,
                     m.idmodulo AS id_modulo, m.nome_modulo,
                     (SELECT COUNT(*) FROM avaliacoes a WHERE a.idaluno = u.idusuario AND a.idpreceptor = ? AND a.idmodulo = m.idmodulo) > 0 AS avaliado
              FROM horarios h
              JOIN subgrupos sg ON sg.idsubgrupo = h.idsubgrupo
              JOIN alunos_subgrupos als ON als.idsubgrupo = sg.idsubgrupo
              JOIN usuarios u ON u.idusuario = als.idusuario
              JOIN modulos m ON m.idmodulo = h.idmodulo
              WHERE h.idpreceptor = ? AND u.tipo = 0
              ORDER BY u.nome, m.nome_modulo";

$stmt = $conn->prepare($sqlAlunos);
$stmt->bind_param("ii", $idpreceptor, $idpreceptor);
$stmt->execute();
$res = $stmt->get_result();

while ($row = $res->fetch_assoc()) {
    $alunoId = $row['aluno_id'];
    
    if (!isset($alunos[$alunoId])) {
        $alunos[$alunoId] = [
            'aluno_id' => $alunoId,
            'nome' => $row['nome'],
            'registro' => $row['registro'],
            'nomeSubgrupo' => $row['nome_subgrupo'],
            'modulos' => []
        ];
    } else {
        // Agrega subgrupos distintos
        $existente = $alunos[$alunoId]['nomeSubgrupo'];
        $novo = $row['nome_subgrupo'];
        if (strpos($existente, $novo) === false) {
            $alunos[$alunoId]['nomeSubgrupo'] = $existente . ', ' . $novo;
        }
    }
    
    $alunos[$alunoId]['modulos'][] = [
        'id_modulo' => $row['id_modulo'],
        'modulo_nome' => $row['nome_modulo'],
        'avaliado' => (bool)$row['avaliado']
    ];
}
$stmt->close();
?>

<h3>Lista de Alunos para Avaliação</h3>
<table class="table table-striped table-secondary table-bordered">
    <thead class="table-secondary">
        <tr>
            <th>Nome</th>
            <th>RA</th>
            <th>Subgrupo</th>
            <th class="text-center">Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($alunos)): foreach ($alunos as $al): ?>
            <tr>
                <td><?= htmlspecialchars($al['nome']) ?></td>
                <td><?= htmlspecialchars($al['registro']) ?></td>
                <td><?= htmlspecialchars($al['nomeSubgrupo']) ?></td>
                <td class="text-center">
                    <?php
                    // Verifica se todos módulos foram avaliados
                    $todosAvaliados = true;
                    foreach ($al['modulos'] as $m) {
                        if (!$m['avaliado']) {
                            $todosAvaliados = false;
                            break;
                        }
                    }
                    ?>
                    <?php if ($todosAvaliados): ?>
                        <span class="badge bg-success">Aluno Avaliado</span>
                    <?php else: ?>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-aluno-<?= $al['aluno_id'] ?>">
                            Realizar Avaliação
                        </button>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; else: ?>
            <tr><td colspan="4" class="text-center">Nenhum aluno encontrado para avaliação.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<!-- Modais de Módulos (um para cada aluno) -->
<?php foreach ($alunos as $al): ?>
<div class="modal fade" id="modal-aluno-<?= $al['aluno_id'] ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Módulos de <?= htmlspecialchars($al['nome']) ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <ul class="list-group">
                    <?php foreach ($al['modulos'] as $mod): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><?= htmlspecialchars($mod['modulo_nome']) ?></span>
                            <?php if ($mod['avaliado']): ?>
                                <span class="badge bg-success">Já Avaliado</span>
                            <?php else: ?>
                                <a href="?page=realizar-avaliacao&idaluno=<?= $al['aluno_id'] ?>&id_modulo=<?= $mod['id_modulo'] ?>&aluno_nome=<?= urlencode($al['nome']) ?>&modulo_nome=<?= urlencode($mod['modulo_nome']) ?>" class="btn btn-sm btn-outline-primary">Avaliar</a>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>
