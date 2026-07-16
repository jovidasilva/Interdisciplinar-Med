-- Usuário de coordenação padrão para testar o sistema logo após subir o
-- ambiente Docker. Login: admin | Senha: admin123
-- (troque essa senha antes de usar em qualquer ambiente que não seja local/demo)
INSERT INTO usuarios (nome, email, telefone, login, senha, tipo, ativo, registro, periodo)
VALUES (
    'Administrador',
    'admin@ceuma.com',
    '(99) 90000-0000',
    'admin',
    '$2b$10$huhuIo16VVXbOSFiQZU0N.DV3v.42NSxEPcwRjOuxN361xjgCUyfy',
    2,
    1,
    'ADMIN001',
    NULL
);
