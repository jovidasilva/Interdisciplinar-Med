-- Desativar as verificações de chave estrangeira
SET FOREIGN_KEY_CHECKS = 0;

-- Truncate em todas as tabelas
TRUNCATE TABLE `proj_internato`.`rodizios_subgrupos`;
TRUNCATE TABLE `proj_internato`.`rodizios`;
TRUNCATE TABLE `proj_internato`.`unidades_modulos`;
TRUNCATE TABLE `proj_internato`.`preceptores_unidades`;
TRUNCATE TABLE `proj_internato`.`preceptores_modulos`;
TRUNCATE TABLE `proj_internato`.`modulos_departamentos`;
TRUNCATE TABLE `proj_internato`.`modulos_alunos`;
TRUNCATE TABLE `proj_internato`.`horarios_supervisao`;
TRUNCATE TABLE `proj_internato`.`horarios`;
TRUNCATE TABLE `proj_internato`.`avaliacoes_respostas`;
TRUNCATE TABLE `proj_internato`.`perguntas_avaliacoes`;
TRUNCATE TABLE `proj_internato`.`avaliacoes`;
TRUNCATE TABLE `proj_internato`.`modulos`;
TRUNCATE TABLE `proj_internato`.`departamentos`;
TRUNCATE TABLE `proj_internato`.`unidades`;
TRUNCATE TABLE `proj_internato`.`alunos_subgrupos`;
TRUNCATE TABLE `proj_internato`.`subgrupos`;
TRUNCATE TABLE `proj_internato`.`grupos`;
TRUNCATE TABLE `proj_internato`.`usuarios`;

-- Reativar as verificações de chave estrangeira
SET FOREIGN_KEY_CHECKS = 1;
