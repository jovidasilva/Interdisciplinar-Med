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
    <title>Lista de Preceptores</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../css/style.css">
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
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "
                                SELECT u.*, un.nome_unidade 
                                FROM usuarios u
                                LEFT JOIN preceptores_unidades pu ON u.idusuario = pu.idusuario
                                LEFT JOIN unidades un ON pu.idunidade = un.idunidade
                                WHERE u.tipo = 1
                            ";
                            $res = $conn->query($sql);

                            if (!$res) {
                                die("Erro na consulta: " . $conn->error);
                            }

                            $qtd = $res->num_rows;

                            if ($qtd > 0) {
                                while ($row = $res->fetch_object()) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row->nome) . "</td>";
                                    echo "<td>" . htmlspecialchars($row->registro) . "</td>";
                                    echo "<td>" . htmlspecialchars($row->email) . "</td>";
                                    echo "<td>" . ativoTexto($row->ativo) . "</td>";
                                    echo "<td>" . htmlspecialchars($row->telefone) . "</td>";
                                    echo "<td>" . htmlspecialchars($row->nome_unidade ?? 'Não Associado') . "</td>";
                                    echo "</tr>";
                                }
                             } else {
                                echo "<tr><td colspan='6'>Nenhum preceptor encontrado.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
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
