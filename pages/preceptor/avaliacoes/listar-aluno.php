<h3>Lista de Alunos</h3>
<table class="table table-striped table-secondary table-bordered">
    <thead>
        <tr>
            <th>Nome</th>
            <th>RA</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $sql = "SELECT * FROM usuarios WHERE tipo = 0";
        $res = $conn->query($sql);

        if (!$res) {
            die("Erro na consulta: " . $conn->error);
        }

        $qtd = $res->num_rows;

        if ($qtd > 0) {
            while ($row = $res->fetch_object()) {
                $sql_avaliacao = "SELECT COUNT(*) AS total_avaliacoes FROM avaliacoes WHERE idusuario = " . $row->idusuario;
                $res_avaliacao = $conn->query($sql_avaliacao);
                $row_avaliacao = $res_avaliacao->fetch_object();
                $avaliacao_realizada = $row_avaliacao->total_avaliacoes > 0;

                echo "<tr>";
                echo "<td>" . htmlspecialchars($row->nome) . "</td>";
                echo "<td>" . htmlspecialchars($row->registro) . "</td>";
                echo "<td>";
                if ($avaliacao_realizada) {
                    echo "<span class='text-success'>Avaliação já realizada</span>";
                } else {
                    echo "<button onclick=\"window.location.href='realizar-avaliacao.php?idusuario=" . $row->idusuario . "';\" class='btn btn-primary'>Realizar Avaliação</button>";
                }
                echo "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='3'>Nenhum aluno encontrado.</td></tr>";
        }
        ?>
    </tbody>
</table>
