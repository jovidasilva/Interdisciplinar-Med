<?php
require_once '../../../cfg/config.php';

$idavaliacao = isset($_GET['idavaliacao']) ? intval($_GET['idavaliacao']) : null;

if (!$idavaliacao) {
    echo "<script>alert('Erro: Avaliação não encontrada.'); location.href='?page=listar-avaliacoes';</script>";
    exit();
}

$query_avaliacao = "
    SELECT 
        aluno.nome AS aluno_nome,
        preceptor.nome AS preceptor_nome,
        a.data_avaliacao,
        a.nota,
        m.nome_modulo AS modulo_nome 
    FROM 
        avaliacoes AS a
    JOIN usuarios AS aluno ON a.idaluno = aluno.idusuario
    JOIN usuarios AS preceptor ON a.idpreceptor = preceptor.idusuario
    JOIN modulos AS m ON a.idmodulo = m.idmodulo 
    WHERE 
        a.idavaliacao = ?
";
$stmt_avaliacao = $conn->prepare($query_avaliacao);
$stmt_avaliacao->bind_param("i", $idavaliacao);
$stmt_avaliacao->execute();
$result_avaliacao = $stmt_avaliacao->get_result();
$avaliacao = $result_avaliacao->fetch_assoc();

if (!$avaliacao) {
    echo "<script>alert('Erro: Avaliação não encontrada.'); location.href='?page=listar-avaliacoes';</script>";
    exit();
}

$query_respostas = "
    SELECT 
        p.descricao AS pergunta,
        ar.resposta
    FROM 
        avaliacoes_respostas AS ar
    JOIN perguntas_avaliacoes AS p ON ar.idpergunta = p.idpergunta
    WHERE 
        ar.idavaliacao = ?
";
$stmt_respostas = $conn->prepare($query_respostas);
$stmt_respostas->bind_param("i", $idavaliacao);
$stmt_respostas->execute();
$result_respostas = $stmt_respostas->get_result();

function tipo_resposta($nota) {
    switch ($nota) {
        case 4:
            return "Insuficiente";
        case 6:
            return "Regular";
        case 8:
            return "Bom";
        case 10:
            return "Excelente";
        default:
            return "Nota inválida";
    }
}

?>

<h1>Detalhes da Avaliação</h1>

<p><strong>Aluno:</strong> <?php echo htmlspecialchars($avaliacao['aluno_nome']); ?></p>
<p><strong>Preceptor:</strong> <?php echo htmlspecialchars($avaliacao['preceptor_nome']); ?></p>
<p><strong>Módulo:</strong> <?php echo htmlspecialchars($avaliacao['modulo_nome']); ?></p> <!-- Corrigido aqui -->
<p><strong>Data da Avaliação:</strong> <?php echo $avaliacao['data_avaliacao']; ?></p>
<p><strong>Nota Final:</strong> <?php echo $avaliacao['nota']; ?></p>

<h2>Perguntas e Respostas</h2>
<table class="table">
    <thead>
        <tr>
            <th>Pergunta</th>
            <th>Resposta</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($resposta = $result_respostas->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($resposta['pergunta']); ?></td>
                <td><?php echo htmlspecialchars(tipo_resposta($resposta['resposta'])); ?></td> 
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<a href="?page=listar-avaliacoes">Voltar para a lista de avaliações</a>
