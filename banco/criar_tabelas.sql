-- ============================================================
-- Banco de dados: plataforma de conexão pessoas/clínicas x
-- voluntários com impressoras 3D
-- ============================================================

CREATE DATABASE IF NOT EXISTS oficinahotrods_joaodavi_banco_projeto
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE oficinahotrods_joaodavi_banco_projeto;

-- ------------------------------------------------------------
-- Tabela: usuarios
-- Guarda tanto quem solicita peças (solicitante/clinica)
-- quanto quem se voluntaria para imprimir (voluntario).
-- ------------------------------------------------------------
CREATE TABLE usuarios (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    nome           VARCHAR(150)        NOT NULL,
    email          VARCHAR(150)        NOT NULL,
    senha          VARCHAR(255)        NOT NULL,   -- hash (password_hash)
    tipo           ENUM('solicitante', 'clinica', 'voluntario', 'admin') NOT NULL,
    telefone       VARCHAR(20)         NULL,
    cidade         VARCHAR(100)        NULL,
    criado_em      DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT uq_usuarios_email UNIQUE (email)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Tabela: demandas
-- Cada pedido de peça feito por um solicitante/clinica,
-- que pode (ou não, ainda) estar associado a um voluntário.
-- ------------------------------------------------------------
CREATE TABLE demandas (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    solicitante_id    INT                 NOT NULL,
    voluntario_id     INT                 NULL,
    titulo            VARCHAR(150)        NOT NULL,
    descricao         TEXT                NOT NULL,
    tipo_peca         VARCHAR(100)        NULL,     -- ex: "engrossador de talher", "suporte de utensílio"
    status            ENUM('aberta', 'em_andamento', 'concluida', 'cancelada')
                                           NOT NULL DEFAULT 'aberta',
    ativa             TINYINT(1)          NOT NULL DEFAULT 1,  -- 0 = inativada pelo admin
    criado_em         DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em     DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP
                                           ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_demandas_solicitante
        FOREIGN KEY (solicitante_id) REFERENCES usuarios(id)
        ON DELETE RESTRICT,

    CONSTRAINT fk_demandas_voluntario
        FOREIGN KEY (voluntario_id) REFERENCES usuarios(id)
        ON DELETE SET NULL,

    INDEX idx_demandas_status (status)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Tabela: historico_status
-- Registra cada mudança de status de uma demanda: quem
-- alterou, de qual status para qual, e quando.
-- ------------------------------------------------------------
CREATE TABLE historico_status (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    demanda_id       INT                 NOT NULL,
    usuario_id       INT                 NOT NULL,  -- quem fez a alteração
    status_anterior  ENUM('aberta', 'em_andamento', 'concluida', 'cancelada') NULL,
    status_novo      ENUM('aberta', 'em_andamento', 'concluida', 'cancelada') NOT NULL,
    observacao       TEXT                NULL,
    data_alteracao   DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_historico_demanda
        FOREIGN KEY (demanda_id) REFERENCES demandas(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_historico_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON DELETE RESTRICT,

    INDEX idx_historico_demanda (demanda_id)
) ENGINE=InnoDB;
