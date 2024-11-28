CREATE VIEW vw_modulos_alunos AS
SELECT
    u.idusuario AS aluno_id,
    u.nome AS aluno_nome,
    m.idmodulo,
    m.nome_modulo,
    m.periodo,
    d.nome_departamento
FROM
    proj_internato.modulos_alunos ma
JOIN
    proj_internato.usuarios u ON ma.idusuario = u.idusuario
JOIN
    proj_internato.modulos m ON ma.idmodulo = m.idmodulo
LEFT JOIN
    proj_internato.departamentos d ON m.iddepartamento = d.iddepartamento;
