<?php
session_start();
if (empty($_SESSION["login"])) {
    echo "<script>location.href='../../../index.php';</script>";
    exit();
}
include('../../../cfg/config.php');

$idsubgrupo = isset($_GET['idsubgrupo']) ? intval($_GET['idsubgrupo']) : 0;

if (!$idsubgrupo) {
    echo "<script>alert('Subgrupo inválido.'); history.back();</script>";
    exit();
}

// Busca informações do subgrupo
$sqlSubgrupo = "SELECT sg.nome_subgrupo, g.nome_grupo FROM subgrupos sg JOIN grupos g ON sg.idgrupo = g.idgrupo WHERE sg.idsubgrupo = ?";
$stmtSub = $conn->prepare($sqlSubgrupo);
$stmtSub->bind_param("i", $idsubgrupo);
$stmtSub->execute();
$stmtSub->bind_result($nomeSubgrupo, $nomeGrupo);
$stmtSub->fetch();
$stmtSub->close();

// Calcula capacidade ideal do subgrupo
// Total de alunos dividido por 9 subgrupos (3 grupos x 3 subgrupos)
$sqlTotalAlunos = "SELECT COUNT(*) as total FROM usuarios WHERE tipo = 0";
$resTotalAlunos = $conn->query($sqlTotalAlunos);
$totalAlunos = $resTotalAlunos->fetch_assoc()['total'];
$capacidadeIdeal = ceil($totalAlunos / 9); // Capacidade por subgrupo
$limiteMaximo = $capacidadeIdeal + 2; // Permite 2 alunos a mais que a média

// Busca alunos já alocados neste subgrupo
$alunosAlocados = [];
$sqlAlocados = "SELECT u.idusuario, u.nome, u.registro FROM usuarios u JOIN alunos_subgrupos als ON u.idusuario = als.idusuario WHERE als.idsubgrupo = ? ORDER BY u.nome";
$stmtAloc = $conn->prepare($sqlAlocados);
$stmtAloc->bind_param("i", $idsubgrupo);
$stmtAloc->execute();
$resAloc = $stmtAloc->get_result();
while ($row = $resAloc->fetch_assoc()) {
    $alunosAlocados[] = $row;
}
$stmtAloc->close();

$alunosAtuais = count($alunosAlocados);
$percentualOcupacao = $limiteMaximo > 0 ? round(($alunosAtuais / $limiteMaximo) * 100) : 0;

// Busca alunos disponíveis (tipo 0) que NÃO estão em NENHUM subgrupo
$alunosDisponiveis = [];
$sqlDisp = "SELECT u.idusuario, u.nome, u.registro 
            FROM usuarios u 
            WHERE u.tipo = 0 
            AND u.idusuario NOT IN (SELECT idusuario FROM alunos_subgrupos)
            ORDER BY u.nome";
