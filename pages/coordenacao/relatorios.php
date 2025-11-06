<?php
session_start();
if (empty($_SESSION["login"])) {
    echo "<script>location.href='../../index.php';</script>";
    exit();
}

include('../../cfg/config.php');

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatorios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        /* Estilos para impressão */
        @media print {
            .no-print {
                display: none !important;
            }
            
            header, footer, .sidebar {
                display: none !important;
            }
            
            .content {
                margin: 0 !important;
                padding: 20px !important;
            }
            
            .card {
                border: none !important;
                box-shadow: none !important;
            }
            
            .card-header {
                background-color: #f8f9fa !important;
                border-bottom: 2px solid #000 !important;
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
            
            table {
                width: 100%;
                border-collapse: collapse;
            }
            
            table, th, td {
                border: 1px solid #000 !important;
            }
            
            th {
                background-color: #e9ecef !important;
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
            
            .table-success {
                background-color: #d4edda !important;
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
            
            .table-danger {
                background-color: #f8d7da !important;
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
            
            .badge {
                border: 1px solid #000;
                padding: 2px 5px;
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
            
            .bg-success {
                background-color: #28a745 !important;
                color: white !important;
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
            
            .bg-danger {
                background-color: #dc3545 !important;
                color: white !important;
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
        }
        
        /* Estilos para tela */
        .relatorio-container {
            margin-bottom: 20px;
        }
        
        .btn-group .btn {
            margin-right: 5px;
        }
        
        .print-only {
            display: none;
        }
        
        @media print {
            .print-only {
                display: block !important;
                text-align: center;
                margin-bottom: 20px;
                border-bottom: 2px solid #000;
                padding-bottom: 10px;
            }
            
            .print-header {
                font-size: 18px;
                font-weight: bold;
                margin-bottom: 5px;
            }
            
            .print-date {
                font-size: 12px;
                color: #666;
            }
        }
    </style>
</head>

<body>
    <header>
        <?php include('../../includes/navbar.php'); ?>
        <?php include('../../includes/menu-lateral-coordenacao.php'); ?>
    </header>
    <main>
        <div class="container-fluid content">
            <div class="row">
                <div class="col-12">
                    <h2 class="mb-4 no-print">Relatórios</h2>
                    
                    <!-- Seletor de tipo de relatório -->
                    <div class="card mb-4 no-print">
                        <div class="card-body">
                            <h5 class="card-title">Selecione o tipo de relatório</h5>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-primary" onclick="mostrarRelatorio('notas', this)">
                                    <i class="bi bi-file-earmark-text"></i> Notas dos Alunos
                                </button>
                                <button type="button" class="btn btn-outline-primary" onclick="mostrarRelatorio('horarios', this)">
                                    <i class="bi bi-calendar3"></i> Horários por Grupos
                                </button>
                                <button type="button" class="btn btn-outline-primary" onclick="mostrarRelatorio('avaliacoes', this)">
                                    <i class="bi bi-clipboard-check"></i> Avaliações 
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Relatório de Notas dos Alunos -->
                    <div id="relatorio-notas" class="relatorio-container" style="display: none;">
                        <div class="card">
                            <div class="print-only">
                                <div class="print-header">Sistema de Gestão de Internato Médico</div>
                                <div class="print-header">Relatório de Notas dos Alunos</div>
                                <div class="print-date">Data de Impressão: <?php echo date('d/m/Y H:i:s'); ?></div>
                            </div>
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Relatório de Notas dos Alunos</h5>
                                <button class="btn btn-success no-print" onclick="imprimirRelatorio()">
                                    <i class="bi bi-printer"></i> Imprimir
                                </button>
                            </div>
                            <div class="card-body">
                                <!-- Filtros -->
                                <div class="row mb-3 no-print">
                                    <div class="col-md-6">
                                        <label class="form-label">Pesquisar por Nome:</label>
                                        <input type="text" id="filtro-nome-notas" class="form-control" placeholder="Digite o nome do aluno...">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Filtrar por Grupo:</label>
                                        <select id="filtro-grupo-notas" class="form-select">
                                            <option value="">Todos os Grupos</option>
                                            <?php
                                            $sql_grupos = "SELECT DISTINCT g.nome_grupo FROM grupos g ORDER BY g.nome_grupo";
                                            $result_grupos = $conn->query($sql_grupos);
                                            if ($result_grupos) {
                                                while ($grupo = $result_grupos->fetch_assoc()) {
                                                    echo "<option value='" . htmlspecialchars($grupo['nome_grupo']) . "'>" . htmlspecialchars($grupo['nome_grupo']) . "</option>";
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                
                                <?php
                                $sql_notas = "SELECT 
                                    u.nome AS aluno,
                                    u.registro AS matricula,
                                    m.nome_modulo AS modulo,
                                    g.nome_grupo AS grupo,
                                    sg.nome_subgrupo AS subgrupo,
                                    AVG(a.nota) AS media_nota,
                                    COUNT(a.idavaliacao) AS total_avaliacoes
                                FROM usuarios u
                                LEFT JOIN avaliacoes a ON u.idusuario = a.idaluno
                                LEFT JOIN modulos m ON a.idmodulo = m.idmodulo
                                LEFT JOIN alunos_subgrupos asg ON u.idusuario = asg.idusuario
                                LEFT JOIN subgrupos sg ON asg.idsubgrupo = sg.idsubgrupo
                                LEFT JOIN grupos g ON sg.idgrupo = g.idgrupo
                                WHERE u.tipo = 0
                                GROUP BY u.idusuario, m.idmodulo, g.idgrupo, sg.idsubgrupo
                                ORDER BY u.nome, m.nome_modulo";
                                
                                $result_notas = $conn->query($sql_notas);
                                ?>
                                
                                <table id="tabela-notas" class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Matrícula</th>
                                            <th>Aluno</th>
                                            <th>Módulo</th>
                                            <th>Grupo</th>
                                            <th>Subgrupo</th>
                                            <th>Média</th>
                                            <th>Avaliações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if ($result_notas && $result_notas->num_rows > 0) {
                                            while ($row = $result_notas->fetch_assoc()) {
                                                $media = $row['media_nota'] ? number_format($row['media_nota'], 2) : 'Não Possui';
                                                echo "<tr>";
                                                echo "<td>" . htmlspecialchars($row['matricula']) . "</td>";
                                                echo "<td>" . htmlspecialchars($row['aluno']) . "</td>";
                                                echo "<td>" . htmlspecialchars($row['modulo'] ?? 'Não Possui') . "</td>";
                                                echo "<td>" . htmlspecialchars($row['grupo'] ?? 'Não Possui') . "</td>";
                                                echo "<td>" . htmlspecialchars($row['subgrupo'] ?? 'Não Possui') . "</td>";
                                                echo "<td>" . $media . "</td>";
                                                echo "<td>" . $row['total_avaliacoes'] . "</td>";
                                                echo "</tr>";
                                            }
                                        } else {
                                            echo "<tr><td colspan='7' class='text-center'>Nenhum dado encontrado</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Relatório de Horários por Grupos -->
                    <div id="relatorio-horarios" class="relatorio-container" style="display: none;">
                        <div class="card">
                            <div class="print-only">
                                <div class="print-header">Sistema de Gestão de Internato Médico</div>
                                <div class="print-header">Relatório de Horários por Grupos</div>
                                <div class="print-date">Data de Impressão: <?php echo date('d/m/Y H:i:s'); ?></div>
                            </div>
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Relatório de Horários por Grupos</h5>
                                <button class="btn btn-success no-print" onclick="imprimirRelatorio()">
                                    <i class="bi bi-printer"></i> Imprimir
                                </button>
                            </div>
                            <div class="card-body">
                                <!-- Filtros -->
                                <div class="row mb-3 no-print">
                                    <div class="col-md-6">
                                        <label class="form-label">Filtrar por Grupo:</label>
                                        <select id="filtro-grupo-horarios" class="form-select">
                                            <option value="">Todos os Grupos</option>
                                            <?php
                                            $sql_grupos_h = "SELECT DISTINCT g.nome_grupo FROM grupos g ORDER BY g.nome_grupo";
                                            $result_grupos_h = $conn->query($sql_grupos_h);
                                            if ($result_grupos_h) {
                                                while ($grupo = $result_grupos_h->fetch_assoc()) {
                                                    echo "<option value='" . htmlspecialchars($grupo['nome_grupo']) . "'>" . htmlspecialchars($grupo['nome_grupo']) . "</option>";
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Filtrar por Dia:</label>
                                        <select id="filtro-dia-horarios" class="form-select">
                                            <option value="">Todos os Dias</option>
                                            <option value="Segunda">Segunda</option>
                                            <option value="Terça">Terça</option>
                                            <option value="Quarta">Quarta</option>
                                            <option value="Quinta">Quinta</option>
                                            <option value="Sexta">Sexta</option>
                                            <option value="Sábado">Sábado</option>
                                            <option value="Domingo">Domingo</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <?php
                                $sql_horarios = "SELECT 
                                    m.nome_modulo AS modulo,
                                    g.nome_grupo AS grupo,
                                    sg.nome_subgrupo AS subgrupo,
                                    u.nome AS preceptor,
                                    h.dia_semana,
                                    h.hora_inicio,
                                    h.hora_fim,
                                    un.nome_unidade AS local
                                FROM horarios h
                                INNER JOIN usuarios u ON h.idpreceptor = u.idusuario
                                INNER JOIN subgrupos sg ON h.idsubgrupo = sg.idsubgrupo
                                INNER JOIN grupos g ON sg.idgrupo = g.idgrupo
                                INNER JOIN modulos m ON h.idmodulo = m.idmodulo
                                LEFT JOIN unidades un ON h.idunidade = un.idunidade
                                WHERE u.tipo = 1
                                ORDER BY m.nome_modulo, g.nome_grupo, sg.nome_subgrupo, h.dia_semana, h.hora_inicio";
                                
                                $result_horarios = $conn->query($sql_horarios);
                                
                                $horarios_por_grupo = [];
                                if ($result_horarios && $result_horarios->num_rows > 0) {
                                    while ($row = $result_horarios->fetch_assoc()) {
                                        $chave = $row['modulo'] . ' - ' . $row['grupo'];
                                        if (!isset($horarios_por_grupo[$chave])) {
                                            $horarios_por_grupo[$chave] = [];
                                        }
                                        $horarios_por_grupo[$chave][] = $row;
                                    }
                                }
                                ?>
                                
                                <?php if (count($horarios_por_grupo) > 0): ?>
                                    <?php foreach ($horarios_por_grupo as $grupo => $horarios): ?>
                                        <h6 class="mt-4 mb-3"><strong><?php echo htmlspecialchars($grupo); ?></strong></h6>
                                        <table class="table table-bordered table-sm mb-4">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Subgrupo</th>
                                                    <th>Preceptor</th>
                                                    <th>Dia</th>
                                                    <th>Horário</th>
                                                    <th>Local</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($horarios as $h): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($h['subgrupo']); ?></td>
                                                        <td><?php echo htmlspecialchars($h['preceptor']); ?></td>
                                                        <td><?php echo htmlspecialchars($h['dia_semana']); ?></td>
                                                        <td><?php echo htmlspecialchars($h['hora_inicio'] . ' - ' . $h['hora_fim']); ?></td>
                                                        <td><?php echo htmlspecialchars($h['local'] ?? 'Não Possui'); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-center">Nenhum horário encontrado</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Relatório de Avaliações (Positivas/Negativas) -->
                    <div id="relatorio-avaliacoes" class="relatorio-container" style="display: none;">
                        <div class="card">
                            <div class="print-only">
                                <div class="print-header">Sistema de Gestão de Internato Médico</div>
                                <div class="print-header">Relatório de Avaliações </div>
                                <div class="print-date">Data de Impressão: <?php echo date('d/m/Y H:i:s'); ?></div>
                            </div>
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Relatório de Avaliações </h5>
                                <button class="btn btn-success no-print" onclick="imprimirRelatorio()">
                                    <i class="bi bi-printer"></i> Imprimir
                                </button>
                            </div>
                            <div class="card-body">
                                <!-- Filtros -->
                                <div class="row mb-3 no-print">
                                    <div class="col-md-6">
                                        <label class="form-label">Pesquisar por Nome:</label>
                                        <input type="text" id="filtro-nome-avaliacoes" class="form-control" placeholder="Digite o nome do aluno...">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Filtrar por Desempenho:</label>
                                        <select id="filtro-desempenho-avaliacoes" class="form-select">
                                            <option value="">Todas as Notas</option>
                                            <option value="acima">Na Média ou Acima (≥ 7)</option>
                                            <option value="abaixo">Abaixo da Média (< 7)</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <?php
                                $sql_avaliacoes = "SELECT 
                                    a.idavaliacao,
                                    u.nome AS aluno,
                                    u.registro AS matricula,
                                    p.nome AS preceptor,
                                    m.nome_modulo AS modulo,
                                    g.nome_grupo AS grupo,
                                    sg.nome_subgrupo AS subgrupo,
                                    a.nota,
                                    a.data_avaliacao
                                FROM avaliacoes a
                                INNER JOIN usuarios u ON a.idaluno = u.idusuario
                                INNER JOIN usuarios p ON a.idpreceptor = p.idusuario
                                INNER JOIN modulos m ON a.idmodulo = m.idmodulo
                                LEFT JOIN alunos_subgrupos asg ON u.idusuario = asg.idusuario
                                LEFT JOIN subgrupos sg ON asg.idsubgrupo = sg.idsubgrupo
                                LEFT JOIN grupos g ON sg.idgrupo = g.idgrupo
                                WHERE u.tipo = 0
                                ORDER BY a.nota DESC, u.nome, a.data_avaliacao DESC";
                                
                                $result_avaliacoes = $conn->query($sql_avaliacoes);
                                ?>
                                
                                <table id="tabela-avaliacoes" class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Matrícula</th>
                                            <th>Aluno</th>
                                            <th>Preceptor</th>
                                            <th>Módulo</th>
                                            <th>Grupo/Subgrupo</th>
                                            <th>Nota</th>
                                            <th>Data</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if ($result_avaliacoes && $result_avaliacoes->num_rows > 0) {
                                            while ($row = $result_avaliacoes->fetch_assoc()) {
                                                $nota = $row['nota'];
                                                $classe_nota = $nota >= 7 ? 'table-success' : 'table-danger';
                                                $badge_nota = $nota >= 7 ? 
                                                    '<span class="badge bg-success">' . number_format($nota, 2) . '</span>' : 
                                                    '<span class="badge bg-danger">' . number_format($nota, 2) . '</span>';
                                                
                                                echo "<tr class='$classe_nota'>";
                                                echo "<td>" . htmlspecialchars($row['matricula']) . "</td>";
                                                echo "<td>" . htmlspecialchars($row['aluno']) . "</td>";
                                                echo "<td>" . htmlspecialchars($row['preceptor']) . "</td>";
                                                echo "<td>" . htmlspecialchars($row['modulo'] ?? 'Não Possui') . "</td>";
                                                echo "<td>" . htmlspecialchars(($row['grupo'] ?? 'Não Possui') . ' / ' . ($row['subgrupo'] ?? 'Não Possui')) . "</td>";
                                                echo "<td>" . $badge_nota . "</td>";
                                                echo "<td>" . date('d/m/Y', strtotime($row['data_avaliacao'])) . "</td>";
                                                echo "</tr>";
                                            }
                                        } else {
                                            echo "<tr><td colspan='7' class='text-center'>Nenhuma avaliação encontrada</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </main>
    <footer>
        <div class="card footer-home rounded-0">
            <div class="card-body">
            </div>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>
        // Função para mostrar o relatório selecionado
        function mostrarRelatorio(tipo, btnElement) {
            console.log('Função mostrarRelatorio chamada com tipo:', tipo);
            
            // Ocultar todos os relatórios
            const relatorios = document.querySelectorAll('.relatorio-container');
            console.log('Relatórios encontrados:', relatorios.length);
            relatorios.forEach(rel => {
                rel.style.display = 'none';
            });
            
            // Remover classe ativa de todos os botões
            const botoes = document.querySelectorAll('.btn-group .btn');
            botoes.forEach(btn => {
                btn.classList.remove('active');
                btn.classList.remove('btn-primary');
                btn.classList.add('btn-outline-primary');
            });
            
            // Mostrar o relatório selecionado
            const relatorioId = 'relatorio-' + tipo;
            console.log('Procurando relatório com ID:', relatorioId);
            const relatorio = document.getElementById(relatorioId);
            if (relatorio) {
                console.log('Relatório encontrado, exibindo...');
                relatorio.style.display = 'block';
            } else {
                console.error('Relatório não encontrado!');
            }
            
            // Ativar o botão selecionado
            if (btnElement) {
                btnElement.classList.remove('btn-outline-primary');
                btnElement.classList.add('btn-primary');
                btnElement.classList.add('active');
            }
        }
        
        // Função para imprimir o relatório
        function imprimirRelatorio() {
            window.print();
        }
        
        // Verificar se a página carregou corretamente
        window.addEventListener('DOMContentLoaded', (event) => {
            console.log('Página carregada!');
            const relatorios = document.querySelectorAll('.relatorio-container');
            console.log('Total de relatórios na página:', relatorios.length);
            relatorios.forEach((rel, index) => {
                console.log('Relatório ' + index + ':', rel.id);
            });
            
            // Configurar filtros do relatório de notas
            configurarFiltrosNotas();
            
            // Configurar filtros do relatório de horários
            configurarFiltrosHorarios();
            
            // Configurar filtros do relatório de avaliações
            configurarFiltrosAvaliacoes();
        });
        
        // Filtros para Relatório de Notas
        function configurarFiltrosNotas() {
            const filtroNome = document.getElementById('filtro-nome-notas');
            const filtroGrupo = document.getElementById('filtro-grupo-notas');
            
            if (filtroNome) {
                filtroNome.addEventListener('keyup', filtrarTabelaNotas);
            }
            if (filtroGrupo) {
                filtroGrupo.addEventListener('change', filtrarTabelaNotas);
            }
        }
        
        function filtrarTabelaNotas() {
            const filtroNome = document.getElementById('filtro-nome-notas').value.toLowerCase();
            const filtroGrupo = document.getElementById('filtro-grupo-notas').value.toLowerCase();
            const tabela = document.getElementById('tabela-notas');
            const linhas = tabela.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
            
            for (let i = 0; i < linhas.length; i++) {
                const colunas = linhas[i].getElementsByTagName('td');
                if (colunas.length > 0) {
                    const nome = colunas[1].textContent.toLowerCase();
                    const grupo = colunas[3].textContent.toLowerCase().trim();
                    
                    // Busca por nome que COMEÇA com o texto digitado
                    const matchNome = filtroNome === '' || nome.startsWith(filtroNome);
                    // Busca por grupo EXATO (ignora "Não Possui")
                    const matchGrupo = filtroGrupo === '' || (grupo !== 'não possui' && grupo === filtroGrupo);
                    
                    if (matchNome && matchGrupo) {
                        linhas[i].style.display = '';
                    } else {
                        linhas[i].style.display = 'none';
                    }
                }
            }
        }
        
        // Filtros para Relatório de Horários
        function configurarFiltrosHorarios() {
            const filtroGrupo = document.getElementById('filtro-grupo-horarios');
            const filtroDia = document.getElementById('filtro-dia-horarios');
            
            if (filtroGrupo) {
                filtroGrupo.addEventListener('change', filtrarHorarios);
            }
            if (filtroDia) {
                filtroDia.addEventListener('change', filtrarHorarios);
            }
        }
        
        function filtrarHorarios() {
            const filtroGrupo = document.getElementById('filtro-grupo-horarios').value.toLowerCase().trim();
            const filtroDia = document.getElementById('filtro-dia-horarios').value.toLowerCase();
            const container = document.getElementById('relatorio-horarios');
            const tabelas = container.querySelectorAll('table.table-bordered');
            const titulos = container.querySelectorAll('h6');
            
            titulos.forEach((titulo, index) => {
                const tituloTexto = titulo.textContent.toLowerCase().trim();
                const tabela = tabelas[index];
                
                // Extrair apenas o nome do grupo do título (ex: "clínica médica - a" -> "a")
                // O formato é: "Nome do Módulo - Grupo"
                const partesTitulo = tituloTexto.split(' - ');
                const grupoNoTitulo = partesTitulo.length > 1 ? partesTitulo[partesTitulo.length - 1].trim() : '';
                
                // Verificar se o grupo do título é EXATAMENTE o grupo filtrado
                const matchGrupo = filtroGrupo === '' || grupoNoTitulo === filtroGrupo;
                
                if (matchGrupo) {
                    // Mostrar o título
                    titulo.style.display = '';
                    
                    // Filtrar linhas da tabela por dia
                    if (tabela) {
                        const linhas = tabela.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
                        let algumVisivel = false;
                        
                        for (let i = 0; i < linhas.length; i++) {
                            const colunas = linhas[i].getElementsByTagName('td');
                            if (colunas.length > 0) {
                                const dia = colunas[2].textContent.toLowerCase();
                                const matchDia = filtroDia === '' || dia.includes(filtroDia);
                                
                                if (matchDia) {
                                    linhas[i].style.display = '';
                                    algumVisivel = true;
                                } else {
                                    linhas[i].style.display = 'none';
                                }
                            }
                        }
                        
                        // Se nenhuma linha está visível, ocultar a tabela e título
                        if (!algumVisivel) {
                            tabela.style.display = 'none';
                            titulo.style.display = 'none';
                        } else {
                            tabela.style.display = '';
                        }
                    }
                } else {
                    // Ocultar título e tabela se não corresponder ao grupo
                    titulo.style.display = 'none';
                    if (tabela) {
                        tabela.style.display = 'none';
                    }
                }
            });
        }
        
        // Filtros para Relatório de Avaliações
        function configurarFiltrosAvaliacoes() {
            const filtroNome = document.getElementById('filtro-nome-avaliacoes');
            const filtroDesempenho = document.getElementById('filtro-desempenho-avaliacoes');
            
            if (filtroNome) {
                filtroNome.addEventListener('keyup', filtrarTabelaAvaliacoes);
            }
            if (filtroDesempenho) {
                filtroDesempenho.addEventListener('change', filtrarTabelaAvaliacoes);
            }
        }
        
        function filtrarTabelaAvaliacoes() {
            const filtroNome = document.getElementById('filtro-nome-avaliacoes').value.toLowerCase();
            const filtroDesempenho = document.getElementById('filtro-desempenho-avaliacoes').value;
            const tabela = document.getElementById('tabela-avaliacoes');
            const linhas = tabela.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
            
            for (let i = 0; i < linhas.length; i++) {
                const colunas = linhas[i].getElementsByTagName('td');
                if (colunas.length > 0) {
                    const nome = colunas[1].textContent.toLowerCase();
                    const notaTexto = colunas[5].textContent.trim();
                    const nota = parseFloat(notaTexto);
                    
                    // Busca por nome que COMEÇA com o texto digitado
                    const matchNome = filtroNome === '' || nome.startsWith(filtroNome);
                    let matchDesempenho = true;
                    
                    if (filtroDesempenho === 'acima') {
                        matchDesempenho = nota >= 7;
                    } else if (filtroDesempenho === 'abaixo') {
                        matchDesempenho = nota < 7;
                    }
                    
                    if (matchNome && matchDesempenho) {
                        linhas[i].style.display = '';
                    } else {
                        linhas[i].style.display = 'none';
                    }
                }
            }
        }
    </script>
</body>

</html>