USE proj_internato;

-- A nota é a média das respostas (ex.: 7,33); como INT ela era truncada.
ALTER TABLE avaliacoes MODIFY nota DECIMAL(4,2) NOT NULL;
