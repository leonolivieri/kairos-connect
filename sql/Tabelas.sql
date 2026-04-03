SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

CREATE TABLE cidades (
  id int(11) NOT NULL,
  estado_id int(11) NOT NULL,
  nome varchar(100) NOT NULL,
  slug varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE codice (
  id int(11) UNSIGNED NOT NULL,
  id_usuario int(11) UNSIGNED NOT NULL,
  titulo varchar(255) NOT NULL,
  conteudo text NOT NULL,
  data_criacao timestamp NULL DEFAULT current_timestamp(),
  data_atualizacao timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE conversas (
  id int(11) UNSIGNED NOT NULL,
  id_usuario int(11) UNSIGNED NOT NULL,
  titulo varchar(255) NOT NULL,
  data_criacao timestamp NULL DEFAULT current_timestamp(),
  data_atualizacao timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE estados (
  id int(11) NOT NULL,
  nome varchar(50) NOT NULL,
  sigla char(2) NOT NULL,
  pais_id int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE kairos_configuracoes (
  id int(11) NOT NULL,
  chave varchar(100) NOT NULL,
  valor text NOT NULL,
  descricao varchar(255) DEFAULT NULL,
  categoria enum('Analise de Mercado','Configuração','Sistema') DEFAULT 'Sistema',
  config_group varchar(30) DEFAULT NULL,
  is_active tinyint(1) DEFAULT 1,
  is_secret tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

CREATE TABLE kairos_conhecimento (
  id int(11) NOT NULL,
  titulo_popular varchar(255) NOT NULL,
  titulo_tecnico varchar(255) DEFAULT NULL,
  slug varchar(255) NOT NULL,
  resumo_home varchar(500) DEFAULT NULL,
  conteudo text NOT NULL,
  categoria enum('Analise de Mercado','Doutrina Tecnica','Manual de Ferramenta') DEFAULT 'Analise de Mercado',
  escopo enum('Local','Regional','Estadual','Nacional') DEFAULT 'Nacional',
  setor_alvo varchar(100) DEFAULT NULL,
  status enum('Rascunho','Ativo','Arquivado') DEFAULT 'Rascunho',
  is_destaque tinyint(1) DEFAULT 0,
  data_criacao datetime DEFAULT current_timestamp(),
  data_publicacao datetime DEFAULT NULL,
  data_revisao datetime DEFAULT NULL ON UPDATE current_timestamp(),
  meta_descricao varchar(160) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE kairos_mensagens (
  id int(11) NOT NULL,
  cliente_id int(11) DEFAULT 1,
  whatsapp_id varchar(100) DEFAULT NULL,
  remetente_numero varchar(20) DEFAULT NULL,
  remetente_nome varchar(100) DEFAULT NULL,
  mensagem_texto text DEFAULT NULL,
  direcao enum('ENTRADA','SAIDA') DEFAULT 'ENTRADA',
  status_leitura tinyint(1) DEFAULT 0,
  created_at timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;


CREATE TABLE kairos_sessoes (
  telefone_cliente varchar(50) NOT NULL,
  ia_responde tinyint(1) DEFAULT 1,
  data_intervencao datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

CREATE TABLE paises (
  id int(11) NOT NULL,
  nome varchar(100) NOT NULL,
  sigla char(2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

CREATE TABLE registros (
  id int(11) UNSIGNED NOT NULL,
  id_conversa int(11) UNSIGNED NOT NULL,
  autor varchar(10) NOT NULL,
  registro text NOT NULL,
  data_registro timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE usuarios (
  id int(11) UNSIGNED NOT NULL,
  workspace_id int(11) NOT NULL,
  nome varchar(255) NOT NULL,
  foto_url varchar(255) DEFAULT NULL,
  fundo_url varchar(255) DEFAULT NULL,
  cargo varchar(100) NOT NULL,
  bio text DEFAULT NULL,
  data_nascimento date DEFAULT NULL,
  sexo varchar(20) DEFAULT NULL,
  links_sociais text DEFAULT NULL,
  email varchar(100) NOT NULL,
  senha varchar(255) NOT NULL,
  data_criacao timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE workspaces (
  id int(11) NOT NULL,
  nome_empresa varchar(255) NOT NULL,
  plano_assinatura varchar(50) NOT NULL,
  data_criacao timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


ALTER TABLE cidades
  ADD PRIMARY KEY (id),
  ADD KEY fk_cidades_estados (estado_id);

ALTER TABLE codice
  ADD PRIMARY KEY (id),
  ADD KEY id_usuario (id_usuario);

ALTER TABLE conversas
  ADD PRIMARY KEY (id),
  ADD KEY id_usuario (id_usuario);

ALTER TABLE estados
  ADD PRIMARY KEY (id),
  ADD KEY fk_estado_pais (pais_id);

ALTER TABLE kairos_configuracoes
  ADD PRIMARY KEY (id),
  ADD UNIQUE KEY chave (chave);

ALTER TABLE kairos_conhecimento
  ADD PRIMARY KEY (id),
  ADD UNIQUE KEY slug (slug);

ALTER TABLE kairos_mensagens
  ADD PRIMARY KEY (id);

ALTER TABLE kairos_sessoes
  ADD PRIMARY KEY (telefone_cliente);

ALTER TABLE paises
  ADD PRIMARY KEY (id),
  ADD UNIQUE KEY sigla (sigla);

ALTER TABLE registros
  ADD PRIMARY KEY (id),
  ADD KEY id_conversa (id_conversa);

ALTER TABLE usuarios
  ADD PRIMARY KEY (id),
  ADD UNIQUE KEY email (email),
  ADD KEY fk_usuarios_workspaces (workspace_id);

ALTER TABLE workspaces
  ADD PRIMARY KEY (id);


ALTER TABLE cidades
  MODIFY id int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE codice
  MODIFY id int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE conversas
  MODIFY id int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE estados
  MODIFY id int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE kairos_configuracoes
  MODIFY id int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE kairos_conhecimento
  MODIFY id int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE kairos_mensagens
  MODIFY id int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE paises
  MODIFY id int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE registros
  MODIFY id int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE usuarios
  MODIFY id int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE workspaces
  MODIFY id int(11) NOT NULL AUTO_INCREMENT;


ALTER TABLE cidades
  ADD CONSTRAINT fk_cidades_estados FOREIGN KEY (estado_id) REFERENCES estados (id);

ALTER TABLE codice
  ADD CONSTRAINT codice_ibfk_1 FOREIGN KEY (id_usuario) REFERENCES usuarios (id) ON DELETE CASCADE;

ALTER TABLE conversas
  ADD CONSTRAINT conversas_ibfk_1 FOREIGN KEY (id_usuario) REFERENCES usuarios (id) ON DELETE CASCADE;

ALTER TABLE estados
  ADD CONSTRAINT fk_estado_pais FOREIGN KEY (pais_id) REFERENCES paises (id);

ALTER TABLE registros
  ADD CONSTRAINT registros_ibfk_1 FOREIGN KEY (id_conversa) REFERENCES conversas (id) ON DELETE CASCADE;

ALTER TABLE usuarios
  ADD CONSTRAINT fk_usuarios_workspaces FOREIGN KEY (workspace_id) REFERENCES workspaces (id);
