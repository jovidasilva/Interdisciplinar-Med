-- Migration 005: ciclos de rodízio + integridade referencial.
--
-- 1. Charset utf8mb4 em todas as tabelas (a conexão já usa utf8mb4).
-- 2. Tabela rodizio_ciclos: cada geração de rodízios vira um ciclo (período +
--    módulos + janelas), dono dos seus grupos e rodízios. Excluir o ciclo
--    remove tudo o que ele gerou.
-- 3. Chaves únicas nas tabelas de associação (antes era possível duplicar
--    matrícula, associação, aluno em subgrupo, avaliação...).
-- 4. ON DELETE CASCADE nas tabelas de junção, para excluir usuário/unidade
--    sem erro de FK. Tabelas "de negócio" (rodizios/horarios -> modulos,
--    horarios -> unidades) continuam RESTRICT de propósito.

USE proj_internato;

SET FOREIGN_KEY_CHECKS = 0;

ALTER DATABASE proj_internato CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
ALTER TABLE usuarios CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
ALTER TABLE grupos CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
ALTER TABLE subgrupos CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
ALTER TABLE alunos_subgrupos CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
ALTER TABLE unidades CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
ALTER TABLE departamentos CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
ALTER TABLE modulos CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
ALTER TABLE avaliacoes CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
ALTER TABLE perguntas_avaliacoes CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
ALTER TABLE avaliacoes_respostas CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
ALTER TABLE horarios CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
ALTER TABLE horarios_supervisao CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
ALTER TABLE modulos_alunos CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
ALTER TABLE modulos_departamentos CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
ALTER TABLE preceptores_modulos CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
ALTER TABLE preceptores_unidades CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
ALTER TABLE rodizios CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
ALTER TABLE rodizios_subgrupos CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
ALTER TABLE unidades_modulos CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
ALTER TABLE password_reset_tokens CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
ALTER TABLE login_attempts CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

