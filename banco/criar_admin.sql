-- Cria o usuário administrador. Rode depois de criar_tabelas.sql
-- (pela aba Importar ou SQL do phpMyAdmin).
--
-- Senha: "123456". O valor em `senha` é o hash real gerado por
-- password_hash('123456', PASSWORD_DEFAULT), pronto para login.

USE oficinahotrods_joaodavi_banco_projeto;

INSERT INTO usuarios (nome, email, senha, tipo, telefone, cidade)
VALUES ('Administrador', 'admin@conecta3d.com', '$2y$10$bpYnaEENIk3q2L6yJUJ4pub6.gRD4MDSg3s7.1NIxncdokCgKIeSe', 'admin', NULL, NULL);
