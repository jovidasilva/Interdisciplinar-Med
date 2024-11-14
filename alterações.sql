-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- -----------------------------------------------------
-- Schema mydb
-- -----------------------------------------------------
-- -----------------------------------------------------
-- Schema proj_internato
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema proj_internato
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `proj_internato` DEFAULT CHARACTER SET utf8mb3 ;
USE `proj_internato` ;

-- -----------------------------------------------------
-- Table `proj_internato`.`usuarios`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `proj_internato`.`usuarios` (
  `idusuario` INT NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(90) NULL DEFAULT NULL,
  `email` VARCHAR(90) NULL DEFAULT NULL,
  `telefone` VARCHAR(90) NULL DEFAULT NULL,
  `login` VARCHAR(90) NULL DEFAULT NULL,
  `senha` VARCHAR(90) NULL DEFAULT NULL,
  `tipo` INT NOT NULL DEFAULT '-1',
  `ativo` TINYINT NULL DEFAULT '1',
  `registro` VARCHAR(90) NULL DEFAULT NULL,
  `periodo` INT NULL DEFAULT NULL,
  PRIMARY KEY (`idusuario`))
ENGINE = InnoDB
AUTO_INCREMENT = 217
DEFAULT CHARACTER SET = utf8mb3;


-- -----------------------------------------------------
-- Table `proj_internato`.`grupos`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `proj_internato`.`grupos` (
  `idgrupo` INT NOT NULL AUTO_INCREMENT,
  `nome_grupo` VARCHAR(5) NOT NULL,
  PRIMARY KEY (`idgrupo`))
ENGINE = InnoDB
AUTO_INCREMENT = 4
DEFAULT CHARACTER SET = utf8mb3;


-- -----------------------------------------------------
-- Table `proj_internato`.`subgrupos`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `proj_internato`.`subgrupos` (
  `idsubgrupo` INT NOT NULL AUTO_INCREMENT,
  `idgrupo` INT NOT NULL,
  `nome_subgrupo` VARCHAR(5) NOT NULL,
  PRIMARY KEY (`idsubgrupo`),
  INDEX `fk_grupo_idx` (`idgrupo` ASC) VISIBLE,
  CONSTRAINT `fk_subgrupos_grupos`
    FOREIGN KEY (`idgrupo`)
    REFERENCES `proj_internato`.`grupos` (`idgrupo`)
    ON DELETE CASCADE)
ENGINE = InnoDB
AUTO_INCREMENT = 10
DEFAULT CHARACTER SET = utf8mb3;


-- -----------------------------------------------------
-- Table `proj_internato`.`alunos_subgrupos`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `proj_internato`.`alunos_subgrupos` (
  `idaluno_subgrupo` INT NOT NULL AUTO_INCREMENT,
  `idusuario` INT NOT NULL,
  `idsubgrupo` INT NOT NULL,
  PRIMARY KEY (`idaluno_subgrupo`),
  INDEX `idusuario` (`idusuario` ASC) VISIBLE,
  INDEX `idsubgrupo` (`idsubgrupo` ASC) VISIBLE,
  CONSTRAINT `alunos_subgrupos_ibfk_1`
    FOREIGN KEY (`idusuario`)
    REFERENCES `proj_internato`.`usuarios` (`idusuario`),
  CONSTRAINT `alunos_subgrupos_ibfk_2`
    FOREIGN KEY (`idsubgrupo`)
    REFERENCES `proj_internato`.`subgrupos` (`idsubgrupo`))
ENGINE = InnoDB
AUTO_INCREMENT = 37
DEFAULT CHARACTER SET = utf8mb3;


-- -----------------------------------------------------
-- Table `proj_internato`.`unidades`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `proj_internato`.`unidades` (
  `idunidade` INT NOT NULL AUTO_INCREMENT,
  `nome_unidade` VARCHAR(90) NOT NULL,
  `endereco_unidade` VARCHAR(90) NULL DEFAULT NULL,
  PRIMARY KEY (`idunidade`))
