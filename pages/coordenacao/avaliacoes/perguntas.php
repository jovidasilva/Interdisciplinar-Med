<?php
require_once __DIR__ . '/../../../includes/app.php';
exigir_acesso(PAPEIS_COORDENACAO);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify_or_die();
    $acao = $_POST['acao'] ?? '';
    $id = (int) ($_POST['idpergunta'] ?? 0);
    $titulo = trim((string) ($_POST['titulo'] ?? ''));
    $descricao = trim((string) ($_POST['descricao'] ?? ''));

    if ($acao === 'salvar') {
        if ($titulo === '' || $descricao === '') {
            redirecionar('perguntas.php', 'Preencha o título e a descrição.', 'error');
        }
        if ($id) {
            db_exec($conn, 'UPDATE perguntas_avaliacoes SET titulo = ?, descricao = ? WHERE idpergunta = ?', 'ssi', [$titulo, $descricao, $id]);
            redirecionar('perguntas.php', 'Pergunta atualizada.');
        }
        db_exec($conn, "INSERT INTO perguntas_avaliacoes (titulo, descricao, tipo_resposta, ativo) VALUES (?, ?, 'escala', 1)", 'ss', [$titulo, $descricao]);
        redirecionar('perguntas.php', 'Pergunta adicionada.');
    }
    if ($acao === 'alternar') {
        db_exec($conn, 'UPDATE perguntas_avaliacoes SET ativo = 1 - ativo WHERE idpergunta = ?', 'i', [$id]);
        redirecionar('perguntas.php', 'Situação da pergunta atualizada.');
    }
    if ($acao === 'excluir') {
        // Apagar pergunta já respondida mudaria avaliações antigas (CASCADE): só desativar.
        if (db_um($conn, 'SELECT 1 FROM avaliacoes_respostas WHERE idpergunta = ? LIMIT 1', 'i', [$id])) {
            redirecionar('perguntas.php', 'Esta pergunta já foi respondida em avaliações; desative-a em vez de excluir.', 'warning');
        }
        db_exec($conn, 'DELETE FROM perguntas_avaliacoes WHERE idpergunta = ?', 'i', [$id]);
        redirecionar('perguntas.php', 'Pergunta excluída.');
    }
    redirecionar('perguntas.php', 'Ação inválida.', 'error');
}

$perguntas = db_todos($conn, 'SELECT p.idpergunta, p.titulo, p.descricao, p.ativo,
        (SELECT COUNT(*) FROM avaliacoes_respostas ar WHERE ar.idpergunta = p.idpergunta) AS respostas
    FROM perguntas_avaliacoes p ORDER BY p.ativo DESC, p.idpergunta');
$editar = (int) ($_GET['editar'] ?? 0);

/** Formulário de criação/edição de pergunta. */
function form_pergunta(?array $p = null): void
{
    ?>
    <form method="POST" class="<?= $p ? '' : 'card-body' ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="acao" value="salvar">
        <input type="hidden" name="idpergunta" value="<?= (int) ($p['idpergunta'] ?? 0) ?>">
        <div class="mb-3">
            <label class="form-label" for="titulo<?= (int) ($p['idpergunta'] ?? 0) ?>">Critério</label>
            <input type="text" name="titulo" id="titulo<?= (int) ($p['idpergunta'] ?? 0) ?>" class="form-control" maxlength="255" value="<?= e($p['titulo'] ?? '') ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label" for="descricao<?= (int) ($p['idpergunta'] ?? 0) ?>">O que o preceptor observa</label>
            <textarea name="descricao" id="descricao<?= (int) ($p['idpergunta'] ?? 0) ?>" class="form-control" rows="3" required><?= e($p['descricao'] ?? '') ?></textarea>
        </div>
        <div class="d-flex gap-2 justify-content-end">
            <?php if ($p): ?><a href="perguntas.php" class="btn btn-outline-secondary">Cancelar</a><?php endif; ?>
            <button class="btn btn-primary"><i class="bi bi-check2"></i> <?= $p ? 'Salvar' : 'Adicionar pergunta' ?></button>
        </div>
    </form>
    <?php
}

layout_inicio('Perguntas de avaliação');
page_header('Perguntas de avaliação', 'Critérios do formulário que o preceptor preenche. Cada um é respondido em: ' . implode(', ', OPCOES_RESPOSTA) . '. A nota final é a média.', botao_voltar('avaliacoes.php'));
?>
<div class="row g-4">
    <div class="col-xl-8">
        <?php if (!$perguntas): ?>
            <div class="card"><div class="card-body"><?= empty_state('Nenhuma pergunta cadastrada.', 'bi-question-circle') ?></div></div>
        <?php endif; ?>
        <div class="gap-list">
            <?php foreach ($perguntas as $i => $p): ?>
                <div class="card<?= (int) $p['ativo'] ? '' : ' opacity-75' ?>">
                    <div class="card-body">
                        <?php if ($editar === (int) $p['idpergunta']): ?>
                            <?php form_pergunta($p); ?>
                        <?php else: ?>
                            <div class="d-flex gap-3 align-items-start">
                                <div class="flex-grow-1">
                                    <h2 class="h6 mb-1"><?= e($p['titulo']) ?> <?= (int) $p['ativo'] ? '' : badge_status('inativo') ?></h2>
                                    <p class="text-muted small mb-2"><?= e($p['descricao']) ?></p>
                                    <span class="small text-muted"><?= (int) $p['respostas'] ?> resposta(s) registrada(s)</span>
                                </div>
                                <div class="d-flex gap-1 flex-shrink-0">
                                    <a href="?editar=<?= (int) $p['idpergunta'] ?>" class="btn btn-sm btn-outline-secondary" aria-label="Editar"><i class="bi bi-pencil"></i></a>
                                    <?= form_acao('perguntas.php', ['acao' => 'alternar', 'idpergunta' => $p['idpergunta']], (int) $p['ativo'] ? 'Desativar' : 'Ativar', 'btn btn-sm btn-outline-secondary') ?>
                                    <?php if (!(int) $p['respostas']): ?>
                                        <?= form_acao('perguntas.php', ['acao' => 'excluir', 'idpergunta' => $p['idpergunta']], '<i class="bi bi-trash"></i>', 'btn btn-sm btn-outline-danger', 'Excluir esta pergunta?') ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card position-sticky" style="top: calc(var(--topbar-h) + 16px)">
            <div class="card-header"><h2>Nova pergunta</h2></div>
            <?php form_pergunta(); ?>
        </div>
    </div>
</div>
<?php layout_fim(); ?>
