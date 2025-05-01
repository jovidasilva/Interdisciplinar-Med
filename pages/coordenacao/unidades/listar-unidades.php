<div class="container mt-3">
    <div class="card">
        <div class="card-body">
            <h3>Lista de Unidades
                <button onclick="location.href='?page=registrar-unidades'" class="btn btn-secondary">Novo</button>
            </h3>
            <table class="table table-secondary table-bordered">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Endereço</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT * FROM unidades";
                    $res = $conn->query($sql);

                    if (!$res) {
                        die("Erro na consulta: " . $conn->error);
                    }

                    $qtd = $res->num_rows;

                    if ($qtd > 0) {
                        while ($row = $res->fetch_object()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row->nome_unidade) . "</td>";
                            echo "<td>" . htmlspecialchars($row->endereco_unidade) . "</td>";
                            echo "<td>
                             <button onclick=\"location.href='?page=visualizar-unidade&idunidade=" . $row->idunidade . "';\" class='btn btn-primary'>Visualizar</button>
                             <button onclick=\"location.href='?page=editar-unidades&idunidade=" . $row->idunidade . "';\" class='btn btn-success'>Editar</button>
                             <button onclick=\"if(confirm('Tem certeza que deseja excluir?')) location.href='acoes-unidades.php?acao=excluir&idunidade=" . $row->idunidade . "';\" class='btn btn-danger'>Excluir</button>
                             </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='3'>Nenhuma unidade encontrada.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>