ENGINE = InnoDB
AUTO_INCREMENT = 4
DEFAULT CHARACTER SET = utf8mb3;


-- -----------------------------------------------------
-- Table `proj_internato`.`departamentos`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `proj_internato`.`departamentos` (
  `iddepartamento` INT NOT NULL AUTO_INCREMENT,
  `nome_departamento` VARCHAR(100) NULL DEFAULT NULL,
  `idunidade` INT NOT NULL,
  PRIMARY KEY (`iddepartamento`, `idunidade`),
  INDEX `fk_departamentos_unidades1_idx` (`idunidade` ASC) VISIBLE,
  CONSTRAINT `fk_departamentos_unidades1`
    FOREIGN KEY (`idunidade`)
    REFERENCES `proj_internato`.`unidades` (`idunidade`))
ENGINE = InnoDB
AUTO_INCREMENT = 5
DEFAULT CHARACTER SET = utf8mb3;


-- -----------------------------------------------------
-- Table `proj_internato`.`modulos`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `proj_internato`.`modulos` (
  `idmodulo` INT NOT NULL AUTO_INCREMENT,
  `nome_modulo` VARCHAR(90) NOT NULL,
  `periodo` INT NOT NULL,
  `iddepartamento` INT NULL DEFAULT NULL,
  PRIMARY KEY (`idmodulo`),
  INDEX `fk_modulos_departamentos` (`iddepartamento` ASC) VISIBLE,
  CONSTRAINT `fk_modulos_departamentos`
    FOREIGN KEY (`iddepartamento`)
    REFERENCES `proj_internato`.`departamentos` (`iddepartamento`)
    ON DELETE SET NULL)
ENGINE = InnoDB
AUTO_INCREMENT = 12
DEFAULT CHARACTER SET = utf8mb3;


