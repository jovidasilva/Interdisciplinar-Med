-- Migration 002: tabela de tokens para o fluxo "esqueci minha senha".
--
-- Guarda apenas o HASH (sha256) do token enviado por e-mail — o token em
-- texto puro nunca é persistido, só existe no link enviado ao usuário.
-- Compatível com o schema existente em db/schema.sql (mesmo engine/charset).

USE `proj_internato`;

CREATE TABLE IF NOT EXISTS `proj_internato`.`password_reset_tokens` (
  `idtoken` INT NOT NULL AUTO_INCREMENT,
  `idusuario` INT NOT NULL,
  `token_hash` VARCHAR(64) NOT NULL,
  `expira_em` DATETIME NOT NULL,
  `usado` TINYINT NOT NULL DEFAULT 0,
  `criado_em` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idtoken`),
  INDEX `idx_token_hash` (`token_hash` ASC),
  INDEX `fk_password_reset_tokens_usuario_idx` (`idusuario` ASC),
  CONSTRAINT `fk_password_reset_tokens_usuario`
    FOREIGN KEY (`idusuario`)
    REFERENCES `proj_internato`.`usuarios` (`idusuario`)
    ON DELETE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb3;
