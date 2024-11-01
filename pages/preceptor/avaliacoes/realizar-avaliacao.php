<?php
session_start();

include('../../../cfg/config.php');

if (empty($_SESSION["login"])) {
    echo "<script>location.href='../../../index.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avaliações</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../../css/style.css">
    <style>
        body {
            overflow-y: hidden;
        }
        .container {
            overflow-y: auto;
            max-height: 750px;
        }
    </style>
</head>
<body>
    <header>
        <?php include('../../../includes/navbar.php'); ?>
        <?php include('../../../includes/menu-lateral-preceptor.php'); ?>
    </header>
    <main>
        <div class="container mt-3">
            <div class="card">
                <div class="card-body">
                    <h3>Realizar Avaliação</h3>
                    <form method="post" action="processar-avaliacao.php">
                        <input type="hidden" name="idusuario" value="<?php echo isset($_GET['idusuario']) ? intval($_GET['idusuario']) : ''; ?>">

                        <?php
                        $perguntas = [
                            ["titulo" => "Planejamento do atendimento", "descricao" => "Revisa e sumariza o prontuário focalizando nas necessidades do paciente."],
                            ["titulo" => "História clínica", "descricao" => "Favorece o relato do contexto de vida do paciente e obtém dados relevantes da história clínica de maneira articulada e cronologicamente adequada."],
                            ["titulo" => "Exame clínico", "descricao" => "Respeita a privacidade e cuida do conforto do paciente, explica e orienta o paciente sobre os procedimentos a serem realizados; adota medidas de biossegurança."],
                            ["titulo" => "Formulação do problema do paciente", "descricao" => "Integra e organiza os dados da história e exame clínicos, elaborando hipóteses diagnósticas fundamentadas nos processos de produção da doença."],
                            ["titulo" => "Investigação diagnóstica", "descricao" => "Solicita e interpreta recursos complementares para confirmar ou afastar as hipóteses elaboradas (exames, visita domiciliária, obtenção de dados com familiares, cuidador ou outros profissionais."],
                            ["titulo" => "Plano de cuidado", "descricao" => "Elabora um plano de cuidado e terapêutico considerando as evidências encontradas na literatura e o contexto de vida do paciente; envolve outros profissionais ou recursos comunitários quando necessário; contempla ações de prevenção das doenças; considera o grau de resolutividade dos diferentes serviços de atenção à saúde ao referenciar/contra-referenciar o paciente."],
                            ["titulo" => "Comunicação, organização e registro de informações", "descricao" => "Comunica e registra informações relevantes, de forma organizada e orientada para o problema do paciente."],
                            ["titulo" => "Relacionamento interpessoal", "descricao" => "Mantém comunicação respeitosa com o paciente, com sua família e acompanhante, relacionando-se de maneira empática; estabelece relação de colaboração com colegas e/ou membros de equipe; faz e recebe críticas respeitosamente."],
                            ["titulo" => "Qualidade do cuidado", "descricao" => "Avalia indicadores de qualidade do serviço de saúde no qual participa (média de permanência, taxa de infecção hospitalar, etc) e propõe ações de melhoria."],
                            ["titulo" => "Atitude profissional", "descricao" => "Mostra assiduidade e responsabilidade no cumprimento das tarefas; respeita normas institucionais, posiciona-se ética e humanisticamente em sua prática profissional, considerando, entre outros, valores de justiça, equidade e diversidade cultural e religiosa. Utiliza avaliação crítica do conhecimento. Usa estratégias adequadas ao preenchimento de suas lacunas de conhecimento. Faz auto avaliação."],
                        ];
                        ?>

                        <?php foreach ($perguntas as $index => $pergunta): ?>
                            <fieldset class="mb-4">
                                <legend><?php echo htmlspecialchars($pergunta['titulo']); ?></legend>
                                <p><?php echo htmlspecialchars($pergunta['descricao']); ?></p>
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

                        <button type="submit" class="btn btn-primary">Enviar Avaliação</button>
                    </form>
                </div>
            </div>
    </main>
    <footer>
        <div class="card footer-home rounded-0">
            <div class="card-body"></div>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
