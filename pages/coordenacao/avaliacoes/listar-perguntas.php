<?php

include('../../../cfg/config.php');

$sql = "SELECT * FROM perguntas_avaliacoes";
$result = $conn->query($sql);
?>


<h2>Gerenciar Perguntas de Avaliação <button class="btn btn-success" onclick="location.href='?page=acoes-avaliacoes&acao=adicionar'">Novo</button> <button class="btn btn-secondary"
        onclick="location.href='?page=avaliacoes'">Voltar</button></h2>

<div class="list-group my-3">
    <?php while ($row = $result->fetch_assoc()): ?>
        <div class="list-group-item">
            <h5><?php echo htmlspecialchars($row['titulo']); ?></h5>
            <p><?php echo htmlspecialchars($row['descricao']); ?></p>
            <p>Opções de Resposta: Insuficiente, Regular, Bom, Excelente</p>

            <a href="?page=acoes-avaliacoes&acao=excluir&idpergunta=<?php echo $row['idpergunta']; ?>"
                class="btn btn-danger btn-sm"
                onclick="return confirm('Tem certeza que deseja excluir esta pergunta?')">Excluir</a>

            <a href="?page=acoes-avaliacoes&acao=editar&idpergunta=<?php echo $row['idpergunta']; ?>&titulo=<?php echo urlencode($row['titulo']); ?>&descricao=<?php echo urlencode($row['descricao']); ?>"
                class="btn btn-primary btn-sm">Editar</a>
        </div>
    <?php endwhile; ?>
</div>
