<?php
require_once __DIR__ . '/../../includes/app.php';
exigir_acesso(PAPEIS_ALUNO);

$horariosPorDia = [];
$linhas = db_todos($conn, "SELECT h.dia_semana, h.hora_inicio, h.hora_fim, h.local,
        u.nome_unidade, d.nome_departamento, m.nome_modulo, p.nome AS preceptor_nome
    FROM alunos_subgrupos asg
    JOIN horarios h ON h.idsubgrupo = asg.idsubgrupo
    JOIN unidades u ON h.idunidade = u.idunidade
    LEFT JOIN departamentos d ON h.iddepartamento = d.iddepartamento
    JOIN modulos m ON h.idmodulo = m.idmodulo
    JOIN usuarios p ON h.idpreceptor = p.idusuario
    WHERE asg.idusuario = ?
    ORDER BY FIELD(h.dia_semana, 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo'), h.hora_inicio", 'i', [usuario_id()]);
foreach ($linhas as $row) {
    $horariosPorDia[$row['dia_semana']][] = $row;
}

layout_inicio('Meus horários');
page_header('Meus horários', 'Grade semanal das atividades do seu subgrupo.');

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
                <thead><tr><th>Horário</th><th>Módulo</th><th>Unidade</th><th>Departamento</th><th>Preceptor</th><th>Local</th></tr></thead>
                <tbody>
                    <?php foreach ($horariosPorDia[$dia] as $h): ?>
                        <tr>
                            <td class="time"><?= e(hora_curta($h['hora_inicio'])) ?>–<?= e(hora_curta($h['hora_fim'])) ?></td>
                            <td><?= e($h['nome_modulo']) ?></td>
                            <td><?= e($h['nome_unidade']) ?></td>
                            <td><?= e($h['nome_departamento'] ?? '—') ?></td>
                            <td><?= e($h['preceptor_nome']) ?></td>
                            <td><?= e($h['local'] ?: '—') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endforeach;
layout_fim();