-- -----------------------------------------------------
-- Table `proj_internato`.`avaliacoes`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `proj_internato`.`avaliacoes` (
  `idavaliacao` INT NOT NULL AUTO_INCREMENT,
  `idaluno` INT NOT NULL,
  `data_avaliacao` DATE NOT NULL,
  `nota` INT NOT NULL,
  `idpreceptor` INT NOT NULL,
  `idmodulo` INT NOT NULL,
  PRIMARY KEY (`idavaliacao`),
  INDEX `fk_aluno_idx` (`idaluno` ASC) VISIBLE,
  INDEX `fk_preceptor_idx` (`idpreceptor` ASC) VISIBLE,
  INDEX `fk_modulo_idx` (`idmodulo` ASC) VISIBLE,
  CONSTRAINT `fk_avaliacao_aluno`
    FOREIGN KEY (`idaluno`)
    REFERENCES `proj_internato`.`usuarios` (`idusuario`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_avaliacao_modulo`
    FOREIGN KEY (`idmodulo`)
    REFERENCES `proj_internato`.`modulos` (`idmodulo`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_avaliacao_preceptor`
    FOREIGN KEY (`idpreceptor`)
    REFERENCES `proj_internato`.`usuarios` (`idusuario`)
    ON DELETE CASCADE)
ENGINE = InnoDB
AUTO_INCREMENT = 2
DEFAULT CHARACTER SET = utf8mb3;


-- -----------------------------------------------------
-- Table `proj_internato`.`perguntas_avaliacoes`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `proj_internato`.`perguntas_avaliacoes` (
  `idpergunta` INT NOT NULL AUTO_INCREMENT,
  `descricao` TEXT NOT NULL,
  `tipo_resposta` ENUM('texto', 'numerico', 'escala') NOT NULL,
  `ativo` TINYINT NOT NULL DEFAULT '1',
  `titulo` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`idpergunta`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb3;


-- -----------------------------------------------------
-- Table `proj_internato`.`avaliacoes_respostas`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `proj_internato`.`avaliacoes_respostas` (
  `idresposta` INT NOT NULL AUTO_INCREMENT,
  `idavaliacao` INT NOT NULL,
  `idpergunta` INT NOT NULL,
  `resposta` VARCHAR(255) NULL DEFAULT NULL,
  PRIMARY KEY (`idresposta`),
  INDEX `fk_resposta_avaliacao` (`idavaliacao` ASC) VISIBLE,
  INDEX `fk_resposta_pergunta` (`idpergunta` ASC) VISIBLE,
  CONSTRAINT `fk_resposta_avaliacao`
    FOREIGN KEY (`idavaliacao`)
    REFERENCES `proj_internato`.`avaliacoes` (`idavaliacao`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_resposta_pergunta`
    FOREIGN KEY (`idpergunta`)
    REFERENCES `proj_internato`.`perguntas_avaliacoes` (`idpergunta`)
    ON DELETE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb3;


-- -----------------------------------------------------
-- Table `proj_internato`.`horarios`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `proj_internato`.`horarios` (
  `idhorario` INT NOT NULL AUTO_INCREMENT,
  `idunidade` INT NOT NULL,
  `idmodulo` INT NOT NULL,
  `iddepartamento` INT NULL DEFAULT NULL,
  `idpreceptor` INT NOT NULL,
  `dia_semana` ENUM('Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo') NOT NULL,
  `hora_inicio` TIME NOT NULL,
  `hora_fim` TIME NOT NULL,
  `local` VARCHAR(90) NULL DEFAULT NULL,
  `idsubgrupo` INT NOT NULL,
  PRIMARY KEY (`idhorario`),
  INDEX `fk_horarios_unidade_idx` (`idunidade` ASC) VISIBLE,
  INDEX `fk_horarios_modulo_idx` (`idmodulo` ASC) VISIBLE,
  INDEX `fk_horarios_preceptor_idx` (`idpreceptor` ASC) VISIBLE,
  INDEX `fk_horario_subgrupo` (`idsubgrupo` ASC) VISIBLE,
  INDEX `fk_horarios_departamento` (`iddepartamento` ASC) VISIBLE,
  CONSTRAINT `fk_horario_subgrupo`
    FOREIGN KEY (`idsubgrupo`)
    REFERENCES `proj_internato`.`subgrupos` (`idsubgrupo`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_horarios_departamento`
    FOREIGN KEY (`iddepartamento`)
    REFERENCES `proj_internato`.`departamentos` (`iddepartamento`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `fk_horarios_modulo`
    FOREIGN KEY (`idmodulo`)
    REFERENCES `proj_internato`.`modulos` (`idmodulo`),
  CONSTRAINT `fk_horarios_preceptor`
    FOREIGN KEY (`idpreceptor`)
    REFERENCES `proj_internato`.`usuarios` (`idusuario`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_horarios_unidade`
    FOREIGN KEY (`idunidade`)
    REFERENCES `proj_internato`.`unidades` (`idunidade`))
ENGINE = InnoDB
AUTO_INCREMENT = 8
DEFAULT CHARACTER SET = utf8mb3;


-- -----------------------------------------------------
-- Table `proj_internato`.`horarios_supervisao`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `proj_internato`.`horarios_supervisao` (
  `idhorario` INT NOT NULL AUTO_INCREMENT,
  `idpreceptor` INT NOT NULL,
  `idmodulo` INT NULL DEFAULT NULL,
  `idunidade` INT NULL DEFAULT NULL,
  `dia_semana` ENUM('Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo') NOT NULL,
  `hora_inicio` TIME NOT NULL,
  `hora_fim` TIME NOT NULL,
  `turno` VARCHAR(10) NULL DEFAULT NULL,
  PRIMARY KEY (`idhorario`),
  INDEX `idpreceptor` (`idpreceptor` ASC) VISIBLE,
  INDEX `idmodulo` (`idmodulo` ASC) VISIBLE,
  INDEX `idunidade` (`idunidade` ASC) VISIBLE,
  CONSTRAINT `horarios_supervisao_ibfk_1`
    FOREIGN KEY (`idpreceptor`)
    REFERENCES `proj_internato`.`usuarios` (`idusuario`),
  CONSTRAINT `horarios_supervisao_ibfk_2`
    FOREIGN KEY (`idmodulo`)
    REFERENCES `proj_internato`.`modulos` (`idmodulo`),
  CONSTRAINT `horarios_supervisao_ibfk_3`
    FOREIGN KEY (`idunidade`)
    REFERENCES `proj_internato`.`unidades` (`idunidade`))
ENGINE = InnoDB
AUTO_INCREMENT = 71
DEFAULT CHARACTER SET = utf8mb3;


-- -----------------------------------------------------
-- Table `proj_internato`.`modulos_alunos`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `proj_internato`.`modulos_alunos` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `idmodulo` INT NOT NULL,
  `idusuario` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_modulo_idx` (`idmodulo` ASC) VISIBLE,
  INDEX `fk_usuario_idx` (`idusuario` ASC) VISIBLE,
  CONSTRAINT `fk_modulo_alunos_modulo`
    FOREIGN KEY (`idmodulo`)
    REFERENCES `proj_internato`.`modulos` (`idmodulo`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_modulo_alunos_usuario`
    FOREIGN KEY (`idusuario`)
    REFERENCES `proj_internato`.`usuarios` (`idusuario`)
    ON DELETE CASCADE)
ENGINE = InnoDB
AUTO_INCREMENT = 154
DEFAULT CHARACTER SET = utf8mb3;


-- -----------------------------------------------------
-- Table `proj_internato`.`modulos_departamentos`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `proj_internato`.`modulos_departamentos` (
  `iddepartamento` INT NOT NULL,
  `idmodulo` INT NOT NULL,
  PRIMARY KEY (`iddepartamento`, `idmodulo`),
  INDEX `fk_departamento_idx` (`iddepartamento` ASC) VISIBLE,
  INDEX `fk_modulo_idx` (`idmodulo` ASC) VISIBLE,
  CONSTRAINT `fk_departamento`
    FOREIGN KEY (`iddepartamento`)
    REFERENCES `proj_internato`.`departamentos` (`iddepartamento`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_modulo`
    FOREIGN KEY (`idmodulo`)
    REFERENCES `proj_internato`.`modulos` (`idmodulo`)
    ON DELETE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb3;


-- -----------------------------------------------------
-- Table `proj_internato`.`preceptores_modulos`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `proj_internato`.`preceptores_modulos` (
  `idpreceptor_modulo` INT NOT NULL AUTO_INCREMENT,
  `idusuario` INT NOT NULL,
  `idmodulo` INT NOT NULL,
  `data_inicio` DATE NULL DEFAULT NULL,
  `data_fim` DATE NULL DEFAULT NULL,
  PRIMARY KEY (`idpreceptor_modulo`),
  INDEX `idusuario` (`idusuario` ASC) VISIBLE,
  INDEX `idmodulo` (`idmodulo` ASC) VISIBLE,
  CONSTRAINT `preceptores_modulos_ibfk_1`
    FOREIGN KEY (`idusuario`)
    REFERENCES `proj_internato`.`usuarios` (`idusuario`),
  CONSTRAINT `preceptores_modulos_ibfk_2`
    FOREIGN KEY (`idmodulo`)
    REFERENCES `proj_internato`.`modulos` (`idmodulo`))
ENGINE = InnoDB
AUTO_INCREMENT = 27
DEFAULT CHARACTER SET = utf8mb3;


-- -----------------------------------------------------
-- Table `proj_internato`.`preceptores_unidades`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `proj_internato`.`preceptores_unidades` (
  `idpreceptor_unidade` INT NOT NULL AUTO_INCREMENT,
  `idusuario` INT NOT NULL,
  `idunidade` INT NOT NULL,
  `data_inicio` DATE NULL DEFAULT NULL,
  `data_fim` DATE NULL DEFAULT NULL,
  PRIMARY KEY (`idpreceptor_unidade`),
  INDEX `idusuario` (`idusuario` ASC) VISIBLE,
  INDEX `idunidade` (`idunidade` ASC) VISIBLE,
  CONSTRAINT `preceptores_unidades_ibfk_1`
    FOREIGN KEY (`idusuario`)
    REFERENCES `proj_internato`.`usuarios` (`idusuario`),
  CONSTRAINT `preceptores_unidades_ibfk_2`
    FOREIGN KEY (`idunidade`)
    REFERENCES `proj_internato`.`unidades` (`idunidade`))
ENGINE = InnoDB
AUTO_INCREMENT = 17
DEFAULT CHARACTER SET = utf8mb3;


-- -----------------------------------------------------
-- Table `proj_internato`.`rodizios`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `proj_internato`.`rodizios` (
  `idrodizio` INT NOT NULL AUTO_INCREMENT,
  `periodo` INT NOT NULL,
  `inicio` DATE NOT NULL,
  `fim` DATE NOT NULL,
  `idmodulo` INT NOT NULL,
  `grupos` CHAR(1) NULL DEFAULT NULL,
  PRIMARY KEY (`idrodizio`),
  INDEX `fk_rodizio_modulo_idx` (`idmodulo` ASC) VISIBLE,
  CONSTRAINT `fk_rodizio_modulo`
    FOREIGN KEY (`idmodulo`)
    REFERENCES `proj_internato`.`modulos` (`idmodulo`))
ENGINE = InnoDB
AUTO_INCREMENT = 10
DEFAULT CHARACTER SET = utf8mb3;


-- -----------------------------------------------------
-- Table `proj_internato`.`rodizios_subgrupos`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `proj_internato`.`rodizios_subgrupos` (
  `idrodizio_subgrupo` INT NOT NULL AUTO_INCREMENT,
  `idrodizio` INT NOT NULL,
  `idsubgrupo` INT NOT NULL,
  PRIMARY KEY (`idrodizio_subgrupo`),
  INDEX `fk_rodizio_idx` (`idrodizio` ASC) VISIBLE,
  INDEX `fk_subgrupo_idx` (`idsubgrupo` ASC) VISIBLE,
  CONSTRAINT `fk_rodiziosub_rodizio`
    FOREIGN KEY (`idrodizio`)
    REFERENCES `proj_internato`.`rodizios` (`idrodizio`),
  CONSTRAINT `fk_rodiziosub_subgrupo`
    FOREIGN KEY (`idsubgrupo`)
    REFERENCES `proj_internato`.`subgrupos` (`idsubgrupo`))
ENGINE = InnoDB
AUTO_INCREMENT = 28
DEFAULT CHARACTER SET = utf8mb3;


-- -----------------------------------------------------
-- Table `proj_internato`.`unidades_modulos`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `proj_internato`.`unidades_modulos` (
  `idunidade_modulo` INT NOT NULL AUTO_INCREMENT,
  `idmodulo` INT NOT NULL,
  `idunidade` INT NOT NULL,
  PRIMARY KEY (`idunidade_modulo`),
  INDEX `fk_unidade_modulo_unidade_idx` (`idunidade` ASC) VISIBLE,
  INDEX `fk_unidade_modulo_modulo_idx` (`idmodulo` ASC) VISIBLE,
  CONSTRAINT `fk_unidade_modulo_modulo`
    FOREIGN KEY (`idmodulo`)
    REFERENCES `proj_internato`.`modulos` (`idmodulo`),
  CONSTRAINT `fk_unidade_modulo_unidade`
    FOREIGN KEY (`idunidade`)
    REFERENCES `proj_internato`.`unidades` (`idunidade`))
ENGINE = InnoDB
AUTO_INCREMENT = 32
DEFAULT CHARACTER SET = utf8mb3;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
