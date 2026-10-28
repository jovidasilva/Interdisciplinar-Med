<?php
require_once __DIR__ . '/../../../includes/app.php';
exigir_acesso(PAPEIS_COORDENACAO);

$f = [
    'dia' => in_array($_GET['dia'] ?? '', DIAS_SEMANA, true) ? $_GET['dia'] : '',
    'unidade' => (int) ($_GET['unidade'] ?? 0),
    'modulo' => (int) ($_GET['modulo'] ?? 0),
    'preceptor' => (int) ($_GET['preceptor'] ?? 0),
    'subgrupo' => (int) ($_GET['subgrupo'] ?? 0),
];

$horarios = db_todos($conn, "SELECT h.idhorario, h.dia_semana, h.hora_inicio, h.hora_fim, h.local,
        u.nome_unidade, d.nome_departamento, m.nome_modulo, p.nome AS preceptor, sg.nome_subgrupo, c.descricao AS ciclo
    FROM horarios h
    JOIN unidades u ON u.idunidade = h.idunidade
    LEFT JOIN departamentos d ON d.iddepartamento = h.iddepartamento
    JOIN modulos m ON m.idmodulo = h.idmodulo
    JOIN usuarios p ON p.idusuario = h.idpreceptor
    JOIN subgrupos sg ON sg.idsubgrupo = h.idsubgrupo
    JOIN grupos g ON g.idgrupo = sg.idgrupo
    LEFT JOIN rodizio_ciclos c ON c.idciclo = g.idciclo
    WHERE (? = '' OR h.dia_semana = ?) AND (? = 0 OR h.idunidade = ?) AND (? = 0 OR h.idmodulo = ?)
      AND (? = 0 OR h.idpreceptor = ?) AND (? = 0 OR h.idsubgrupo = ?)
    ORDER BY FIELD(h.dia_semana, 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo'), h.hora_inicio, sg.nome_subgrupo",
    'ssiiiiiiii', [$f['dia'], $f['dia'], $f['unidade'], $f['unidade'], $f['modulo'], $f['modulo'], $f['preceptor'], $f['preceptor'], $f['subgrupo'], $f['subgrupo']]);

$porDia = [];
foreach ($horarios as $h) {
    $porDia[$h['dia_semana']][] = $h;
}

$opcoes = [
    'unidade' => db_todos($conn, 'SELECT idunidade AS id, nome_unidade AS nome FROM unidades ORDER BY nome_unidade'),
    'modulo' => db_todos($conn, "SELECT idmodulo AS id, CONCAT(nome_modulo, ' (', periodo, 'º)') AS nome FROM modulos ORDER BY periodo, nome_modulo"),
    'preceptor' => db_todos($conn, 'SELECT idusuario AS id, nome FROM usuarios WHERE tipo IN (1, 3) ORDER BY nome'),
    'subgrupo' => db_todos($conn, "SELECT sg.idsubgrupo AS id, CONCAT(sg.nome_subgrupo, ' — ', COALESCE(c.descricao, 'sem ciclo')) AS nome
        FROM subgrupos sg JOIN grupos g ON g.idgrupo = sg.idgrupo LEFT JOIN rodizio_ciclos c ON c.idciclo = g.idciclo ORDER BY g.idciclo DESC, sg.nome_subgrupo"),
];
$filtrando = array_filter($f) !== [];

layout_inicio('Horários');
page_header('Horários', 'Grade semanal de atividades: quem, onde e quando cada subgrupo atua.',
    '<a href="preencher-horario.php" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Novo horário</a>');
?>
<form method="GET" class="card mb-4">
    <div class="card-body form-grid">
        <div>
            <label class="form-label" for="dia">Dia</label>
            <select name="dia" id="dia" class="form-select"><option value="">Todos</option>
                <?php foreach (DIAS_SEMANA as $d): ?><option <?= $f['dia'] === $d ? 'selected' : '' ?>><?= e($d) ?></option><?php endforeach; ?>
            </select>
        </div>
        <?php foreach (['unidade' => 'Unidade', 'modulo' => 'Módulo', 'preceptor' => 'Preceptor', 'subgrupo' => 'Subgrupo'] as $campo => $rotulo): ?>
            <div>
                <label class="form-label" for="f_<?= $campo ?>"><?= $rotulo ?></label>
                <select name="<?= $campo ?>" id="f_<?= $campo ?>" class="form-select"><option value="0">Todos</option>
                    <?php foreach ($opcoes[$campo] as $o): ?><option value="<?= (int) $o['id'] ?>" <?= $f[$campo] === (int) $o['id'] ? 'selected' : '' ?>><?= e($o['nome']) ?></option><?php endforeach; ?>
                </select>
            </div>
        <?php endforeach; ?>
        <div class="d-flex gap-2 align-items-end">
            <button class="btn btn-outline-primary"><i class="bi bi-funnel"></i> Filtrar</button>
            <?php if ($filtrando): ?><a href="horarios.php" class="btn btn-link">Limpar</a><?php endif; ?>
        </div>
    </div>
</form>

<?php if (!$horarios): ?>
    <div class="card"><div class="card-body"><?= empty_state($filtrando ? 'Nenhum horário com esses filtros.' : 'Nenhum horário cadastrado.', 'bi-clock', '<a href="preencher-horario.php" class="btn btn-primary">Cadastrar horário</a>') ?></div></div>
<?php endif; ?>

<?php foreach (DIAS_SEMANA as $dia): if (empty($porDia[$dia])) { continue; } ?>
    <div class="card day-block">
        <div class="card-header"><h2><?= e($dia) ?></h2><span class="text-muted"><?= count($porDia[$dia]) ?> atividade(s)</span></div>
        <div class="table-wrap">
            <table class="table align-middle">
                <thead><tr><th>Horário</th><th>Subgrupo</th><th>Módulo</th><th>Preceptor</th><th>Unidade / departamento</th><th>Local</th><th class="actions"></th></tr></thead>
                <tbody>
                    <?php foreach ($porDia[$dia] as $h): ?>
                        <tr>
                            <td class="time"><?= e(hora_curta($h['hora_inicio'])) ?>–<?= e(hora_curta($h['hora_fim'])) ?></td>
                            <td><span class="chip"><?= e($h['nome_subgrupo']) ?></span><div class="small text-muted"><?= e($h['ciclo'] ?? '') ?></div></td>
                            <td><?= e($h['nome_modulo']) ?></td>
                            <td><?= e($h['preceptor']) ?></td>
                            <td><?= e($h['nome_unidade']) ?><div class="small text-muted"><?= e($h['nome_departamento'] ?? '—') ?></div></td>
                            <td><?= e($h['local'] ?: '—') ?></td>
                            <td class="actions">
                                <a href="preencher-horario.php?id=<?= (int) $h['idhorario'] ?>" class="btn btn-sm btn-outline-secondary" aria-label="Editar"><i class="bi bi-pencil"></i></a>
                                <?= form_acao('acoes-horario.php', ['acao' => 'excluir', 'idhorario' => $h['idhorario']], '<i class="bi bi-trash"></i>', 'btn btn-sm btn-outline-danger', 'Excluir este horário?') ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endforeach; ?>
<?php layout_fim(); ?>
