<?php
require_once __DIR__ . '/../../../includes/app.php';
require_once __DIR__ . '/avaliacao-comum.php';
exigir_acesso(PAPEIS_PRECEPTOR);

$idaluno = (int) ($_GET['idaluno'] ?? 0);
$idmodulo = (int) ($_GET['idmodulo'] ?? 0);

$contexto = contexto_avaliacao($conn, usuario_id(), $idaluno, $idmodulo);
if (!$contexto) {
    redirecionar('avaliacoes.php', 'Este aluno não está matriculado em um módulo seu.', 'error');
}
if ($contexto['idavaliacao']) {
    redirecionar('consultar-avaliacao.php?id=' . (int) $contexto['idavaliacao'], 'Este aluno já foi avaliado neste módulo.', 'info');
}

$perguntas = db_todos($conn, 'SELECT idpergunta, titulo, descricao FROM perguntas_avaliacoes WHERE ativo = 1 ORDER BY idpergunta');

layout_inicio('Realizar avaliação');
page_header('Avaliar ' . $contexto['aluno'], $contexto['nome_modulo'] . ' · RA ' . $contexto['registro'], botao_voltar('avaliacoes.php'));
?>
<?php if (!$perguntas): ?>
    <div class="card"><div class="card-body"><?= empty_state('Nenhuma pergunta de avaliação ativa. A coordenação precisa cadastrá-las.', 'bi-question-circle') ?></div></div>
<?php else: ?>
<form method="POST" action="processar-avaliacao.php" class="form-narrow" style="max-width: 820px">
    <?= csrf_field() ?>
    <input type="hidden" name="idaluno" value="<?= $idaluno ?>">
    <input type="hidden" name="idmodulo" value="<?= $idmodulo ?>">

    <?php foreach ($perguntas as $i => $p): $pid = (int) $p['idpergunta']; ?>
        <fieldset class="question">
            <legend><?= $i + 1 ?>. <?= e($p['titulo']) ?></legend>
            <p><?= e($p['descricao']) ?></p>
            <div class="scale">
                <?php foreach (OPCOES_RESPOSTA as $valor => $rotulo): ?>
                    <input type="radio" id="p<?= $pid ?>_<?= $valor ?>" name="respostas[<?= $pid ?>]" value="<?= $valor ?>" required>
                    <label for="p<?= $pid ?>_<?= $valor ?>"><?= e($rotulo) ?> <span class="opacity-75">(<?= $valor ?>)</span></label>
                <?php endforeach; ?>
            </div>
        </fieldset>
    <?php endforeach; ?>

    <div class="form-actions">
        <span class="me-auto text-muted align-self-center" id="previaNota">Nota: —</span>
        <a href="avaliacoes.php" class="btn btn-outline-secondary">Cancelar</a>
        <button type="submit" class="btn btn-primary"><i class="bi bi-send"></i> Registrar avaliação</button>
    </div>
</form>
<script>
    // Prévia da nota final (média das respostas) enquanto preenche.
    document.addEventListener('change', function () {
        var marcadas = Array.from(document.querySelectorAll('.scale input:checked')).map(function (i) { return +i.value; });
        var total = <?= count($perguntas) ?>;
        document.getElementById('previaNota').textContent = marcadas.length
            ? 'Nota parcial: ' + (marcadas.reduce(function (a, b) { return a + b; }, 0) / marcadas.length).toFixed(2).replace('.', ',') + ' · ' + marcadas.length + '/' + total + ' respondidas'
            : 'Nota: —';
    });
</script>
<?php endif; ?>
<?php layout_fim(); ?>
