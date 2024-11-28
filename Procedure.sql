DELIMITER $$

CREATE PROCEDURE associar_aluno_subgrupo(
    IN p_idusuario INT,
    IN p_idsubgrupo INT
)
BEGIN
    -- Verifica se o aluno já está associado ao subgrupo
    IF NOT EXISTS (SELECT 1 FROM proj_internato.alunos_subgrupos WHERE idusuario = p_idusuario AND idsubgrupo = p_idsubgrupo) THEN
        INSERT INTO proj_internato.alunos_subgrupos (idusuario, idsubgrupo)
        VALUES (p_idusuario, p_idsubgrupo);
    ELSE
        SELECT 'Aluno já está associado ao subgrupo.' AS Mensagem;
    END IF;
END $$

DELIMITER ;
