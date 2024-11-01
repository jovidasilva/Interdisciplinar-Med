<?php
require_once '../../../cfg/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recebendo o ID do aluno da URL
    $idaluno = isset($_GET['idaluno']) ? intval($_GET['idaluno']) : null;
    
    if (!$idaluno) {
        echo "<script>alert('Erro: Aluno não encontrado.'); location.href='realizar-avaliacao.php';</script>";
        exit();
    }

    // Recebendo o ID do preceptor da sessão
    $idpreceptor = isset($_GET['idpreceptor']) ? intval($_GET['idpreceptor']) : null;
    
    if (!$idpreceptor) {
        echo "<script>alert('Erro: Preceptor não está logado.'); location.href='../../../index.php';</script>";
        exit();
    }

    // Recebendo o ID do módulo da requisição POST
    $idmodulo = isset($_POST['modulo']) ? intval($_POST['modulo']) : null;
    if (!$idmodulo) {
        echo "<script>alert('Erro: Módulo não selecionado.'); location.href='realizar-avaliacao.php';</script>";
        exit();
    }

    // Inicializando a pontuação total e o total de perguntas
    $total_pontuacao = 0;
    $total_perguntas = 0;

    // Calculando a pontuação total e o número de perguntas
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'pergunta_') === 0) {
            $total_pontuacao += intval($value);
            $total_perguntas++;
        }
    }

    // Calculando a média da avaliação
    $media = $total_perguntas > 0 ? $total_pontuacao / $total_perguntas : 0;
    $data_avaliacao = date('Y-m-d H:i:s');

    // Preparando e executando a inserção na tabela de avaliações
    $query = "INSERT INTO avaliacoes (idusuario, nota, data_avaliacao, idaluno, idpreceptor, idmodulo) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    // Bind dos parâmetros
    $stmt->bind_param("iisiii", $idpreceptor, $media, $data_avaliacao, $idaluno, $idpreceptor, $idmodulo);
    $stmt->execute();

    // Verificando se a inserção foi bem-sucedida
    if ($stmt->affected_rows > 0) {
        echo "<script>alert('Avaliação registrada com sucesso!'); location.href='avaliacoes.php';</script>";
    } else {
        echo "<script>alert('Erro ao registrar a avaliação.'); location.href='avaliacoes.php';</script>";
    }

    // Fechando a declaração
    $stmt->close();
}
?>
