<?php
session_start();
if (empty($_SESSION["login"]) || !in_array($_SESSION['tipo'] ?? null, [2, 3], true)) {
    echo "<script>location.href='../../index.php';</script>";
    exit();
}
include('../../cfg/config.php');
require_once __DIR__ . '/../../includes/csrf.php';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Preceptores</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../css/style.css?v=<?php echo ASSET_VERSION; ?>">
</head>

<body>
    <?php
    function ativoTexto($ativo) {
        switch ($ativo) {
            case '0':
                return 'Inativo';
            case '1':
                return 'Ativo';
            default:
                return 'Não definido';
        }
    }
    ?>
    <header>
        <?php include('../../includes/navbar.php'); ?>
        <?php include('../../includes/menu-lateral-coordenacao.php'); ?>
    </header>
    <main>
        <div class="container mt-3">
            <div class="card">
                <div class="card-body">
                    <h3>Lista de Preceptores</h3>
                    <button class="btn btn-primary mb-3" onclick="location.href='associar-preceptor.php'">Gerenciar Preceptores</button>
                    <table class="table table-striped table-secondary table-bordered">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>CRM</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Telefone</th>
                                <th>Unidade</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $porPagina = 20;
                            $pagina = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
                            $offset = ($pagina - 1) * $porPagina;

                            $sqlCount = "
                                SELECT COUNT(*) AS total
                                FROM usuarios u
                                LEFT JOIN preceptores_unidades pu ON u.idusuario = pu.idusuario
                                LEFT JOIN unidades un ON pu.idunidade = un.idunidade
                                WHERE u.tipo = 1
                            ";
                            $resCount = $conn->query($sqlCount);
                            $totalRegistros = $resCount ? (int) $resCount->fetch_assoc()['total'] : 0;
                            $totalPaginas = max(1, (int) ceil($totalRegistros / $porPagina));
                            if ($pagina > $totalPaginas) {
                                $pagina = $totalPaginas;
                                $offset = ($pagina - 1) * $porPagina;
                            }

                            $sql = "
                                SELECT u.*, un.nome_unidade
                                FROM usuarios u
                                LEFT JOIN preceptores_unidades pu ON u.idusuario = pu.idusuario
                                LEFT JOIN unidades un ON pu.idunidade = un.idunidade
                                WHERE u.tipo = 1
                                LIMIT ? OFFSET ?
                            ";
                            $stmt = $conn->prepare($sql);

                            if (!$stmt) {
                                error_log("Erro na consulta (listar-preceptor.php): " . $conn->error);
                                die("Erro ao processar a consulta. Tente novamente mais tarde.");
                            }

                            $stmt->bind_param("ii", $porPagina, $offset);
                            $stmt->execute();
                            $res = $stmt->get_result();

                            $qtd = $res->num_rows;

                            if ($qtd > 0) {
                                while ($row = $res->fetch_object()) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row->nome) . "</td>";
                                    echo "<td>" . htmlspecialchars($row->registro) . "</td>"; // Aqui está correto, usando 'registro' em vez de 'crm'
                                    echo "<td>" . htmlspecialchars($row->email) . "</td>";
                                    echo "<td>" . ativoTexto($row->ativo) . "</td>";
                                    echo "<td>" . htmlspecialchars($row->telefone) . "</td>";
                                    echo "<td>" . htmlspecialchars($row->nome_unidade ?? 'Não Associado') . "</td>";
                                    echo "<td>";
                                    if (!empty($row->nome_unidade)) {
                                        // Preceptor já associado, exibir botão de dissociar
                                        echo "<form method='POST' action='associar-preceptor.php' style='display: inline;'>
                                                " . csrf_field() . "
                                                <input type='hidden' name='idPreceptor' value='" . $row->idusuario . "'>
                                                <input type='hidden' name='acao' value='dissociar'>
                                                <button type='submit' class='btn btn-danger btn-sm' onclick=\"return confirm('Deseja realmente dissociar este preceptor da unidade e remover todos os módulos associados?');\">Dissociar</button>
                                            </form>";
                                    }
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                if ($totalRegistros === 0) {
                                    echo "<tr><td colspan='7'>Nenhum preceptor encontrado.</td></tr>";
                                } else {
                                    echo "<tr><td colspan='7'>Nenhum preceptor encontrado nesta página. <a href='?" . http_build_query(array_merge($_GET, ['pagina' => 1])) . "'>Voltar à primeira página</a>.</td></tr>";
                                }
                            }
                            ?>
                        </tbody>
                    </table>

                    <?php if ($totalPaginas > 1): ?>
                        <nav aria-label="Paginação de preceptores">
                            <ul class="pagination justify-content-center">
                                <?php
                                $paramsAnterior = $_GET;
                                $paramsAnterior['pagina'] = max(1, $pagina - 1);
                                $paramsProxima = $_GET;
                                $paramsProxima['pagina'] = min($totalPaginas, $pagina + 1);
                                ?>
                                <li class="page-item <?php echo $pagina <= 1 ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?<?php echo http_build_query($paramsAnterior); ?>">Anterior</a>
                                </li>
                                <?php for ($p = 1; $p <= $totalPaginas; $p++): ?>
                                    <?php $paramsP = $_GET; $paramsP['pagina'] = $p; ?>
                                    <li class="page-item <?php echo $p === $pagina ? 'active' : ''; ?>">
                                        <a class="page-link" href="?<?php echo http_build_query($paramsP); ?>"><?php echo $p; ?></a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?php echo $pagina >= $totalPaginas ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?<?php echo http_build_query($paramsProxima); ?>">Próxima</a>
                                </li>
                            </ul>
                        </nav>
                    <?php endif; ?>
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
</body>
</html>