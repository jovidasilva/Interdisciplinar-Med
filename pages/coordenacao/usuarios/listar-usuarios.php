<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['login']) || !in_array($_SESSION['tipo'] ?? null, [2, 3], true)) {
    header('Location: ' . str_repeat('../', 3) . 'index.php');
    exit();
}
if (!isset($conn)) {
    require_once __DIR__ . '/' . str_repeat('../', 3) . 'cfg/config.php';
}
require_once __DIR__ . '/' . str_repeat('../', 3) . 'includes/csrf.php';
?>
<style>
    .card {
        overflow-y: auto;
        max-height: 750px;
    }

    tr.usuario-pendente {
        background-color: #fff3cd;
    }

    tr.linha-alterada {
        outline: 2px solid var(--brand-blue, #0d4f9b);
        outline-offset: -2px;
    }

    .badge-alterado {
        display: none;
        margin-left: .35rem;
    }

    tr.linha-alterada .badge-alterado {
        display: inline-block;
    }

    #barraSalvarAlteracoes {
        position: sticky;
        bottom: 0;
        background: #fff;
        border-top: 1px solid #dee2e6;
        padding: 1rem;
        margin: 1rem -1rem -1rem -1rem;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: .75rem;
    }
</style>

<?php
if (isset($_GET['alert'])) {
    switch ($_GET['alert']) {
        case '7':
            echo "<script>
                Swal.fire({
                    position: 'top',
                    title: 'Sucesso!',
                    text: 'Alterações salvas com sucesso.',
                    icon: 'success',
                    confirmButtonText: 'Ok'
                }).then(function() {
                    window.location.href = window.location.pathname;
                });
            </script>";
            break;
        case '8':
            echo "<script>
                Swal.fire({
                    position: 'top',
                    title: 'Erro',
                    text: 'Não foi possível salvar as alterações. Tente novamente.',
                    icon: 'error',
                    confirmButtonText: 'Ok'
                }).then(function() {
                    window.location.href = window.location.pathname;
                });
            </script>";
            break;
        case '9':
            echo "<script>
                Swal.fire({
                    position: 'top',
                    title: 'Alterações salvas parcialmente',
                    text: 'As demais alterações foram salvas, mas você não pode alterar o próprio tipo de acesso ou se desativar por esta tela.',
                    icon: 'warning',
                    confirmButtonText: 'Ok'
                }).then(function() {
                    window.location.href = window.location.pathname;
                });
            </script>";
            break;
        case '3':
            echo "<script>
                Swal.fire({
                    position: 'top',
                    title: 'Sucesso!',
                    text: 'Usuário(s) excluído(s) com sucesso.',
                    icon: 'success',
                    confirmButtonText: 'Ok'
                }).then(function() {
                    window.location.href = window.location.pathname;
                });
            </script>";
            break;
        case '4':
            echo "<script>
                Swal.fire({
                    position: 'top',
                    title: 'Erro',
                    text: 'Não foi possível excluir o(s) usuário(s) selecionado(s). Isso pode acontecer quando o usuário já possui registros vinculados no sistema (avaliações, rodízios, grupos etc.).',
                    icon: 'error',
                    confirmButtonText: 'Ok'
                }).then(function() {
                    window.location.href = window.location.pathname;
                });
            </script>";
            break;
    }
}
?>

<?php
$tiposOpcoes = [
    '-1' => 'Pendente de aprovação',
    '0' => 'Aluno',
    '1' => 'Preceptor',
    '2' => 'Coordenação',
    '3' => 'Coordenação e Preceptor',
];

$porPagina = 20;
$pagina = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$offset = ($pagina - 1) * $porPagina;

$sqlCount = "SELECT COUNT(*) AS total FROM usuarios";
$resCount = $conn->query($sqlCount);
$totalRegistros = $resCount ? (int) $resCount->fetch_assoc()['total'] : 0;
$totalPaginas = max(1, (int) ceil($totalRegistros / $porPagina));
if ($pagina > $totalPaginas) {
    $pagina = $totalPaginas;
    $offset = ($pagina - 1) * $porPagina;
}

