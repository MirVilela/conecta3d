-- ============================================================
-- Migração 001: demanda inativa em vez de exclusão
-- ============================================================
-- Rode apenas em bancos criados ANTES desta coluna existir em
-- criar_tabelas.sql. Bancos criados com o criar_tabelas.sql atual
-- já têm a coluna e não precisam desta migração.

USE oficinahotrods_joaodavi_banco_projeto;

ALTER TABLE demandas
    ADD COLUMN ativa TINYINT(1) NOT NULL DEFAULT 1 AFTER status;
