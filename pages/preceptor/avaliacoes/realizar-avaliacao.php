<?php
include('../../../cfg/config.php');

$idaluno = isset($_GET['idaluno']) ? intval($_GET['idaluno']) : null;
$id_modulo = isset($_GET['id_modulo']) ? intval($_GET['id_modulo']) : null;
$aluno_nome = isset($_GET['aluno_nome']) ? $_GET['aluno_nome'] : '';
$modulo_nome = isset($_GET['modulo_nome']) ? $_GET['modulo_nome'] : '';
$idpreceptor = isset($_SESSION['idusuario']) ? $_SESSION['idusuario'] : null;

if (!$idaluno || !$id_modulo) {
    echo "<script>alert('Dados insuficientes.'); location.href='avaliacoes.php';</script>";
    exit();
}

// Busca módulos disponíveis para este aluno e preceptor
$modulosDisponiveis = [];
$sqlMods = "SELECT DISTINCT m.idmodulo, m.nome_modulo,
                   (SELECT COUNT(*) FROM avaliacoes a WHERE a.idaluno = ? AND a.idpreceptor = ? AND a.idmodulo = m.idmodulo) > 0 AS avaliado
            FROM horarios h
            JOIN subgrupos sg ON sg.idsubgrupo = h.idsubgrupo
            JOIN alunos_subgrupos als ON als.idsubgrupo = sg.idsubgrupo
            JOIN modulos m ON m.idmodulo = h.idmodulo
            WHERE h.idpreceptor = ? AND als.idusuario = ?
            ORDER BY m.nome_modulo";
$stmtMods = $conn->prepare($sqlMods);
$stmtMods->bind_param("iiii", $idaluno, $idpreceptor, $idpreceptor, $idaluno);
$stmtMods->execute();
$resMods = $stmtMods->get_result();
while ($rowMod = $resMods->fetch_assoc()) {
    $modulosDisponiveis[] = $rowMod;
}
$stmtMods->close();

// Busca perguntas de avaliação
$queryPerguntas = "SELECT idpergunta, titulo, descricao FROM perguntas_avaliacoes ORDER BY idpergunta";
$resultPerguntas = $conn->query($queryPerguntas);
$perguntas = [];
if ($resultPerguntas) {
    while ($row = $resultPerguntas->fetch_assoc()) {
        $perguntas[] = $row;
    }
}
?>


<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f7f9fc;
    }

    h3 {
        color: green;
        font-weight: bold;
        margin-bottom: 1.5rem;
    }

    .form-container {
        background: #ffffff;
        border-radius: 8px;
        box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        padding: 2rem;
        max-width: 700px;
        margin: auto;
    }

    .form-select,
    .form-label,
    .btn {
        font-size: 1rem;
    }

    .fieldset-container {
        background: #f1f4f9;
        border-radius: 5px;
        padding: 1.5rem;
        margin-bottom: 1rem;
    }

    .legend {
        font-weight: bold;
        font-size: 1.2rem;
        color: #333;
    }

    .btn-primary {
        background-color: #007bff;
        border: none;
        padding: 0.6rem 1.2rem;
    }
</style>


<h3>Realizar Avaliação</h3>
<hr>

<!-- Informações do aluno -->
<div class="mb-4">
    <h5><strong>Aluno:</strong> <?= htmlspecialchars($aluno_nome) ?></h5>
</div>