$resDisp = $conn->query($sqlDisp);
while ($row = $resDisp->fetch_assoc()) {
    $alunosDisponiveis[] = $row;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alocar Alunos - <?= htmlspecialchars($nomeSubgrupo) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../../css/style.css">
</head>
<body>
    <header>
        <?php include('../../../includes/navbar.php'); ?>
        <?php include('../../../includes/menu-lateral-coordenacao.php'); ?>
    </header>
    <main>
        <div class="container mt-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3>Alocar Alunos - Grupo <?= htmlspecialchars($nomeGrupo) ?> - Subgrupo <?= htmlspecialchars($nomeSubgrupo) ?></h3>
                        <a href="grupos.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Voltar</a>
                    </div>

                    <!-- Informações de Capacidade -->
                    <div class="alert <?= $alunosAtuais > $limiteMaximo ? 'alert-danger' : ($alunosAtuais >= $capacidadeIdeal ? 'alert-warning' : 'alert-info') ?> mb-3">
                        <div class="row">
                            <div class="col-md-8">
                                <strong><i class="bi bi-info-circle"></i> Capacidade deste Subgrupo:</strong>
                                <div class="mt-2">
                                    <span class="me-3">
                                        <i class="bi bi-people-fill"></i> <strong>Alunos Atuais:</strong> 
                                        <span class="badge bg-secondary fs-6"><?= $alunosAtuais ?></span>
                                    </span>
                                    <span class="me-3">
                                        <i class="bi bi-check-circle"></i> <strong>Capacidade Ideal:</strong> 
                                        <span class="badge bg-info fs-6"><?= $capacidadeIdeal ?> alunos</span>
                                    </span>
                                    <span>
                                        <i class="bi bi-exclamation-circle"></i> <strong>Limite Máximo:</strong> 
                                        <span class="badge bg-warning text-dark fs-6"><?= $limiteMaximo ?> alunos</span>
                                    </span>
                                </div>
                                <small class="text-muted mt-2 d-block">
                                    <i class="bi bi-calculator"></i> Cálculo: <?= $totalAlunos ?> alunos totais ÷ 9 subgrupos = ~<?= $capacidadeIdeal ?> alunos por subgrupo
                                </small>
                            </div>
                            <div class="col-md-4 text-end">
                                <div>
                                    <small class="text-muted">Ocupação</small>
                                </div>
                                <span class="badge <?= $percentualOcupacao >= 100 ? 'bg-danger' : ($percentualOcupacao >= 80 ? 'bg-warning text-dark' : 'bg-success') ?>" style="font-size: 1.5rem; padding: 0.5rem 1rem;">
                                    <?= $percentualOcupacao ?>%
                                </span>
                            </div>
                        </div>
                        <?php if ($alunosAtuais > $limiteMaximo): ?>
                            <hr>
                            <div class="alert alert-danger mb-0 py-2">
                                <i class="bi bi-exclamation-triangle-fill"></i> <strong>Atenção:</strong> Este subgrupo está com <strong><?= $alunosAtuais - $limiteMaximo ?> aluno(s) acima</strong> do limite máximo recomendado!
                            </div>
                        <?php elseif ($alunosAtuais >= $capacidadeIdeal): ?>
                            <hr>
                            <small><i class="bi bi-exclamation-circle"></i> Este subgrupo atingiu a capacidade ideal. Considere distribuir novos alunos em outros subgrupos para manter o equilíbrio.</small>
                        <?php endif; ?>
                    </div>

                    <div class="row">
                        <!-- Alunos Disponíveis -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0"><i class="bi bi-people"></i> Alunos Disponíveis</h5>
                                        <button type="button" class="btn btn-success btn-sm" id="btnAdicionarSelecionados" onclick="adicionarSelecionados()" disabled>
                                            <i class="bi bi-plus-circle"></i> Adicionar (<span id="countSelecionados">0</span>)
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                                    <input type="text" id="searchDisponiveis" class="form-control mb-3" placeholder="Buscar aluno...">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <?php 
                                        $vagasDisponiveis = $limiteMaximo - $alunosAtuais;
                                        if ($vagasDisponiveis > 0): ?>
                                            <div class="alert alert-info py-2 mb-0 flex-grow-1 me-2" id="infoSelecao">
                                                <small><i class="bi bi-info-circle"></i> Você pode adicionar até <strong id="vagasDisponiveis"><?= $vagasDisponiveis ?></strong> aluno(s)</small>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="selectAllDisponiveis" onchange="selecionarTodosDisponiveis()">
                                                <label class="form-check-label" for="selectAllDisponiveis">
                                                    <small><strong>Selecionar até o limite</strong></small>
                                                </label>
                                            </div>
                                        <?php else: ?>
                                            <div class="alert alert-warning py-2 mb-0 flex-grow-1" id="infoSelecao">
                                                <small><i class="bi bi-exclamation-triangle"></i> <strong>Não é possível adicionar mais alunos</strong></small>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div id="listaDisponiveis">
                                        <?php foreach ($alunosDisponiveis as $aluno): ?>
                                            <div class="d-flex align-items-center border-bottom py-2 aluno-item" data-nome="<?= strtolower($aluno['nome']) ?>">
                                                <div class="form-check">
                                                    <input class="form-check-input checkbox-disponivel" type="checkbox" 
                                                           value="<?= $aluno['idusuario'] ?>" 
                                                           id="disp_<?= $aluno['idusuario'] ?>"
                                                           data-nome="<?= htmlspecialchars($aluno['nome']) ?>"
                                                           data-registro="<?= htmlspecialchars($aluno['registro']) ?>"
                                                           <?= $vagasDisponiveis <= 0 ? 'disabled' : '' ?>
                                                           onchange="atualizarSelecaoDisponiveis()">
                                                    <label class="form-check-label" for="disp_<?= $aluno['idusuario'] ?>">
                                                        <strong><?= htmlspecialchars($aluno['nome']) ?></strong><br>
                                                        <small class="text-muted">RA: <?= htmlspecialchars($aluno['registro']) ?></small>
                                                    </label>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                        <?php if (empty($alunosDisponiveis)): ?>
                                            <div class="alert alert-info mb-0">
                                                <i class="bi bi-info-circle"></i> Todos os alunos já foram alocados em subgrupos.
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Alunos Alocados -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-success text-white">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0"><i class="bi bi-check-circle"></i> Alunos Alocados (<span id="countAlocados"><?= count($alunosAlocados) ?></span>)</h5>
                                        <button type="button" class="btn btn-danger btn-sm" id="btnRemoverSelecionados" onclick="removerSelecionados()" disabled>
                                            <i class="bi bi-trash"></i> Remover (<span id="countRemover">0</span>)
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="selectAllAlocados" onchange="selecionarTodosAlocados()">
                                        <label class="form-check-label" for="selectAllAlocados">
                                            <strong>Selecionar Todos</strong>
                                        </label>
                                    </div>
                                    <div id="listaAlocados">
                                        <?php foreach ($alunosAlocados as $aluno): ?>
                                            <div class="d-flex align-items-center border-bottom py-2" id="alocado-<?= $aluno['idusuario'] ?>">
                                                <div class="form-check">
                                                    <input class="form-check-input checkbox-alocado" type="checkbox" 
                                                           value="<?= $aluno['idusuario'] ?>" 
                                                           id="aloc_<?= $aluno['idusuario'] ?>"
                                                           data-nome="<?= htmlspecialchars($aluno['nome']) ?>"
                                                           data-registro="<?= htmlspecialchars($aluno['registro']) ?>"
                                                           onchange="atualizarSelecaoAlocados()">
                                                    <label class="form-check-label" for="aloc_<?= $aluno['idusuario'] ?>">
                                                        <strong><?= htmlspecialchars($aluno['nome']) ?></strong><br>
                                                        <small class="text-muted">RA: <?= htmlspecialchars($aluno['registro']) ?></small>
                                                    </label>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <footer>
        <div class="card footer-home rounded-0"><div class="card-body"></div></div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const idsubgrupo = <?= $idsubgrupo ?>;
        const capacidadeIdeal = <?= $capacidadeIdeal ?>;
        const limiteMaximo = <?= $limiteMaximo ?>;
        let alunosAtuais = <?= $alunosAtuais ?>;

        // Busca em tempo real
        document.getElementById('searchDisponiveis').addEventListener('input', function() {
            const search = this.value.toLowerCase();
            document.querySelectorAll('#listaDisponiveis .aluno-item').forEach(item => {
                const nome = item.dataset.nome;
                item.style.display = nome.includes(search) ? '' : 'none';
            });
        });

        // Selecionar todos disponíveis (até o limite)
        function selecionarTodosDisponiveis() {
            const selectAll = document.getElementById('selectAllDisponiveis');
            const checkboxes = document.querySelectorAll('.checkbox-disponivel:not([style*="display: none"])');
            const vagasDisponiveis = limiteMaximo - alunosAtuais;
            
            if (selectAll.checked) {
                // Marca até o limite
                let marcados = 0;
                checkboxes.forEach(cb => {
                    if (marcados < vagasDisponiveis) {
                        cb.checked = true;
                        marcados++;
                    }
                });
            } else {
                // Desmarca todos
                checkboxes.forEach(cb => cb.checked = false);
            }
            
            atualizarSelecaoDisponiveis();
        }

        // Atualiza seleção de alunos disponíveis
        function atualizarSelecaoDisponiveis() {
            const checkboxes = document.querySelectorAll('.checkbox-disponivel:checked');
            const count = checkboxes.length;
            const vagasDisponiveis = limiteMaximo - alunosAtuais;
            
            // Atualiza contador no botão
            document.getElementById('countSelecionados').textContent = count;
            document.getElementById('btnAdicionarSelecionados').disabled = count === 0;
            
            // Atualiza estado do "Selecionar Todos"
            const totalVisiveis = document.querySelectorAll('.checkbox-disponivel:not([style*="display: none"])').length;
            const selectAll = document.getElementById('selectAllDisponiveis');
            selectAll.checked = count > 0 && count >= Math.min(totalVisiveis, vagasDisponiveis);
            
            // Limita seleção ao máximo de vagas
            if (count >= vagasDisponiveis) {
                document.querySelectorAll('.checkbox-disponivel:not(:checked)').forEach(cb => {
                    cb.disabled = true;
                });
                
                if (count > vagasDisponiveis) {
                    alert(`Você só pode adicionar até ${vagasDisponiveis} aluno(s). O limite máximo do subgrupo é ${limiteMaximo}.`);
                    // Desmarca os últimos selecionados
                    Array.from(checkboxes).slice(vagasDisponiveis).forEach(cb => cb.checked = false);
                    atualizarSelecaoDisponiveis();
                    return;
                }
            } else {
                document.querySelectorAll('.checkbox-disponivel').forEach(cb => {
                    cb.disabled = false;
                });
            }
        }

        // Selecionar todos alocados
        function selecionarTodosAlocados() {
            const selectAll = document.getElementById('selectAllAlocados');
            const checkboxes = document.querySelectorAll('.checkbox-alocado');
            
            checkboxes.forEach(cb => {
                cb.checked = selectAll.checked;
            });
            
            atualizarSelecaoAlocados();
        }

        // Atualiza seleção de alunos alocados
        function atualizarSelecaoAlocados() {
            const checkboxes = document.querySelectorAll('.checkbox-alocado:checked');
            const totalCheckboxes = document.querySelectorAll('.checkbox-alocado').length;
            const count = checkboxes.length;
            
            document.getElementById('countRemover').textContent = count;
            document.getElementById('btnRemoverSelecionados').disabled = count === 0;
            
            // Atualiza estado do "Selecionar Todos"
            const selectAll = document.getElementById('selectAllAlocados');
            selectAll.checked = count > 0 && count === totalCheckboxes;
        }

        // Adicionar múltiplos alunos selecionados
        function adicionarSelecionados() {
            const checkboxes = document.querySelectorAll('.checkbox-disponivel:checked');
            if (checkboxes.length === 0) return;
            
            const alunos = Array.from(checkboxes).map(cb => ({
                id: cb.value,
                nome: cb.dataset.nome,
                registro: cb.dataset.registro
            }));
            
            const ids = alunos.map(a => a.id).join(',');
            
            fetch('processar-alocacao.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=alocar_multiplos&idusuarios=${ids}&idsubgrupo=${idsubgrupo}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload(); // Reload para atualizar interface
                } else {
                    alert('Erro ao alocar alunos: ' + data.message);
                }
            });
        }

        // Remover múltiplos alunos selecionados
        function removerSelecionados() {
            const checkboxes = document.querySelectorAll('.checkbox-alocado:checked');
            if (checkboxes.length === 0) return;
            
            const alunos = Array.from(checkboxes).map(cb => ({
                id: cb.value,
                nome: cb.dataset.nome
            }));
            
            if (!confirm(`Remover ${alunos.length} aluno(s) deste subgrupo?`)) return;
            
            const ids = alunos.map(a => a.id).join(',');
            
            fetch('processar-alocacao.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=remover_multiplos&idusuarios=${ids}&idsubgrupo=${idsubgrupo}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload(); // Reload para atualizar interface
                } else {
                    alert('Erro ao remover alunos: ' + data.message);
                }
            });
        }
    </script>
</body>
</html>
