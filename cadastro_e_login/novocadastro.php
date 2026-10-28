<?php
require_once __DIR__ . '/../includes/app.php';
exigir_acesso();

if (usuario_tipo() !== TIPO_PENDENTE) {
    redirecionar(home_do_usuario());
}

layout_inicio('Aguardando liberação');
?>
<div class="card form-narrow">
    <div class="card-body">
        <?= empty_state('Seu cadastro foi recebido e está aguardando aprovação da coordenação. Assim que seu tipo de acesso for definido, as funcionalidades aparecerão aqui.', 'bi-hourglass-split') ?>
    </div>
</div>
<?php layout_fim(); ?>
