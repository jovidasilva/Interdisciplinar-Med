-- Desativar checagem de chaves estrangeiras
SET FOREIGN_KEY_CHECKS = 0;

-- Truncar tabelas dependentes primeiro
TRUNCATE TABLE proj_internato.avaliacoes_respostas;
TRUNCATE TABLE proj_internato.avaliacoes;
TRUNCATE TABLE proj_internato.preceptores_unidades;
TRUNCATE TABLE proj_internato.preceptores_modulos;
TRUNCATE TABLE proj_internato.modulos_alunos;
TRUNCATE TABLE proj_internato.horarios;
TRUNCATE TABLE proj_internato.rodizios_subgrupos;
TRUNCATE TABLE proj_internato.rodizios;
TRUNCATE TABLE proj_internato.unidades_modulos;
TRUNCATE TABLE proj_internato.alunos_subgrupos;
TRUNCATE TABLE proj_internato.subgrupos;
TRUNCATE TABLE proj_internato.grupos;
TRUNCATE TABLE proj_internato.perguntas_avaliacoes;
TRUNCATE TABLE proj_internato.modulos;
TRUNCATE TABLE proj_internato.unidades;
TRUNCATE TABLE proj_internato.usuarios;

-- Reativar checagem de chaves estrangeiras
SET FOREIGN_KEY_CHECKS = 1;