$sql = "SELECT idusuario, nome, tipo, registro, ativo FROM usuarios ORDER BY (tipo = -1) DESC, nome ASC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    error_log("Erro na consulta SQL (listar-usuarios.php): " . $conn->error);
    die("Erro ao processar a consulta. Tente novamente mais tarde.");
}
$stmt->bind_param("ii", $porPagina, $offset);
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    error_log("Erro na consulta SQL (listar-usuarios.php): " . $conn->error);
    die("Erro ao processar a consulta. Tente novamente mais tarde.");
}

function montaLinkPaginacaoUsuarios($pagina)
{
    $params = $_GET;
    $params['pagina'] = $pagina;
    return '?' . http_build_query($params);
}
?>

<div class="container mt-3">
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
                <p class="text-muted mb-0">
                    Altere o tipo de acesso ou o status de um ou mais usuários diretamente na tabela e clique em
                    <strong>Salvar Alterações</strong>, no final da página, para confirmar. O botão só fica
                    disponível quando houver alguma alteração pendente.
                </p>
                <div class="text-nowrap">
                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="excluirSelecionados()">
                        <i class="bi bi-trash"></i> Excluir Selecionados
                    </button>
                </div>
            </div>

            <form id="formExcluir" method="post" action="?page=excluir-usuarios">
                <?php echo csrf_field(); ?>
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="selectAll" onclick="selecionarTodos(this)"> Selecionar</th>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Tipo</th>
                            <th>Registro</th>
                            <th>Ativo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows === 0): ?>
                            <tr>
                                <td colspan="6">
                                    <?php if ($totalRegistros === 0): ?>
                                        Nenhum usuário encontrado.
                                    <?php else: ?>
                                        Nenhum usuário encontrado nesta página. <a href="<?php echo montaLinkPaginacaoUsuarios(1); ?>" class="link-paginacao-usuarios">Voltar à primeira página</a>.
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <?php $tipoAtual = (string) $row['tipo']; ?>
                                <tr id="linha-<?php echo $row['idusuario']; ?>"
                                    data-usuario-row
                                    data-id="<?php echo $row['idusuario']; ?>"
                                    class="<?php echo $tipoAtual === '-1' ? 'usuario-pendente' : ''; ?>">
                                    <td><input type="checkbox" name="usuarios[]" value="<?php echo $row['idusuario']; ?>"></td>
                                    <td><?php echo htmlspecialchars($row['idusuario']); ?></td>
                                    <td><?php echo htmlspecialchars($row['nome']); ?></td>
                                    <td>
                                        <select class="form-select form-select-sm tipo-select" data-original="<?php echo $tipoAtual; ?>">
                                            <?php foreach ($tiposOpcoes as $valor => $rotulo): ?>
                                                <option value="<?php echo $valor; ?>" <?php echo ((string) $valor === $tipoAtual) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($rotulo); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <span class="badge bg-primary badge-alterado">alterado</span>
                                    </td>
                                    <td><?php echo isset($row['registro']) ? htmlspecialchars($row['registro']) : 'N/A'; ?></td>
                                    <td>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input ativo-switch" type="checkbox"
                                                data-original="<?php echo (string) $row['ativo']; ?>"
                                                <?php echo ((string) $row['ativo'] === '1') ? 'checked' : ''; ?>>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </form>

            <?php if ($totalPaginas > 1): ?>
                <nav aria-label="Paginação de usuários">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?php echo $pagina <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link link-paginacao-usuarios" href="<?php echo montaLinkPaginacaoUsuarios(max(1, $pagina - 1)); ?>">Anterior</a>
                        </li>
                        <?php for ($p = 1; $p <= $totalPaginas; $p++): ?>
                            <li class="page-item <?php echo $p === $pagina ? 'active' : ''; ?>">
                                <a class="page-link link-paginacao-usuarios" href="<?php echo montaLinkPaginacaoUsuarios($p); ?>"><?php echo $p; ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?php echo $pagina >= $totalPaginas ? 'disabled' : ''; ?>">
                            <a class="page-link link-paginacao-usuarios" href="<?php echo montaLinkPaginacaoUsuarios(min($totalPaginas, $pagina + 1)); ?>">Próxima</a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>

            <form id="formSalvarAlteracoes" method="post" action="?page=salvar-usuarios">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="alteracoes" id="inputAlteracoes" value="">
                <div id="barraSalvarAlteracoes">
                    <span id="contadorAlteracoes" class="badge bg-primary d-none"></span>
                    <button type="button" id="btnSalvarAlteracoes" class="btn btn-primary" disabled>
                        <i class="bi bi-check2-circle"></i> Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>

    function alert_selecionar_usuario() {
        Swal.fire({
            text: 'Selecione ao menos um usuário.',
            icon: 'warning',
            confirmButtonText: 'Ok'
        });
    }

    function selecionarTodos(selectAllCheckbox) {
        const checkboxes = document.querySelectorAll('input[name="usuarios[]"]');
        checkboxes.forEach((checkbox) => {
            checkbox.checked = selectAllCheckbox.checked;
        });
    }

    function excluirSelecionados() {
        const checkboxes = document.querySelectorAll('input[name="usuarios[]"]:checked');
        if (checkboxes.length === 0) {
            alert_selecionar_usuario();
            return;
        }

        Swal.fire({
            title: 'Você tem certeza?',
            text: "Esta ação não pode ser revertida!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sim, excluir!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('formExcluir').submit();
            }
        });
    }

    // --- Edição inline de tipo/status com detecção de alterações ---

    function linhaAlterada(row) {
        const tipoAtual = row.querySelector('.tipo-select').value;
        const tipoOriginal = row.querySelector('.tipo-select').dataset.original;
        const ativoAtual = row.querySelector('.ativo-switch').checked ? '1' : '0';
        const ativoOriginal = row.querySelector('.ativo-switch').dataset.original;
        return tipoAtual !== tipoOriginal || ativoAtual !== ativoOriginal;
    }

    function atualizarEstadoSalvar() {
        let alteradas = 0;
        document.querySelectorAll('tr[data-usuario-row]').forEach((row) => {
            const mudou = linhaAlterada(row);
            row.classList.toggle('linha-alterada', mudou);
            if (mudou) alteradas++;
        });

        const btnSalvar = document.getElementById('btnSalvarAlteracoes');
        const badge = document.getElementById('contadorAlteracoes');

        btnSalvar.disabled = alteradas === 0;

        if (alteradas > 0) {
            badge.textContent = alteradas + (alteradas === 1 ? ' alteração pendente' : ' alterações pendentes');
            badge.classList.remove('d-none');
        } else {
            badge.classList.add('d-none');
        }

        return alteradas;
    }

    document.querySelectorAll('.tipo-select, .ativo-switch').forEach((el) => {
        el.addEventListener('change', atualizarEstadoSalvar);
    });

    document.getElementById('btnSalvarAlteracoes').addEventListener('click', function () {
        const alteracoes = [];
        document.querySelectorAll('tr[data-usuario-row]').forEach((row) => {
            if (linhaAlterada(row)) {
                alteracoes.push({
                    id: row.dataset.id,
                    tipo: row.querySelector('.tipo-select').value,
                    ativo: row.querySelector('.ativo-switch').checked ? 1 : 0
                });
            }
        });

        if (alteracoes.length === 0) {
            return;
        }

        Swal.fire({
            title: 'Confirmar alterações?',
            text: `Você está prestes a alterar ${alteracoes.length} usuário(s).`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sim, salvar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('inputAlteracoes').value = JSON.stringify(alteracoes);
                document.getElementById('formSalvarAlteracoes').submit();
            }
        });
    });

    // Avisa antes de sair da página (paginação) se houver alterações não salvas.
    document.querySelectorAll('a.link-paginacao-usuarios').forEach((link) => {
        link.addEventListener('click', function (e) {
            if (atualizarEstadoSalvar() > 0) {
                e.preventDefault();
                Swal.fire({
                    title: 'Alterações não salvas',
                    text: 'Você tem alterações que ainda não foram salvas. Deseja sair mesmo assim?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sair sem salvar',
                    cancelButtonText: 'Continuar editando'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = link.getAttribute('href');
                    }
                });
            }
        });
    });
</script>
