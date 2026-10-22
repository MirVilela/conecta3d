-- ============================================================
-- Dados fictícios para teste da plataforma
-- ============================================================
-- Senhas: todos os usuários fictícios abaixo usam a senha "123456".
-- O valor em `senha` é o hash real gerado por
-- password_hash('123456', PASSWORD_DEFAULT), pronto para login.
-- ============================================================

USE oficinahotrods_joaodavi_banco_projeto;

INSERT INTO usuarios (nome, email, senha, tipo, telefone, cidade) VALUES
('Maria Solicitante',   'maria@exemplo.com',   '$2y$10$bpYnaEENIk3q2L6yJUJ4pub6.gRD4MDSg3s7.1NIxncdokCgKIeSe', 'solicitante', '(35) 99999-0001', 'São Lourenço'),
('Clínica Vida Nova',   'clinica@exemplo.com', '$2y$10$bpYnaEENIk3q2L6yJUJ4pub6.gRD4MDSg3s7.1NIxncdokCgKIeSe', 'clinica',     '(35) 99999-0002', 'São Lourenço'),
('João Voluntário',     'joao@exemplo.com',    '$2y$10$bpYnaEENIk3q2L6yJUJ4pub6.gRD4MDSg3s7.1NIxncdokCgKIeSe', 'voluntario',  '(35) 99999-0003', 'Varginha'),
('Ana Voluntária',      'ana@exemplo.com',     '$2y$10$bpYnaEENIk3q2L6yJUJ4pub6.gRD4MDSg3s7.1NIxncdokCgKIeSe', 'voluntario',  '(35) 99999-0004', 'Pouso Alegre');

-- Demandas de exemplo
INSERT INTO demandas (solicitante_id, voluntario_id, titulo, descricao, tipo_peca, status) VALUES
(1, NULL, 'Adaptador de escova de dente',       'Preciso de um adaptador ergonômico para segurar a escova de dente, dificuldade motora na mão direita.', 'ferramenta ergonômica', 'aberta'),
(2, 3,    'Órtese simples para dedo',           'Órtese leve para imobilização parcial do dedo indicador, uso temporário.',                              'órtese', 'em_andamento'),
(1, 4,    'Suporte adaptado para talher',       'Suporte para facilitar preensão de talher durante refeições.',                                         'ferramenta ergonômica', 'concluida');

-- Histórico de status correspondente às demandas acima
INSERT INTO historico_status (demanda_id, usuario_id, status_anterior, status_novo, observacao) VALUES
(2, 3, 'aberta', 'em_andamento', 'Voluntário aceitou a demanda e iniciou a impressão.'),
(3, 4, 'aberta', 'em_andamento', 'Peça em processo de impressão.'),
(3, 4, 'em_andamento', 'concluida', 'Peça impressa e entregue ao solicitante.');
