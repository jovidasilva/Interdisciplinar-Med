<?php
include('../../../cfg/config.php');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$idaluno = isset($_GET['idaluno']) ? intval($_GET['idaluno']) : null;

if (!$idaluno) {
    echo "<script>alert('ID do aluno não informado ou inválido.'); location.href='avaliacoes.php';</script>";
    exit();
}

// Consulta para obter os módulos associados ao aluno
$queryModulos = "
    SELECT m.idmodulo, m.nome_modulo 
    FROM modulos m
    JOIN modulo_alunos ma ON m.idmodulo = ma.idmodulo
    WHERE ma.idusuario = ?";
$stmtModulos = $conn->prepare($queryModulos);
$stmtModulos->bind_param("i", $idaluno);
$stmtModulos->execute();
$resultModulos = $stmtModulos->get_result();

// Consulta para obter as perguntas da avaliação
$queryPerguntas = "SELECT titulo, descricao FROM perguntas_avaliacoes";
$resultPerguntas = $conn->query($queryPerguntas);
?>
<h3>Realizar Avaliação</h3>

<form method="post" action="?page=processar-avaliacao&idaluno=<?php echo $idaluno; ?>&idpreceptor=<?php echo isset($_SESSION['login']['idusuario']) ? $_SESSION['login']['idusuario'] : ''; ?>">

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
