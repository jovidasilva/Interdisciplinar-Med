<?php
require_once '../../../cfg/config.php';

$query = "
    SELECT 
        a.idavaliacao,
        aluno.nome AS aluno_nome,
        preceptor.nome AS preceptor_nome,
        a.data_avaliacao,
        a.nota
    FROM 
        avaliacoes AS a
    JOIN usuarios AS aluno ON a.idaluno = aluno.idusuario
    JOIN usuarios AS preceptor ON a.idpreceptor = preceptor.idusuario
    ORDER BY a.data_avaliacao DESC
";
$result = $conn->query($query);

?>

<h1>Listagem de Avaliações <button class="btn btn-secondary" onclick="location.href='?page=avaliacoes'">Voltar</button>
</h1>
<table class="table">
    <thead>
        <tr>
            <th>ID da Avaliação</th>
            <th>Aluno</th>
            <th>Preceptor</th>
            <th>Data da Avaliação</th>
            <th>Nota</th>
            <th>Ação</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['idavaliacao']; ?></td>
                <td><?php echo htmlspecialchars($row['aluno_nome']); ?></td>
                <td><?php echo htmlspecialchars($row['preceptor_nome']); ?></td>
                <td><?php echo $row['data_avaliacao']; ?></td>
                <td><?php echo $row['nota']; ?></td>
                <td>
                    <a href="?page=detalhes-avaliacoes&idavaliacao=<?php echo $row['idavaliacao']; ?>">Ver Detalhes</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

</body>

</html>