<!-- Formulário de Avaliação -->
<form method="post" action="processar-avaliacao.php" id="formAvaliacao">
    <!-- Campos ocultos -->
    <input type="hidden" name="id_aluno" value="<?= $idaluno ?>" />
    <input type="hidden" name="idpreceptor" value="<?= $idpreceptor ?>" />
    
    <!-- Seletor de Módulo -->
    <div class="mb-4">
        <label for="selectModulo" class="form-label fw-bold">Módulo:</label>
        <select id="selectModulo" name="id_modulo" class="form-select" required>
            <?php foreach ($modulosDisponiveis as $mod): ?>
                <option value="<?= $mod['idmodulo'] ?>" 
                        <?= ($mod['idmodulo'] == $id_modulo) ? 'selected' : '' ?>
                        <?= $mod['avaliado'] ? 'data-avaliado="true"' : '' ?>>
                    <?= htmlspecialchars($mod['nome_modulo']) ?>
                    <?= $mod['avaliado'] ? ' (Já Avaliado)' : '' ?>
                </option>
            <?php endforeach; ?>
        </select>
        <div class="form-text">Você pode alterar o módulo antes de enviar a avaliação.</div>
    </div>

    <!-- Perguntas Dinâmicas -->
    <?php if (!empty($perguntas)): ?>
        <?php foreach ($perguntas as $index => $pergunta): ?>
            <fieldset class="mb-4">
                <legend><?= htmlspecialchars($pergunta['titulo']) ?></legend>
                <p><?= htmlspecialchars($pergunta['descricao']) ?></p>

                <div>
                    <input type="radio" id="insuficiente_<?= $index ?>" name="pergunta_<?= $pergunta['idpergunta'] ?>" value="4" required>
                    <label for="insuficiente_<?= $index ?>">Insuficiente</label>
                </div>
                <div>
                    <input type="radio" id="regular_<?= $index ?>" name="pergunta_<?= $pergunta['idpergunta'] ?>" value="6" required>
                    <label for="regular_<?= $index ?>">Regular</label>
                </div>
                <div>
                    <input type="radio" id="bom_<?= $index ?>" name="pergunta_<?= $pergunta['idpergunta'] ?>" value="8" required>
                    <label for="bom_<?= $index ?>">Bom</label>
                </div>
                <div>
                    <input type="radio" id="excelente_<?= $index ?>" name="pergunta_<?= $pergunta['idpergunta'] ?>" value="10" required>
                    <label for="excelente_<?= $index ?>">Excelente</label>
                </div>
            </fieldset>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Nenhuma pergunta disponível.</p>
    <?php endif; ?>
    
    <hr>
    <!-- Botões de ação -->
    <div class="d-flex justify-content-end mt-4">
        <a href="avaliacoes.php" class="btn btn-secondary me-2">Voltar</a>
        <button id="btnEnviar" type="submit" class="btn btn-primary">Enviar Avaliação</button>
    </div>
</form>

<!-- Validação JS -->
<script>
    // Alerta se módulo já foi avaliado
    const selectModulo = document.getElementById('selectModulo');
    selectModulo.addEventListener('change', function() {
        const opcaoSelecionada = this.options[this.selectedIndex];
        if (opcaoSelecionada.dataset.avaliado === 'true') {
            if (!confirm('Este módulo já foi avaliado. Deseja criar uma nova avaliação?')) {
                // Retorna para o módulo anterior
                this.value = '<?= $id_modulo ?>';
            }
        }
    });
    
    // Validação do formulário
    const form = document.querySelector('form');
    const btn = document.getElementById('btnEnviar');
    btn.addEventListener('click', function(e){
        const fieldsets = form.querySelectorAll('fieldset');
        let valido = true;
        let primeiroInvalido = null;
        fieldsets.forEach(fs => {
            const radios = fs.querySelectorAll('input[type="radio"]');
            const nomeGrupo = radios.length ? radios[0].name : null;
            const respondido = nomeGrupo && form.querySelector('input[name="' + nomeGrupo + '"]:checked');

            if (!respondido) {
                valido = false;
                if (!primeiroInvalido) primeiroInvalido = fs;
                fs.classList.add('border', 'border-danger');
                if (!fs.querySelector('.invalid-feedback')) {
                    const msg = document.createElement('div');
                    msg.className = 'invalid-feedback d-block fw-semibold';
                    msg.innerText = 'Selecione uma opção';
                    fs.appendChild(msg);
                }
            } else {
                fs.classList.remove('border', 'border-danger');
                const msg = fs.querySelector('.invalid-feedback');
                if (msg) msg.remove();
            }

            radios.forEach(r => r.addEventListener('change', () => {
                fs.classList.remove('border', 'border-danger');
                const msg = fs.querySelector('.invalid-feedback');
                if (msg) msg.remove();
            }, {once:true}));
        });

        if (!valido) {
            e.preventDefault();
            primeiroInvalido.scrollIntoView({behavior: 'smooth', block: 'center'});
            primeiroInvalido.focus();
        }
    });
</script>