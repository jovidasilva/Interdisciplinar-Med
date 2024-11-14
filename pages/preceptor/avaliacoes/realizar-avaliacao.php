<?php
include('../../../cfg/config.php');

$idaluno = isset($_GET['idaluno']) ? intval($_GET['idaluno']) : null;
$idpreceptor = isset($_SESSION['idusuario']) ? $_SESSION['idusuario'] : null;

if (!$idaluno) {
    echo "<script>alert('ID do aluno não informado ou inválido.'); location.href='avaliacoes.php';</script>";
    exit();
}

$queryModulos = "
    SELECT m.idmodulo, m.nome_modulo 
    FROM modulos m
    JOIN modulos_alunos ma ON m.idmodulo = ma.idmodulo
    WHERE ma.idusuario = ?";
$stmtModulos = $conn->prepare($queryModulos);
$stmtModulos->bind_param("i", $idaluno);
$stmtModulos->execute();
$resultModulos = $stmtModulos->get_result();

$queryPerguntas = "SELECT titulo, descricao FROM perguntas_avaliacoes";
$resultPerguntas = $conn->query($queryPerguntas);
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
<form method="post" action="processar-avaliacao.php?<?php echo 'idaluno=' . $idaluno . '&idpreceptor=' . $idpreceptor; ?>">
    <div class="mb-3">
        <label for="modulo" class="form-label">Módulo</label>
        <select name="modulo" id="modulo" class="form-select" required>
            <?php if ($resultModulos->num_rows > 0): ?>
                <?php while ($rowModulo = $resultModulos->fetch_assoc()): ?>
                    <option value="<?php echo $rowModulo['idmodulo']; ?>">
                        <?php echo htmlspecialchars($rowModulo['nome_modulo']); ?>
                    </option>
                <?php endwhile; ?>
            <?php else: ?>
                <option value="">Aluno não está cadastrado em nenhum módulo</option>
            <?php endif; ?>
        </select>
    </div>

    <?php if ($resultPerguntas->num_rows > 0): ?>
        <?php foreach ($resultPerguntas as $index => $pergunta): ?>
            <fieldset class="mb-4">
                <legend><?php echo htmlspecialchars($pergunta['titulo']); ?></legend>
                <p><?php echo htmlspecialchars($pergunta['descricao']); ?></p>

                <div>
                    <input type="radio" id="insuficiente_<?php echo $index; ?>" name="pergunta_<?php echo $index; ?>" value="4" required>
                    <label for="insuficiente_<?php echo $index; ?>">Insuficiente</label>
                </div>
                <div>
                    <input type="radio" id="regular_<?php echo $index; ?>" name="pergunta_<?php echo $index; ?>" value="6" required>
                    <label for="regular_<?php echo $index; ?>">Regular</label>
                </div>
                <div>
                    <input type="radio" id="bom_<?php echo $index; ?>" name="pergunta_<?php echo $index; ?>" value="8" required>
                    <label for="bom_<?php echo $index; ?>">Bom</label>
                </div>
                <div>
                    <input type="radio" id="excelente_<?php echo $index; ?>" name="pergunta_<?php echo $index; ?>" value="10" required>
                    <label for="excelente_<?php echo $index; ?>">Excelente</label>
                </div>
            </fieldset>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Nenhuma pergunta disponível.</p>
    <?php endif; ?>
    <div class="d-flex justify-content-end">
        <a href="avaliacoes.php" class="btn btn-secondary me-2">Voltar</a>
        <button type="submit" class="btn btn-primary">Enviar Avaliação</button>
    </div>
</form>