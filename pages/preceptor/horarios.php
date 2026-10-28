<?php
require_once __DIR__ . '/../../includes/app.php';
exigir_acesso(PAPEIS_PRECEPTOR);

$horariosPorDia = [];
$linhas = db_todos($conn, "SELECT h.dia_semana, h.hora_inicio, h.hora_fim, h.local,
        u.nome_unidade, d.nome_departamento, m.nome_modulo, sg.nome_subgrupo
    FROM horarios h
    JOIN unidades u ON h.idunidade = u.idunidade
    LEFT JOIN departamentos d ON h.iddepartamento = d.iddepartamento
    JOIN modulos m ON h.idmodulo = m.idmodulo
    JOIN subgrupos sg ON h.idsubgrupo = sg.idsubgrupo
    WHERE h.idpreceptor = ?
    ORDER BY FIELD(h.dia_semana, 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo'), h.hora_inicio", 'i', [usuario_id()]);
foreach ($linhas as $row) {
    $horariosPorDia[$row['dia_semana']][] = $row;
}

layout_inicio('Meus horários');
page_header('Meus horários', 'Atividades em que você é o preceptor responsável.');

if (!$horariosPorDia) {
    echo '<div class="card"><div class="card-body">' . empty_state('Você ainda não tem horários cadastrados.', 'bi-clock') . '</div></div>';
}
foreach (DIAS_SEMANA as $dia):
    if (empty($horariosPorDia[$dia])) {
        continue;
    } ?>
    <div class="card day-block">
        <div class="card-header"><h2><?= e($dia) ?></h2><span class="text-muted"><?= count($horariosPorDia[$dia]) ?> atividade(s)</span></div>
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Horário</th><th>Módulo</th><th>Subgrupo</th><th>Unidade</th><th>Departamento</th><th>Local</th></tr></thead>
                <tbody>
                    <?php foreach ($horariosPorDia[$dia] as $h): ?>
                        <tr>
                            <td class="time"><?= e(hora_curta($h['hora_inicio'])) ?>–<?= e(hora_curta($h['hora_fim'])) ?></td>
                            <td><?= e($h['nome_modulo']) ?></td>
                            <td><?= e($h['nome_subgrupo']) ?></td>
                            <td><?= e($h['nome_unidade']) ?></td>
                            <td><?= e($h['nome_departamento'] ?? '—') ?></td>
                            <td><?= e($h['local'] ?: '—') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endforeach;
layout_fim();
