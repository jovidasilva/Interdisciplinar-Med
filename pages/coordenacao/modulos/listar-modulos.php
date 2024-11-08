<div class="container mt-3">
    <div class="card">
        <div class="card-body">
            <h3>Lista de Módulos
                <button onclick="location.href='?page=registrar-modulos'" class="btn btn-secondary">Novo</button>
                <a href="?page=importar-modulos" class="btn btn-secondary">
                    <i class="bi bi-upload"></i> Upload modulos
                </a>
            </h3>
            <table class="table table-striped table-secondary table-bordered">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Período</th>
                        <th>Alunos Cadastrados</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT * FROM modulos";
                    $res = $conn->query($sql);

                    if (!$res) {
                        die("Erro na consulta: " . $conn->error);
                    }

                    $qtd = $res->num_rows;

                    if ($qtd > 0) {
                        while ($row = $res->fetch_object()) {
                            $stmtCount = $conn->prepare("SELECT COUNT(*) as total FROM modulos_alunos WHERE idmodulo = ?");
                            $stmtCount->bind_param("i", $row->idmodulo);
                            $stmtCount->execute();
                            $resultCount = $stmtCount->get_result();
                            $countRow = $resultCount->fetch_assoc();
                            $totalAlunos = $countRow['total'];

                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row->nome_modulo) . "</td>";
                            echo "<td>" . htmlspecialchars($row->periodo) . "</td>";
                            echo "<td>" . htmlspecialchars($totalAlunos) . "</td>"; 
                            echo "<td>
                             <button onclick=\"location.href='?page=visualizar-modulos&idmodulo=" . $row->idmodulo . "';\" class='btn btn-primary'>Visualizar</button>
                             <button onclick=\"location.href='?page=editar-modulos&idmodulo=" . $row->idmodulo . "';\" class='btn btn-success'>Editar</button>
                             <button onclick=\"if(confirm('Tem certeza que deseja excluir?')) location.href='acoes-modulos.php?acao=excluir&idmodulo=" . $row->idmodulo . "';\" class='btn btn-danger'>Excluir</button>
                             </td>";
                            echo "</tr>";

                            $stmtCount->close();
                        }
                    } else {
                        echo "<tr><td colspan='4'>Nenhum módulo encontrado.</td></tr>"; 
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>