-- ---------------------------------------------------------------------
-- Ciclos de rodízio
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS rodizio_ciclos (
  idciclo INT NOT NULL AUTO_INCREMENT,
  periodo INT NOT NULL,
  descricao VARCHAR(90) NOT NULL,
  criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (idciclo),
  INDEX idx_ciclos_periodo (periodo)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8mb4;

ALTER TABLE grupos
  MODIFY nome_grupo VARCHAR(20) NOT NULL,
  ADD COLUMN idciclo INT NULL AFTER idgrupo,
  ADD CONSTRAINT fk_grupos_ciclo FOREIGN KEY (idciclo) REFERENCES rodizio_ciclos (idciclo) ON DELETE CASCADE;

ALTER TABLE subgrupos MODIFY nome_subgrupo VARCHAR(20) NOT NULL;

ALTER TABLE rodizios
  DROP COLUMN grupos,
  ADD COLUMN idciclo INT NULL AFTER idrodizio,
  ADD CONSTRAINT fk_rodizios_ciclo FOREIGN KEY (idciclo) REFERENCES rodizio_ciclos (idciclo) ON DELETE CASCADE;

-- Dados antigos: um ciclo "legado" por período, para que nada fique órfão.
INSERT INTO rodizio_ciclos (periodo, descricao)
  SELECT DISTINCT periodo, CONCAT(periodo, 'º período — rodízios anteriores') FROM rodizios WHERE idciclo IS NULL;
UPDATE rodizios r JOIN rodizio_ciclos c ON c.periodo = r.periodo SET r.idciclo = c.idciclo WHERE r.idciclo IS NULL;
UPDATE grupos g
  JOIN (SELECT sg.idgrupo, MIN(r.idciclo) AS idciclo
        FROM subgrupos sg
        JOIN rodizios_subgrupos rs ON rs.idsubgrupo = sg.idsubgrupo
        JOIN rodizios r ON r.idrodizio = rs.idrodizio
        GROUP BY sg.idgrupo) x ON x.idgrupo = g.idgrupo
  SET g.idciclo = x.idciclo
  WHERE g.idciclo IS NULL;

-- ---------------------------------------------------------------------
-- Remove duplicatas antes de criar as chaves únicas
-- ---------------------------------------------------------------------
DELETE a FROM modulos_alunos a JOIN modulos_alunos b
  ON a.idmodulo = b.idmodulo AND a.idusuario = b.idusuario AND a.id > b.id;
DELETE a FROM alunos_subgrupos a JOIN alunos_subgrupos b
  ON a.idusuario = b.idusuario AND a.idsubgrupo = b.idsubgrupo AND a.idaluno_subgrupo > b.idaluno_subgrupo;
DELETE a FROM unidades_modulos a JOIN unidades_modulos b
  ON a.idmodulo = b.idmodulo AND a.idunidade = b.idunidade AND a.idunidade_modulo > b.idunidade_modulo;
DELETE a FROM preceptores_modulos a JOIN preceptores_modulos b
  ON a.idusuario = b.idusuario AND a.idmodulo = b.idmodulo AND a.idpreceptor_modulo > b.idpreceptor_modulo;
DELETE a FROM preceptores_unidades a JOIN preceptores_unidades b
  ON a.idusuario = b.idusuario AND a.idpreceptor_unidade > b.idpreceptor_unidade;
DELETE a FROM rodizios_subgrupos a JOIN rodizios_subgrupos b
  ON a.idrodizio = b.idrodizio AND a.idsubgrupo = b.idsubgrupo AND a.idrodizio_subgrupo > b.idrodizio_subgrupo;

-- ---------------------------------------------------------------------
-- Chaves únicas
-- ---------------------------------------------------------------------
ALTER TABLE usuarios ADD UNIQUE KEY uk_usuarios_login (login);
ALTER TABLE modulos_alunos ADD UNIQUE KEY uk_modulo_aluno (idmodulo, idusuario);
ALTER TABLE alunos_subgrupos ADD UNIQUE KEY uk_aluno_subgrupo (idusuario, idsubgrupo);
ALTER TABLE unidades_modulos ADD UNIQUE KEY uk_unidade_modulo (idunidade, idmodulo);
ALTER TABLE preceptores_modulos ADD UNIQUE KEY uk_preceptor_modulo (idusuario, idmodulo);
-- Um preceptor pertence a uma única unidade (regra já aplicada pela tela).
ALTER TABLE preceptores_unidades ADD UNIQUE KEY uk_preceptor_unidade (idusuario);
ALTER TABLE rodizios_subgrupos ADD UNIQUE KEY uk_rodizio_subgrupo (idrodizio, idsubgrupo);
-- Cada preceptor avalia cada aluno uma vez por módulo.
ALTER TABLE avaliacoes ADD UNIQUE KEY uk_avaliacao (idaluno, idmodulo, idpreceptor);

-- ---------------------------------------------------------------------
-- CASCADE nas tabelas de junção
-- ---------------------------------------------------------------------
ALTER TABLE alunos_subgrupos
  DROP FOREIGN KEY alunos_subgrupos_ibfk_1, DROP FOREIGN KEY alunos_subgrupos_ibfk_2,
  ADD CONSTRAINT fk_alunos_subgrupos_usuario FOREIGN KEY (idusuario) REFERENCES usuarios (idusuario) ON DELETE CASCADE,
  ADD CONSTRAINT fk_alunos_subgrupos_subgrupo FOREIGN KEY (idsubgrupo) REFERENCES subgrupos (idsubgrupo) ON DELETE CASCADE;

ALTER TABLE rodizios_subgrupos
  DROP FOREIGN KEY fk_rodiziosub_rodizio, DROP FOREIGN KEY fk_rodiziosub_subgrupo,
  ADD CONSTRAINT fk_rodizios_subgrupos_rodizio FOREIGN KEY (idrodizio) REFERENCES rodizios (idrodizio) ON DELETE CASCADE,
  ADD CONSTRAINT fk_rodizios_subgrupos_subgrupo FOREIGN KEY (idsubgrupo) REFERENCES subgrupos (idsubgrupo) ON DELETE CASCADE;

ALTER TABLE preceptores_modulos
  DROP FOREIGN KEY preceptores_modulos_ibfk_1, DROP FOREIGN KEY preceptores_modulos_ibfk_2,
  ADD CONSTRAINT fk_preceptores_modulos_usuario FOREIGN KEY (idusuario) REFERENCES usuarios (idusuario) ON DELETE CASCADE,
  ADD CONSTRAINT fk_preceptores_modulos_modulo FOREIGN KEY (idmodulo) REFERENCES modulos (idmodulo) ON DELETE CASCADE;

ALTER TABLE preceptores_unidades
  DROP FOREIGN KEY preceptores_unidades_ibfk_1, DROP FOREIGN KEY preceptores_unidades_ibfk_2,
  ADD CONSTRAINT fk_preceptores_unidades_usuario FOREIGN KEY (idusuario) REFERENCES usuarios (idusuario) ON DELETE CASCADE,
  ADD CONSTRAINT fk_preceptores_unidades_unidade FOREIGN KEY (idunidade) REFERENCES unidades (idunidade) ON DELETE CASCADE;

ALTER TABLE unidades_modulos
  DROP FOREIGN KEY fk_unidade_modulo_modulo, DROP FOREIGN KEY fk_unidade_modulo_unidade,
  ADD CONSTRAINT fk_unidades_modulos_modulo FOREIGN KEY (idmodulo) REFERENCES modulos (idmodulo) ON DELETE CASCADE,
  ADD CONSTRAINT fk_unidades_modulos_unidade FOREIGN KEY (idunidade) REFERENCES unidades (idunidade) ON DELETE CASCADE;

ALTER TABLE departamentos
  DROP FOREIGN KEY fk_departamentos_unidades1,
  ADD CONSTRAINT fk_departamentos_unidade FOREIGN KEY (idunidade) REFERENCES unidades (idunidade) ON DELETE CASCADE;

SET FOREIGN_KEY_CHECKS = 1;
