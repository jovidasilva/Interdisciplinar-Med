<?php
// Inicia a sessão para verificar o login do usuário
session_start();
if (empty($_SESSION["login"])) {
    // Redireciona para a página inicial se o usuário não estiver logado
    echo "<script>location.href='../../index.php';</script>";
    exit();
}

// Inclui o arquivo de configuração para conectar ao banco de dados
include('../../../cfg/config.php');

// Consulta para carregar os módulos disponíveis
$queryModulos = "SELECT idmodulo, nome_modulo FROM modulos";
$resultModulos = $conn->query($queryModulos);

// Consulta para carregar as perguntas de avaliação
$queryPerguntas = "SELECT titulo, descricao FROM perguntas_avaliacoes";
$resultPerguntas = $conn->query($queryPerguntas);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Realizar Avaliação</title>
    
    <!-- Links para CSS do Bootstrap e estilos adicionais -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../../css/style.css">
</head>
<body>
    <!-- Cabeçalho da página -->
    <header>
        <!-- Inclui o navbar e o menu lateral para o preceptor -->
        <?php include('../../../includes/navbar.php'); ?>
        <?php include('../../../includes/menu-lateral-preceptor.php'); ?>
    </header>

    <!-- Conteúdo principal da página -->
    <main>
        <div class="card">
            <div class="card-body">
                <h3>Realizar Avaliação</h3>
                
                <!-- Formulário para enviar a avaliação -->
                <form method="post" action="processar_avaliacao.php">
                    <!-- Selecionar o módulo -->
                    <div class="mb-3">
                        <label for="modulo" class="form-label">Módulo</label>
                        <select name="modulo" id="modulo" class="form-select" required>
                            <?php while ($rowModulo = $resultModulos->fetch_assoc()): ?>
                                <!-- Exibe cada módulo como uma opção no dropdown -->
                                <option value="<?php echo $rowModulo['idmodulo']; ?>">
                                    <?php echo htmlspecialchars($rowModulo['nome_modulo']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <!-- Exibe cada pergunta de avaliação, se houver perguntas disponíveis -->
                    <?php if ($resultPerguntas->num_rows > 0): ?>
                        <?php foreach ($resultPerguntas as $index => $pergunta): ?>
                            <fieldset class="mb-4">
                                <!-- Título e descrição de cada pergunta -->
                                <legend><?php echo htmlspecialchars($pergunta['titulo']); ?></legend>
                                <p><?php echo htmlspecialchars($pergunta['descricao']); ?></p>

                                <!-- Opções de resposta para cada pergunta -->
                                <div>
                                    <input type="radio" id="insuficiente_<?php echo $index; ?>" name="pergunta_<?php echo $index; ?>" value="0" required>
                                    <label for="insuficiente_<?php echo $index; ?>">Insuficiente</label>
                                </div>
                                <div>
                                    <input type="radio" id="regular_<?php echo $index; ?>" name="pergunta_<?php echo $index; ?>" value="5" required>
                                    <label for="regular_<?php echo $index; ?>">Regular</label>
                                </div>
                                <div>
                                    <input type="radio" id="bom_<?php echo $index; ?>" name="pergunta_<?php echo $index; ?>" value="7" required>
                                    <label for="bom_<?php echo $index; ?>">Bom</label>
                                </div>
                                <div>
                                    <input type="radio" id="excelente_<?php echo $index; ?>" name="pergunta_<?php echo $index; ?>" value="10" required>
                                    <label for="excelente_<?php echo $index; ?>">Excelente</label>
                                </div>
                            </fieldset>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <!-- Mensagem exibida se não houver perguntas cadastradas -->
                        <p>Nenhuma pergunta disponível.</p>
                    <?php endif; ?>

                    <!-- Botões de ação (Voltar e Enviar) -->
                    <div class="d-flex justify-content-end">
                        <!-- Botão para voltar à página anterior -->
                        <a href="avaliacoes.php" class="btn btn-secondary me-2">Voltar</a>
                        <!-- Botão para enviar o formulário -->
                        <button type="submit" class="btn btn-primary">Enviar Avaliação</button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <footer>
        <div class="card footer-home rounded-0">
            <div class="card-body"></div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
