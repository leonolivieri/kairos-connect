USE u818458777_BDKairos;

-- 1. ESTRUTURA DA TABELA DE CONFIGURAÇÕES (SINCRONIZADA COM IMAGENS)
CREATE TABLE IF NOT EXISTS `kairos_configuracoes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `chave` varchar(100) NOT NULL,
  `valor` text NOT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  `categoria` enum('Analise de Mercado','Configuração','Sistema') DEFAULT 'Sistema',
  `config_group` varchar(30) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `chave_UNIQUE` (`chave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

ALTER TABLE kairos_configuracoes
 ADD COLUMN IF NOT EXISTS `categoria` enum('Analise de Mercado','Configuração','Sistema') DEFAULT 'Sistema',
 ADD COLUMN IF NOT EXISTS `config_group` varchar(30) DEFAULT NULL AFTER categoria,
 ADD COLUMN IF NOT EXISTS `is_active` tinyint(1) DEFAULT '1' AFTER config_group;
 
 -- 3. TABELA DE LOGS DE MENSAGENS (PARA FASE 3)
CREATE TABLE IF NOT EXISTS kairos_mensagens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT DEFAULT 1,
    whatsapp_id VARCHAR(100),
    remetente_numero VARCHAR(20),
    remetente_nome VARCHAR(100),
    mensagem_texto TEXT,
    direcao ENUM('ENTRADA', 'SAIDA') DEFAULT 'ENTRADA',
    status_leitura TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DELETE FROM kairos_configuracoes WHERE chave IN (
    'meta_phone_id', 'meta_waba_id', 'meta_access_token', 
    'meta_verify_token', 'ia_system_prompt', 'horario_inicio', 'horario_fim'
);

INSERT INTO kairos_configuracoes (chave, valor, config_group, is_active) VALUES 
('meta_phone_id', '', 'WHATSAPP_API', 1),
('meta_waba_id', '', 'WHATSAPP_API', 1),
('meta_access_token', '', 'WHATSAPP_API', 1),
('meta_verify_token', 'KAIROS_LAB_2026', 'WHATSAPP_API', 1),
('ia_system_prompt', 'Aja como um assistente executivo da Kairós Ventures. Seja direto e profissional.', 'IA_CONFIG', 1),
('horario_inicio', '08:00', 'EXPEDIENTE', 1),
('horario_fim', '18:00', 'EXPEDIENTE', 1);

