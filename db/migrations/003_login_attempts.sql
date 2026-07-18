USE proj_internato;

-- Controle de tentativas de login para mitigar força bruta.
CREATE TABLE IF NOT EXISTS login_attempts (
  idlogin_attempt INT NOT NULL AUTO_INCREMENT,
  login VARCHAR(90) NOT NULL,
  tentativas INT NOT NULL DEFAULT 0,
  bloqueado_ate DATETIME NULL DEFAULT NULL,
  atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (idlogin_attempt),
  UNIQUE KEY idx_login_attempts_login (login)
) ENGINE=InnoDB DEFAULT CHARACTER SET=utf8mb3;
