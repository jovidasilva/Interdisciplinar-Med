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
    <title>Lista de alunos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../css/style.css">
</head>

<body>
    <header>
        <?php include('../../includes/navbar.php'); ?>
        <?php include('../../includes/menu-lateral-preceptor.php'); ?>
    </header>
    <main>
        <div class="container mt-3">
            <div class="card">
                <div class="card-body">
                    <h3>Lista de Alunos</h3>
                    <?php
                    $idpreceptor = $_SESSION['idusuario'] ?? null;
                    $alunos = [];
                    $modulosFiltro = [];
                    $subgruposFiltro = [];
                    if ($idpreceptor) {
                        $sql = "SELECT u.nome, u.idusuario, u.registro, sg.nome_subgrupo, m.nome_modulo, m.idmodulo
                                FROM usuarios u
                                JOIN alunos_subgrupos alsg ON u.idusuario = alsg.idusuario
                                JOIN subgrupos sg ON alsg.idsubgrupo = sg.idsubgrupo
                                JOIN horarios h ON sg.idsubgrupo = h.idsubgrupo
                                JOIN modulos m ON h.idmodulo = m.idmodulo
                                WHERE h.idpreceptor = ? AND u.tipo = 0
                                ORDER BY u.nome";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $idpreceptor);
                        if ($stmt->execute()) {
                            $res = $stmt->get_result();
                            $alunosMap = [];
                            while ($row = $res->fetch_assoc()) {
                                $idAluno = $row['idusuario'];
                                $idModulo = $row['idmodulo'];
                                if (!isset($alunosMap[$idAluno])) {
                                    $alunosMap[$idAluno] = [
                                        'idusuario' => $idAluno,
                                        'nome' => $row['nome'],
                                        'registro' => $row['registro'],
                                        'nomeSubgrupo' => $row['nome_subgrupo'],
                                        'modulosLista' => []
                                    ];
                                } else {
                                    // acrescenta subgrupo se diferente
                                    if (strpos($alunosMap[$idAluno]['nomeSubgrupo'], $row['nome_subgrupo']) === false) {
                                        $alunosMap[$idAluno]['nomeSubgrupo'] .= ', ' . $row['nome_subgrupo'];
                                    }
                                }
                                $subgruposFiltro[$row['nome_subgrupo']] = true;
                                // última nota para módulo
                                $nota = null;
                                $stmtNota = $conn->prepare("SELECT nota FROM avaliacoes WHERE idaluno = ? AND idpreceptor = ? AND idmodulo = ? ORDER BY data_avaliacao DESC LIMIT 1");
                                $stmtNota->bind_param("iii", $idAluno, $idpreceptor, $idModulo);
                                $stmtNota->execute();
                                $stmtNota->bind_result($nota);
                                if (!$stmtNota->fetch()) { $nota = null; }
                                $stmtNota->close();

                                $moduloNome = $row['nome_modulo'];
                                $modulosFiltro[$moduloNome] = true;
                                $repr = $nota !== null ? "$moduloNome (" . $nota . ")" : "$moduloNome (Sem nota)";
                                if (!in_array($repr, $alunosMap[$idAluno]['modulosLista'])) {
                                    $alunosMap[$idAluno]['modulosLista'][] = $repr;
                                }
                            }
                            // complementar módulos via avaliações e horários (versão simplificada): já contemplado acima.
                            // preparar output
                            foreach ($alunosMap as $al) {
                                $al['modulosTexto'] = implode(', ', $al['modulosLista']);
                                $al['modulosHtml'] = implode('<br/>', $al['modulosLista']);
                                $alunos[] = $al;
                            }
                        }
                        $stmt->close();
                    }
                    ?>
                    <!-- Filtros -->
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-body py-3">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold"><i class="bi bi-filter"></i> Módulo</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text"><i class="bi bi-journal-text"></i></span>
                                        <select id="filtroModulo" class="form-select">
                                            <option value="">Todos</option>
                                            <?php foreach (array_keys($modulosFiltro) as $m) {
                                                echo '<option value="' . htmlspecialchars($m) . '">' . htmlspecialchars($m) . '</option>'; }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold"><i class="bi bi-filter"></i> Subgrupo</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text"><i class="bi bi-people"></i></span>
                                        <select id="filtroSubgrupo" class="form-select">
                                            <option value="">Todos</option>
                                            <?php foreach (array_keys($subgruposFiltro) as $s) {
                                                echo '<option value="' . htmlspecialchars($s) . '">' . htmlspecialchars($s) . '</option>'; }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <table id="tabelaAlunos" class="table table-striped table-secondary table-bordered">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Subgrupos</th>
                                <th>Módulos (nota)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($alunos)) {
                                foreach ($alunos as $al) { ?>
                                    <tr data-modulos="<?= htmlspecialchars(strtolower($al['modulosTexto'])) ?>" data-subgrupo="<?= htmlspecialchars(strtolower($al['nomeSubgrupo'])) ?>">
                                        <td><?= htmlspecialchars($al['nome']) ?></td>
                                        <td><?= htmlspecialchars($al['nomeSubgrupo']) ?></td>
                                        <td><?= $al['modulosHtml'] ?></td>
                                    </tr>
                            <?php } } else { echo '<tr><td colspan="3">Nenhum aluno encontrado.</td></tr>'; } ?>
                        </tbody>
                    </table>
                    <script>
                        document.addEventListener('DOMContentLoaded', () => {
                            const selModulo = document.getElementById('filtroModulo');
                            const selSub = document.getElementById('filtroSubgrupo');
                            const rows = document.querySelectorAll('#tabelaAlunos tbody tr[data-modulos]');
                            
                            // Armazena conteúdo original das células de módulos
                            const originalModulos = new Map();
                            rows.forEach(r => {
                                const modulosCell = r.cells[2];
                                originalModulos.set(r, modulosCell.innerHTML);
                            });
                            
                            const applyFilter = () => {
                                const mod = selModulo.value.toLowerCase();
                                const sub = selSub.value.toLowerCase();
                                
                                rows.forEach(r => {
                                    const rowMods = r.getAttribute('data-modulos');
                                    const rowSub = r.getAttribute('data-subgrupo');
                                    const okMod = !mod || rowMods.includes(mod);
                                    const okSub = !sub || rowSub.includes(sub);
                                    r.style.display = okMod && okSub ? '' : 'none';
                                    
                                    // Atualiza coluna de módulos se filtro de módulo estiver ativo
                                    const modulosCell = r.cells[2];
                                    if (mod) {
                                        // Filtra apenas o módulo selecionado
                                        const originalHtml = originalModulos.get(r);
                                        // Divide por <br> ou <br/>
                                        const lines = originalHtml.split(/<br\s*\/?>/i);
                                        const filtered = lines.filter(line => 
                                            line.trim() && line.toLowerCase().includes(mod)
                                        );
                                        modulosCell.innerHTML = filtered.length > 0 ? filtered.join('<br>') : originalHtml;
                                    } else {
                                        // Restaura todos os módulos
                                        modulosCell.innerHTML = originalModulos.get(r);
                                    }
                                });
                            };
                            
                            selModulo.addEventListener('change', applyFilter);
                            selSub.addEventListener('change', applyFilter);
                        });
                    </script>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>

</html>
