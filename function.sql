DELIMITER $$

CREATE FUNCTION obter_modulos_preceptor(p_idpreceptor INT) 
RETURNS TEXT
DETERMINISTIC
BEGIN
    DECLARE resultado TEXT DEFAULT '';
    -- Concatenar os nomes dos módulos atribuídos ao preceptor
    SELECT GROUP_CONCAT(m.nome_modulo ORDER BY m.nome_modulo SEPARATOR ', ')
    INTO resultado
    FROM proj_internato.preceptores_modulos pm
    JOIN proj_internato.modulos m ON pm.idmodulo = m.idmodulo
    WHERE pm.idusuario = p_idpreceptor;
    
    RETURN resultado;
END $$

DELIMITER ;
