<?php

include("../../../cfg/config.php");

$subgroupId = $_REQUEST['idsubgrupo'];

$subgroupQuery = "SELECT nome_subgrupo FROM subgrupos WHERE idsubgrupo = ?";
$stmt = $conn->prepare($subgroupQuery);
$stmt->bind_param("i", $subgroupId);
$stmt->execute();
$subgroupResult = $stmt->get_result();
$subgroupName = $subgroupResult->fetch_assoc()['nome_subgrupo'];

$studentsQuery = "SELECT u.idusuario, u.nome, 
                  (SELECT COUNT(*) FROM alunos_subgrupos AS asg WHERE asg.idusuario = u.idusuario AND asg.idsubgrupo = ?) AS associated 
                  FROM usuarios AS u WHERE u.tipo = 0";
$stmt = $conn->prepare($studentsQuery);
$stmt->bind_param("i", $subgroupId);
$stmt->execute();
$studentsResult = $stmt->get_result();

$associatedCount = 0;
$students = [];

while ($student = $studentsResult->fetch_assoc()) {
    $students[] = $student;
    if ($student['associated'] > 0) {
        $associatedCount++;
    }
}
?>

<form action="?page=acoes-grupos" method="post">
    <input type="hidden" name="idsubgrupo" value="<?php echo $subgroupId; ?>">
    <h1>Subgrupo: <?php echo $subgroupName; ?><a href="grupos.php" class="btn btn-secondary float-end">Voltar</a></h1>

    <div class="row">
        <div class="col-md-6">
            <h2>Alunos Associados</h2>
            <?php if ($associatedCount > 0): ?>
                <?php foreach ($students as $student): ?>
                    <?php if ($student['associated'] > 0): ?>
                        <label>
                            <input type="checkbox" name="associated_students[]" value="<?php echo $student['idusuario']; ?>">
                            <?php echo $student['nome']; ?>
                        </label><br>
                    <?php endif; ?>
                <?php endforeach; ?>
                <button class="btn btn-danger" type="submit" name="action" value="disassociate">Dessassociar</button>
            <?php else: ?>
                <p>Não há alunos associados.</p>
            <?php endif; ?>
        </div>
        <div class="col-md-6">
            <h2>Alunos Não Associados</h2>
            <p>Atenção: No máximo 4 alunos podem ser associados.</p>
            <?php $unassociatedStudents = array_filter($students, function($student) { return $student['associated'] == 0; }); ?>
            <?php if (count($unassociatedStudents) > 0): ?>
                <?php foreach ($unassociatedStudents as $student): ?>
                    <label>
                        <input type="checkbox" name="students[]" value="<?php echo $student['idusuario']; ?>">
                        <?php echo $student['nome']; ?>
                    </label><br>
                <?php endforeach; ?>
                <button class="btn btn-success" type="submit" name="action" value="associate">Associar</button>
            <?php else: ?>
                <p>Não há alunos cadastrados.</p>
            <?php endif; ?>
        </div>
    </div>
</form>