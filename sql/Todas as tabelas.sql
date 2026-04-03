-- =============================================================================
-- KAIRÓS VENTURES - SCRIPT DE MIGRAÇÃO PARA ARQUITETURA SAAS MULTI-TENANT
-- OBJETIVO: Implementar Isolamento Lógico e Relação Usuário x Workspace (N:N)
-- DATA: 2026-04-01
-- =============================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- 1. CRIAÇÃO DA TABELA DE MEMBROS (PIVOT)
-- Permite que um usuário pertença a vários workspaces (Visão 360)
CREATE TABLE IF NOT EXISTS workspace_members (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) UNSIGNED NOT NULL,
    workspace_id INT(11) NOT NULL,
    role ENUM('OWNER', 'ADMIN', 'EDITOR', 'VIEWER') DEFAULT 'VIEWER',
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_member_user FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    CONSTRAINT fk_member_workspace FOREIGN KEY (workspace_id) REFERENCES workspaces(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. MIGRAÇÃO DE DADOS EXISTENTES (BACKUP DE RELAÇÕES)
-- Move as relações atuais de 'usuarios.workspace_id' para a nova tabela 'workspace_members'
INSERT INTO workspace_members (user_id, workspace_id, role)
SELECT id, workspace_id, 'OWNER' FROM usuarios WHERE workspace_id IS NOT NULL;

-- 3. REFATORAÇÃO DA TABELA DE USUÁRIOS
-- Remove o vínculo fixo com um único workspace
ALTER TABLE usuarios DROP FOREIGN KEY fk_usuarios_workspaces;
ALTER TABLE usuarios DROP COLUMN workspace_id;
-- Ajuste da Unique Key: Agora um e-mail pode existir, mas a unicidade é global.
-- Nota: Se quiser permitir e-mails duplicados em workspaces diferentes, a lógica de login muda.
-- Manteremos email UNIQUE globalmente para evitar confusão de identidade.

-- 4. REFATORAÇÃO DE MENSAGENS (TRANSFORMAÇÃO EM MULTI-TENANT)
ALTER TABLE kairos_mensagens CHANGE cliente_id workspace_id INT(11) DEFAULT NULL;
ALTER TABLE kairos_mensagens ADD CONSTRAINT fk_mensagens_workspace FOREIGN KEY (workspace_id) REFERENCES workspaces(id);
CREATE INDEX idx_msg_tenant ON kairos_mensagens(workspace_id);

-- 5. REFATORAÇÃO DE CONFIGURAÇÕES (SISTEMA DE HERANÇA)
-- Se workspace_id for NULL, é uma configuração MASTER. Se tiver ID, é customizada.
ALTER TABLE kairos_configuracoes ADD COLUMN workspace_id INT(11) NULL AFTER id;
ALTER TABLE kairos_configuracoes ADD CONSTRAINT fk_config_workspace FOREIGN KEY (workspace_id) REFERENCES workspaces(id);
CREATE INDEX idx_config_tenant ON kairos_configuracoes(workspace_id);

-- 6. REFATORAÇÃO DE CONHECIMENTO (DOUTRINA GLOBAL VS PRIVADA)
ALTER TABLE kairos_conhecimento ADD COLUMN workspace_id INT(11) NULL AFTER id;
ALTER TABLE kairos_conhecimento ADD COLUMN is_global TINYINT(1) DEFAULT 0 AFTER workspace_id;
ALTER TABLE kairos_conhecimento ADD CONSTRAINT fk_conhecimento_workspace FOREIGN KEY (workspace_id) REFERENCES workspaces(id);
CREATE INDEX idx_conhec_tenant ON kairos_conhecimento(workspace_id);

-- 7. REFATORAÇÃO DE CONVERSAS E CÓDICE (HISTÓRICO POR CONTEXTO)
ALTER TABLE conversas ADD COLUMN workspace_id INT(11) NOT NULL AFTER id;
ALTER TABLE conversas ADD CONSTRAINT fk_conversas_workspace FOREIGN KEY (workspace_id) REFERENCES workspaces(id);
CREATE INDEX idx_conv_tenant ON conversas(workspace_id);

ALTER TABLE codice ADD COLUMN workspace_id INT(11) NOT NULL AFTER id;
ALTER TABLE codice ADD CONSTRAINT fk_codice_workspace FOREIGN KEY (workspace_id) REFERENCES workspaces(id);
CREATE INDEX idx_codice_tenant ON codice(workspace_id);

-- 8. BLINDAGEM DE SESSÕES (EVITAR COLISÃO DE TELEFONE)
-- Remove a PK antiga e cria uma PK Composta (Workspace + Telefone)
ALTER TABLE kairos_sessoes DROP PRIMARY KEY;
ALTER TABLE kairos_sessoes ADD COLUMN workspace_id INT(11) NOT NULL FIRST;
ALTER TABLE kairos_sessoes ADD CONSTRAINT fk_sessoes_workspace FOREIGN KEY (workspace_id) REFERENCES workspaces(id);
ALTER TABLE kairos_sessoes ADD PRIMARY KEY (workspace_id, telefone_cliente);

SET FOREIGN_KEY_CHECKS = 1;