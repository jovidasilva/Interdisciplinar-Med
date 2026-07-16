SELECT 
    subgrupos.idsubgrupo AS IDSubgrupo,
    subgrupos.nome_subgrupo AS NomeSubgrupo,
    usuarios.idusuario AS IDUsuario,
    usuarios.nome AS NomeUsuario,
    usuarios.email AS EmailUsuario,
    usuarios.telefone AS TelefoneUsuario
FROM 
    alunos_subgrupos
JOIN 
    usuarios ON alunos_subgrupos.idusuario = usuarios.idusuario
JOIN 
    subgrupos ON alunos_subgrupos.idsubgrupo = subgrupos.idsubgrupo
ORDER BY 
    subgrupos.idsubgrupo, usuarios.nome;
