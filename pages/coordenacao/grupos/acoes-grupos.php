<?php

if (isset($_POST['action'])) {
    $action = $_POST['action'];
    $subgroupId = $_POST['idsubgrupo'];

    if ($action == 'associate') {
        $students = $_POST['students'];
        $maxStudents = 4;
        $currentStudents = 0;

        $query = "SELECT COUNT(*) FROM alunos_subgrupos WHERE idsubgrupo = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $subgroupId);
        $stmt->execute();
        $result = $stmt->get_result();
        $currentStudents = $result->fetch_assoc()['COUNT(*)'];

        if ($currentStudents + count($students) > $maxStudents) {
            echo "<script>location.href='?page=ver-alunos&idsubgrupo=$subgroupId';</script>";
        } else {
            foreach ($students as $student) {
                $query = "INSERT INTO alunos_subgrupos (idusuario, idsubgrupo) VALUES (?, ?)";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("ii", $student, $subgroupId);
                $stmt->execute();
            }
            echo "<script>location.href='?page=ver-alunos&idsubgrupo=$subgroupId';</script>";

        }
    } elseif ($action == 'disassociate') {
        $students = $_POST['associated_students'];
        foreach ($students as $student) {
            $query = "DELETE FROM alunos_subgrupos WHERE idusuario = ? AND idsubgrupo = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ii", $student, $subgroupId);
            $stmt->execute();
        }
        echo "<script>location.href='?page=ver-alunos&idsubgrupo=$subgroupId';</script>";
    }
}
