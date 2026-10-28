<?php
require_once __DIR__ . '/../../../includes/app.php';
exigir_acesso(PAPEIS_COORDENACAO);

$idsubgrupo = (int) ($_GET['idsubgrupo'] ?? $_POST['idsubgrupo'] ?? 0);
$sub = db_um($conn, 'SELECT sg.idsubgrupo, sg.nome_subgrupo, g.idciclo, c.descricao, c.periodo
    FROM subgrupos sg JOIN grupos g ON g.idgrupo = sg.idgrupo LEFT JOIN rodizio_ciclos c ON c.idciclo = g.idciclo
    WHERE sg.idsubgrupo = ?', 'i', [$idsubgrupo])
    ?? redirecionar('grupos.php', 'Subgrupo não encontrado.', 'error');
$aqui = 'ver-alunos.php?idsubgrupo=' . $idsubgrupo;
$idciclo = (int) $sub['idciclo'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify_or_die();
    $ids = ids_do_post('alunos');
    $acao = $_POST['acao'] ?? '';

    // Um aluno fica em um único subgrupo por ciclo: adicionar/mover sempre
    // tira o aluno de qualquer outro subgrupo do mesmo ciclo antes.
    $tirarDoCiclo = 'DELETE asg FROM alunos_subgrupos asg JOIN subgrupos sg ON sg.idsubgrupo = asg.idsubgrupo JOIN grupos g ON g.idgrupo = sg.idgrupo
        WHERE asg.idusuario = ? AND g.idciclo = ?';

    $conn->begin_transaction();
    if ($acao === 'remover') {
        foreach ($ids as $id) {
            db_exec($conn, 'DELETE FROM alunos_subgrupos WHERE idusuario = ? AND idsubgrupo = ?', 'ii', [$id, $idsubgrupo]);
        }
        $msg = count($ids) . ' aluno(s) removido(s) do subgrupo.';
    } elseif ($acao === 'mover' || $acao === 'adicionar') {
        $destino = $acao === 'mover' ? (int) ($_POST['destino'] ?? 0) : $idsubgrupo;
        $ok = db_um($conn, 'SELECT 1 FROM subgrupos sg JOIN grupos g ON g.idgrupo = sg.idgrupo WHERE sg.idsubgrupo = ? AND g.idciclo = ?', 'ii', [$destino, $idciclo]);
        if (!$ok) {
            $conn->rollback();
            redirecionar($aqui, 'Escolha um subgrupo de destino deste ciclo.', 'error');
        }
        foreach ($ids as $id) {
            db_exec($conn, $tirarDoCiclo, 'ii', [$id, $idciclo]);
            db_exec($conn, 'INSERT INTO alunos_subgrupos (idusuario, idsubgrupo) SELECT idusuario, ? FROM usuarios WHERE idusuario = ? AND tipo = 0', 'ii', [$destino, $id]);
        }
        $msg = count($ids) . ($acao === 'mover' ? ' aluno(s) movido(s).' : ' aluno(s) adicionado(s).');
    } else {
        $conn->rollback();
        redirecionar($aqui, 'Ação inválida.', 'error');
    }
    $conn->commit();
    redirecionar($aqui, $msg);
}

$alunos = db_todos($conn, 'SELECT u.idusuario, u.nome, u.registro FROM alunos_subgrupos asg JOIN usuarios u ON u.idusuario = asg.idusuario
    WHERE asg.idsubgrupo = ? ORDER BY u.nome', 'i', [$idsubgrupo]);
$outrosSubgrupos = db_todos($conn, 'SELECT sg.idsubgrupo, sg.nome_subgrupo FROM subgrupos sg JOIN grupos g ON g.idgrupo = sg.idgrupo
    WHERE g.idciclo = ? AND sg.idsubgrupo <> ? ORDER BY sg.nome_subgrupo', 'ii', [$idciclo, $idsubgrupo]);
// Alunos ativos do período do ciclo que não estão em nenhum subgrupo dele.
$foraDoCiclo = db_todos($conn, 'SELECT u.idusuario, u.nome, u.registro FROM usuarios u
    WHERE u.tipo = 0 AND u.ativo = 1 AND u.periodo = ?
      AND NOT EXISTS (SELECT 1 FROM alunos_subgrupos asg JOIN subgrupos sg ON sg.idsubgrupo = asg.idsubgrupo JOIN grupos g ON g.idgrupo = sg.idgrupo
                      WHERE asg.idusuario = u.idusuario AND g.idciclo = ?)
    ORDER BY u.nome', 'ii', [(int) $sub['periodo'], $idciclo]);

layout_inicio('Subgrupo ' . $sub['nome_subgrupo']);
page_header('Subgrupo ' . $sub['nome_subgrupo'], ($sub['descricao'] ?? 'Sem ciclo') . ' · ' . count($alunos) . ' aluno(s)', botao_voltar('grupos.php?ciclo=' . $idciclo));
?>
<div class="assoc">
    <form method="POST" class="card" data-require-check="alunos[]">
        <?= csrf_field() ?>
        <input type="hidden" name="idsubgrupo" value="<?= $idsubgrupo ?>">
        <div class="card-header">
            <h2>Alunos no subgrupo</h2>
            <?php if ($alunos): ?><label class="small"><input type="checkbox" class="form-check-input me-1" data-select-all="sel-in"> Todos</label><?php endif; ?>
        </div>
        <?php if (!$alunos): ?>
            <div class="card-body"><?= empty_state('Nenhum aluno neste subgrupo.', 'bi-people') ?></div>
        <?php else: ?>
            <ul class="check-list">
                <?php foreach ($alunos as $a): ?>
                    <li><label><input type="checkbox" class="form-check-input sel-in" name="alunos[]" value="<?= (int) $a['idusuario'] ?>"> <?= e($a['nome']) ?><small><?= e($a['registro']) ?></small></label></li>
                <?php endforeach; ?>
            </ul>
            <div class="card-footer">
                <div class="d-flex gap-2 align-items-center">
                    <?php if ($outrosSubgrupos): ?>
                        <select name="destino" class="form-select form-select-sm w-auto" aria-label="Subgrupo de destino">
                            <?php foreach ($outrosSubgrupos as $o): ?><option value="<?= (int) $o['idsubgrupo'] ?>"><?= e($o['nome_subgrupo']) ?></option><?php endforeach; ?>
                        </select>
                        <button name="acao" value="mover" class="btn btn-sm btn-outline-primary"><i class="bi bi-arrow-left-right"></i> Mover</button>
                    <?php endif; ?>
                </div>
                <button name="acao" value="remover" class="btn btn-sm btn-outline-danger"><i class="bi bi-dash-circle"></i> Remover</button>
            </div>
        <?php endif; ?>
    </form>

    <form method="POST" class="card" data-require-check="alunos[]">
        <?= csrf_field() ?>
        <input type="hidden" name="idsubgrupo" value="<?= $idsubgrupo ?>">
        <div class="card-header">
            <h2>Fora do ciclo <span class="text-muted fw-normal">(<?= (int) $sub['periodo'] ?>º período)</span></h2>
            <?php if ($foraDoCiclo): ?><label class="small"><input type="checkbox" class="form-check-input me-1" data-select-all="sel-out"> Todos</label><?php endif; ?>
        </div>
        <?php if (!$foraDoCiclo): ?>
            <div class="card-body"><?= empty_state('Todos os alunos ativos do período já estão em algum subgrupo deste ciclo.', 'bi-check2-circle') ?></div>
        <?php else: ?>
            <ul class="check-list">
                <?php foreach ($foraDoCiclo as $a): ?>
                    <li><label><input type="checkbox" class="form-check-input sel-out" name="alunos[]" value="<?= (int) $a['idusuario'] ?>"> <?= e($a['nome']) ?><small><?= e($a['registro']) ?></small></label></li>
                <?php endforeach; ?>
            </ul>
            <div class="card-footer"><span></span><button name="acao" value="adicionar" class="btn btn-sm btn-primary"><i class="bi bi-plus-circle"></i> Adicionar a este subgrupo</button></div>
        <?php endif; ?>
    </form>
</div>
<?php layout_fim(); ?>
