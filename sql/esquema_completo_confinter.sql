-- =====================================================
-- ESQUEMA COMPLETO DO BANCO DE DADOS - CONFINTER
-- Sistema de Análise e Gestão para Correspondente Bancário
-- Data: 06 de Setembro de 2025
-- Versão: 2.0 - Esquema Consolidado
-- =====================================================

-- Configurações iniciais
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- =====================================================
-- BANCO DE DADOS: confinter
-- =====================================================

-- Criação do banco de dados (se não existir)
CREATE DATABASE IF NOT EXISTS `confinter` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `confinter`;

-- =====================================================
-- 1. TABELA: usuarios
-- Sistema de autenticação e perfis de usuário
-- =====================================================

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `usuario` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `senha` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `perfil` enum('usuario','admin') NOT NULL DEFAULT 'usuario',
  `img_perfil` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `telefone` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `data_cadastro` timestamp DEFAULT CURRENT_TIMESTAMP,
  `ultimo_acesso` timestamp NULL DEFAULT NULL,
  `status` enum('ativo','inativo') DEFAULT 'ativo',
  `token` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario` (`usuario`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- Dados iniciais da tabela usuarios
INSERT INTO `usuarios` (`id`, `nome`, `email`, `usuario`, `senha`, `perfil`, `img_perfil`, `telefone`, `data_cadastro`, `status`) VALUES
(1, 'Administrador do Sistema', 'admin@confinter.com', 'admin', MD5('admin'), 'admin', NULL, NULL, CURRENT_TIMESTAMP, 'ativo'),
(2, 'Fernando Lopes', 'fnando0506@gmail.com', 'fnando', MD5('0m3g4r3d'), 'usuario', NULL, '(11) 99204-3469', CURRENT_TIMESTAMP, 'ativo');

-- =====================================================
-- 2. TABELA: adm (LEGACY - para compatibilidade)
-- Tabela de administradores (mantida para compatibilidade)
-- =====================================================

DROP TABLE IF EXISTS `adm`;
CREATE TABLE `adm` (
  `id_usuario` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `usuario` varchar(50) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `perfil` varchar(20) DEFAULT 'admin',
  `data_cadastro` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `usuario` (`usuario`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- Dados iniciais da tabela adm
INSERT INTO `adm` (`id_usuario`, `nome`, `email`, `usuario`, `senha`, `perfil`) VALUES
(1, 'Administrador do Sistema', 'admin@confinter.com', 'admin', MD5('admin'), 'admin');

-- =====================================================
-- 3. TABELA: clientes
-- Cadastro de clientes do sistema
-- =====================================================

DROP TABLE IF EXISTS `clientes`;
CREATE TABLE `clientes` (
  `id_cliente` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `data_nascimento` date DEFAULT NULL,
  `email` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `telefone` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `cpf` varchar(14) DEFAULT NULL,
  `rg` varchar(20) DEFAULT NULL,
  `endereco` text,
  `bairro` varchar(50) DEFAULT NULL,
  `cidade` varchar(50) DEFAULT NULL,
  `estado` varchar(2) DEFAULT NULL,
  `cep` varchar(10) DEFAULT NULL,
  `data_cadastro` timestamp DEFAULT CURRENT_TIMESTAMP,
  `status` enum('ativo','inativo') DEFAULT 'ativo',
  PRIMARY KEY (`id_cliente`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `cpf` (`cpf`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- Dados de exemplo da tabela clientes
INSERT INTO `clientes` (`id_cliente`, `nome`, `data_nascimento`, `email`, `telefone`, `data_cadastro`, `status`) VALUES
(35, 'Fernando Aparecido Lopes da Silva', '1970-01-01', 'fnando0506@gmail.com', '(55) 11992-0434', CURRENT_TIMESTAMP, 'ativo'),
(36, 'João da Silva', '1955-08-26', 'teste1@gmail.com', '1147459055', CURRENT_TIMESTAMP, 'ativo'),
(37, 'Maria Santos', '1980-03-15', 'maria.santos@email.com', '(11) 99876-5432', CURRENT_TIMESTAMP, 'ativo');

-- =====================================================
-- 4. TABELA: contador_visitas
-- Sistema de contagem e análise de visitas
-- =====================================================

DROP TABLE IF EXISTS `contador_visitas`;
CREATE TABLE `contador_visitas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `data_visita` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `tempo` time DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `pagina` varchar(255) DEFAULT NULL,
  `sessao_id` varchar(255) DEFAULT NULL,
  `referer` varchar(500) DEFAULT NULL,
  `pais` varchar(50) DEFAULT NULL,
  `cidade` varchar(100) DEFAULT NULL,
  `navegador` varchar(100) DEFAULT NULL,
  `dispositivo` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `data_visita` (`data_visita`),
  KEY `ip_address` (`ip_address`),
  KEY `sessao_id` (`sessao_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- Dados de exemplo da tabela contador_visitas
INSERT INTO `contador_visitas` (`id`, `data_visita`, `tempo`, `ip_address`, `user_agent`, `pagina`, `sessao_id`) VALUES
(1, '2024-04-20 19:39:05', '00:05:30', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', '/', 'abc123def456'),
(2, '2024-04-20 19:42:00', '00:03:15', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', '/index.php', 'def456ghi789'),
(3, '2024-04-20 19:42:01', '00:02:45', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', '/sobre.php', 'ghi789jkl012');

-- =====================================================
-- 5. TABELA: depoimentos
-- Sistema de depoimentos e avaliações
-- =====================================================

DROP TABLE IF EXISTS `depoimentos`;
CREATE TABLE `depoimentos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome_cliente` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `mensagem` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `status_mod` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT 'pendente',
  `nome` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `aprovado` tinyint(1) DEFAULT '0',
  `reprovado` tinyint(1) NOT NULL DEFAULT '0',
  `data_envio` timestamp DEFAULT CURRENT_TIMESTAMP,
  `data_moderacao` timestamp NULL DEFAULT NULL,
  `moderador_id` int(11) DEFAULT NULL,
  `avaliacao` int(1) DEFAULT NULL COMMENT '1-5 estrelas',
  PRIMARY KEY (`id`),
  KEY `status_mod` (`status_mod`),
  KEY `aprovado` (`aprovado`),
  KEY `moderador_id` (`moderador_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- Dados de exemplo da tabela depoimentos
INSERT INTO `depoimentos` (`id`, `nome_cliente`, `mensagem`, `status_mod`, `nome`, `aprovado`, `reprovado`, `data_envio`, `avaliacao`) VALUES
(14, 'Fernando', 'Muito bom, recomendo!!!', 'aprovado', 'Anônimo', 1, 0, '2024-04-15 10:30:00', 5),
(15, 'João Silva', 'Excelente atendimento e rapidez no processo!', 'aprovado', 'Anônimo', 1, 0, '2024-04-16 14:20:00', 5),
(16, 'Maria Santos', 'Profissionais muito competentes.', 'pendente', 'Anônimo', 0, 0, '2024-04-17 09:15:00', 4);

-- =====================================================
-- 6. TABELA: empresa
-- Informações da empresa
-- =====================================================

DROP TABLE IF EXISTS `empresa`;
CREATE TABLE `empresa` (
  `id_empresa` int(11) NOT NULL AUTO_INCREMENT,
  `nome_empresa` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `tel` varchar(30) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `celular` varchar(30) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `email` varchar(65) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `descricao` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `cnpj` varchar(18) DEFAULT NULL,
  `site` varchar(100) DEFAULT NULL,
  `facebook` varchar(100) DEFAULT NULL,
  `instagram` varchar(100) DEFAULT NULL,
  `linkedin` varchar(100) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_empresa`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- Dados da empresa
INSERT INTO `empresa` (`id_empresa`, `nome_empresa`, `tel`, `celular`, `email`, `descricao`, `site`) VALUES
(1, 'CONFINTER', '(11) 3456-7890', '(11) 99876-5432', 'contato@confinter.com', 'CONFINTER - Consolidando seus sonhos com excelência em serviços financeiros.', 'https://www.confinter.com.br');

-- =====================================================
-- 7. TABELA: enderecos
-- Endereços da empresa
-- =====================================================

DROP TABLE IF EXISTS `enderecos`;
CREATE TABLE `enderecos` (
  `id_endereco` int(11) NOT NULL AUTO_INCREMENT,
  `id_empresa` int(11) NOT NULL,
  `logradouro` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `numero` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `bairro` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `cidade` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `estado` varchar(2) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `cep` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `complemento` varchar(100) DEFAULT NULL,
  `tipo` enum('principal','filial','escritorio') DEFAULT 'principal',
  PRIMARY KEY (`id_endereco`),
  KEY `id_empresa` (`id_empresa`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- Endereço da empresa
INSERT INTO `enderecos` (`id_endereco`, `id_empresa`, `logradouro`, `numero`, `bairro`, `cidade`, `estado`, `cep`, `tipo`) VALUES
(1, 1, 'Marina La Regina', '203', 'Centro', 'Poá', 'SP', '08550-210', 'principal');

-- =====================================================
-- 8. TABELA: imagens_carrossel
-- Sistema de carrossel de imagens do site
-- =====================================================

DROP TABLE IF EXISTS `imagens_carrossel`;
CREATE TABLE `imagens_carrossel` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome_arquivo` varchar(255) NOT NULL,
  `titulo` varchar(100) DEFAULT NULL,
  `descricao` text,
  `ordem` int(11) DEFAULT '0',
  `ativo` tinyint(1) DEFAULT '1',
  `data_upload` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ativo` (`ativo`),
  KEY `ordem` (`ordem`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- Imagens do carrossel
INSERT INTO `imagens_carrossel` (`id`, `nome_arquivo`, `titulo`, `descricao`, `ordem`, `ativo`) VALUES
(8, 'slider1.jpg', 'Bem-vindo à CONFINTER', 'Sua solução completa em serviços financeiros', 1, 1),
(9, 'slider2.jpg', 'Crédito Consignado', 'Taxas especiais para aposentados e pensionistas', 2, 1),
(10, 'slider3.jpg', 'Atendimento Personalizado', 'Profissionais qualificados prontos para ajudar', 3, 1),
(11, 'slider4.jpg', 'Segurança Garantida', 'Seus dados protegidos com tecnologia avançada', 4, 1),
(12, 'slider5.jpg', 'Resultados Comprovados', 'Milhares de clientes satisfeitos', 5, 1);

-- =====================================================
-- 9. TABELA: requisicoes
-- Sistema de requisições de crédito
-- =====================================================

DROP TABLE IF EXISTS `requisicoes`;
CREATE TABLE `requisicoes` (
  `id_requisicao` int(11) NOT NULL AUTO_INCREMENT,
  `id_cliente` int(11) NOT NULL,
  `horario_contato` time NOT NULL,
  `cotacao` enum('Sim','Não') NOT NULL,
  `contratacao` enum('Sim','Não') NOT NULL,
  `tipo` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `categoria` enum('Aposentado','Pensionista','Servidor Público') NOT NULL,
  `outros_info` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `data_requisicao` date NOT NULL,
  `data_hora` datetime DEFAULT CURRENT_TIMESTAMP,
  `status` enum('pendente','em_analise','aprovado','reprovado','cancelado') DEFAULT 'pendente',
  `analista_id` int(11) DEFAULT NULL,
  `observacoes` text,
  `valor_solicitado` decimal(10,2) DEFAULT NULL,
  `prazo` int(3) DEFAULT NULL,
  PRIMARY KEY (`id_requisicao`),
  KEY `id_cliente` (`id_cliente`),
  KEY `status` (`status`),
  KEY `data_requisicao` (`data_requisicao`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- Dados de exemplo da tabela requisicoes
INSERT INTO `requisicoes` (`id_requisicao`, `id_cliente`, `horario_contato`, `cotacao`, `contratacao`, `tipo`, `categoria`, `outros_info`, `data_requisicao`, `status`, `valor_solicitado`, `prazo`) VALUES
(17, 35, '07:26:00', 'Sim', 'Sim', 'Crédito Consignado', 'Aposentado', 'Cliente fiel, bom pagador', '2024-03-27', 'aprovado', 5000.00, 24),
(18, 36, '08:00:00', 'Sim', 'Sim', 'Empréstimo Pessoal', 'Pensionista', 'Primeira solicitação', '2024-03-27', 'em_analise', 3000.00, 12),
(19, 37, '09:00:00', 'Não', 'Sim', 'Refinanciamento', 'Servidor Público', 'Cliente com restrições antigas', '2024-04-01', 'pendente', 8000.00, 36);

-- =====================================================
-- 10. TABELA: tempo_visita
-- Controle de tempo de permanência no site
-- =====================================================

DROP TABLE IF EXISTS `tempo_visita`;
CREATE TABLE `tempo_visita` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_visita` int(11) NOT NULL,
  `tempo` time NOT NULL,
  `data_registro` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_visita` (`id_visita`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- =====================================================
-- 11. TABELA: reset_senha
-- Sistema de recuperação de senha
-- =====================================================

DROP TABLE IF EXISTS `reset_senha`;
CREATE TABLE `reset_senha` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `data_solicitacao` timestamp DEFAULT CURRENT_TIMESTAMP,
  `status` enum('pendente','processado','expirado') DEFAULT 'pendente',
  `nova_senha` varchar(255) DEFAULT NULL,
  `data_processamento` timestamp NULL DEFAULT NULL,
  `expira_em` timestamp DEFAULT (CURRENT_TIMESTAMP + INTERVAL 24 HOUR),
  PRIMARY KEY (`id`),
  KEY `id_usuario` (`id_usuario`),
  KEY `token` (`token`),
  KEY `status` (`status`),
  KEY `expira_em` (`expira_em`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- =====================================================
-- 12. TABELA: previsoes_pico
-- Sistema de previsões de horários de pico
-- =====================================================

DROP TABLE IF EXISTS `previsoes_pico`;
CREATE TABLE `previsoes_pico` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `data_previsao` date NOT NULL,
  `hora_previsao` time NOT NULL,
  `previsao_visitas` int(11) NOT NULL,
  `modelo_usado` varchar(50) DEFAULT 'PHP-SimpleML',
  `acuracia` decimal(5,2) DEFAULT NULL,
  `data_criacao` timestamp DEFAULT CURRENT_TIMESTAMP,
  `fator_ajuste` decimal(5,2) DEFAULT NULL,
  `classificacao` enum('normal','medio','alto') DEFAULT 'normal',
  PRIMARY KEY (`id`),
  KEY `data_previsao` (`data_previsao`),
  KEY `hora_previsao` (`hora_previsao`),
  KEY `classificacao` (`classificacao`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- =====================================================
-- 13. TABELA: logs_sistema
-- Sistema de auditoria e logs
-- =====================================================

DROP TABLE IF EXISTS `logs_sistema`;
CREATE TABLE `logs_sistema` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) DEFAULT NULL,
  `acao` varchar(100) NOT NULL,
  `tabela_afetada` varchar(50) DEFAULT NULL,
  `registro_id` int(11) DEFAULT NULL,
  `dados_antigos` text,
  `dados_novos` text,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `data_hora` timestamp DEFAULT CURRENT_TIMESTAMP,
  `tipo` enum('INSERT','UPDATE','DELETE','LOGIN','LOGOUT','RESET_SENHA') DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `acao` (`acao`),
  KEY `data_hora` (`data_hora`),
  KEY `tipo` (`tipo`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- =====================================================
-- 14. TABELA: configuracoes_sistema
-- Configurações gerais do sistema
-- =====================================================

DROP TABLE IF EXISTS `configuracoes_sistema`;
CREATE TABLE `configuracoes_sistema` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `chave` varchar(100) NOT NULL,
  `valor` text,
  `tipo` enum('string','integer','boolean','json') DEFAULT 'string',
  `descricao` varchar(255) DEFAULT NULL,
  `data_atualizacao` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `chave` (`chave`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- Configurações iniciais do sistema
INSERT INTO `configuracoes_sistema` (`chave`, `valor`, `tipo`, `descricao`) VALUES
('site_titulo', 'CONFINTER - Consolidando sonhos', 'string', 'Título do site'),
('site_descricao', 'Sistema completo para correspondente bancário', 'string', 'Descrição do site'),
('admin_email', 'admin@confinter.com', 'string', 'Email do administrador'),
('reset_senha_validade', '24', 'integer', 'Validade do token de reset em horas'),
('monitoramento_ativo', '1', 'boolean', 'Sistema de monitoramento ativo'),
('backup_automatico', '1', 'boolean', 'Backup automático ativo'),
('manutencao', '0', 'boolean', 'Modo de manutenção');

-- =====================================================
-- 15. TABELA: notificacoes
-- Sistema de notificações do painel admin
-- =====================================================

DROP TABLE IF EXISTS `notificacoes`;
CREATE TABLE `notificacoes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(100) NOT NULL,
  `mensagem` text NOT NULL,
  `tipo` enum('info','success','warning','danger') DEFAULT 'info',
  `usuario_id` int(11) DEFAULT NULL,
  `lida` tinyint(1) DEFAULT '0',
  `data_criacao` timestamp DEFAULT CURRENT_TIMESTAMP,
  `data_leitura` timestamp NULL DEFAULT NULL,
  `acao_url` varchar(255) DEFAULT NULL,
  `expira_em` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `lida` (`lida`),
  KEY `tipo` (`tipo`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- =====================================================
-- ÍNDICES ADICIONAIS PARA PERFORMANCE
-- =====================================================

-- Índices para otimização de consultas frequentes
CREATE INDEX idx_contador_visitas_data_ip ON contador_visitas(data_visita, ip_address);
CREATE INDEX idx_depoimentos_status_data ON depoimentos(status_mod, data_envio);
CREATE INDEX idx_requisicoes_status_data ON requisicoes(status, data_requisicao);
CREATE INDEX idx_reset_senha_status_data ON reset_senha(status, data_solicitacao);
CREATE INDEX idx_logs_sistema_data_tipo ON logs_sistema(data_hora, tipo);

-- =====================================================
-- VIEWS ÚTEIS PARA RELATÓRIOS
-- =====================================================

-- View para estatísticas de visitas
CREATE OR REPLACE VIEW vw_estatisticas_visitas AS
SELECT
    DATE(data_visita) as data,
    COUNT(*) as total_visitas,
    COUNT(DISTINCT ip_address) as visitantes_unicos,
    COUNT(DISTINCT sessao_id) as sessoes,
    AVG(TIME_TO_SEC(tempo)) as tempo_medio_segundos
FROM contador_visitas
WHERE data_visita >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
GROUP BY DATE(data_visita)
ORDER BY data DESC;

-- View para dashboard administrativo
CREATE OR REPLACE VIEW vw_dashboard_admin AS
SELECT
    (SELECT COUNT(*) FROM usuarios WHERE status = 'ativo') as total_usuarios,
    (SELECT COUNT(*) FROM clientes WHERE status = 'ativo') as total_clientes,
    (SELECT COUNT(*) FROM requisicoes WHERE status = 'pendente') as requisicoes_pendentes,
    (SELECT COUNT(*) FROM depoimentos WHERE status_mod = 'pendente') as depoimentos_pendentes,
    (SELECT COUNT(*) FROM reset_senha WHERE status = 'pendente') as resets_pendentes,
    (SELECT COUNT(*) FROM contador_visitas WHERE DATE(data_visita) = CURDATE()) as visitas_hoje;

-- =====================================================
-- PROCEDURES E FUNCTIONS ÚTEIS
-- =====================================================

DELIMITER //

-- Procedure para limpeza de dados antigos
CREATE PROCEDURE limpar_dados_antigos()
BEGIN
    -- Remove visitas antigas (mais de 1 ano)
    DELETE FROM contador_visitas
    WHERE data_visita < DATE_SUB(CURDATE(), INTERVAL 1 YEAR);

    -- Remove tokens de reset expirados
    DELETE FROM reset_senha
    WHERE expira_em < NOW() AND status = 'pendente';

    -- Remove notificações antigas (mais de 30 dias)
    DELETE FROM notificacoes
    WHERE data_criacao < DATE_SUB(CURDATE(), INTERVAL 30 DAY);

    -- Remove logs antigos (mais de 90 dias)
    DELETE FROM logs_sistema
    WHERE data_hora < DATE_SUB(CURDATE(), INTERVAL 90 DAY);
END //

-- Function para calcular idade
CREATE FUNCTION calcular_idade(data_nascimento DATE) RETURNS INT
DETERMINISTIC
BEGIN
    RETURN TIMESTAMPDIFF(YEAR, data_nascimento, CURDATE());
END //

DELIMITER ;

-- =====================================================
-- TRIGGERS PARA AUDITORIA AUTOMÁTICA
-- =====================================================

DELIMITER //

-- Trigger para auditar alterações em usuários
CREATE TRIGGER trg_auditoria_usuarios AFTER UPDATE ON usuarios
FOR EACH ROW
BEGIN
    INSERT INTO logs_sistema (usuario_id, acao, tabela_afetada, registro_id, dados_antigos, dados_novos, tipo)
    VALUES (
        NEW.id,
        'UPDATE_USUARIO',
        'usuarios',
        NEW.id,
        CONCAT('nome:', OLD.nome, '|email:', OLD.email, '|status:', OLD.status),
        CONCAT('nome:', NEW.nome, '|email:', NEW.email, '|status:', NEW.status),
        'UPDATE'
    );
END //

-- Trigger para auditar login de usuários
CREATE TRIGGER trg_auditoria_login AFTER UPDATE ON usuarios
FOR EACH ROW
BEGIN
    IF OLD.ultimo_acesso != NEW.ultimo_acesso THEN
        INSERT INTO logs_sistema (usuario_id, acao, tabela_afetada, registro_id, dados_novos, tipo)
        VALUES (
            NEW.id,
            'LOGIN_USUARIO',
            'usuarios',
            NEW.id,
            CONCAT('ultimo_acesso:', NEW.ultimo_acesso),
            'LOGIN'
        );
    END IF;
END //

DELIMITER ;

-- =====================================================
-- PERMISSÕES E CONFIGURAÇÕES FINAIS
-- =====================================================

-- Otimizar tabelas após criação
ANALYZE TABLE usuarios, clientes, contador_visitas, depoimentos, empresa, enderecos, imagens_carrossel, requisicoes, reset_senha, previsoes_pico, logs_sistema, configuracoes_sistema, notificacoes;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- =====================================================
-- INSTRUÇÕES DE USO
-- =====================================================
/*
Este esquema SQL contém todas as tabelas necessárias para o funcionamento completo do sistema CONFINTER.

Para instalar:
1. Execute este arquivo em seu servidor MySQL/MariaDB
2. Verifique se todas as tabelas foram criadas
3. Configure as credenciais de acesso no arquivo php/conexao.php
4. Execute o script atualizar_bd.php para aplicar possíveis atualizações futuras

Funcionalidades incluídas:
- ✅ Sistema de usuários e autenticação
- ✅ Gestão de clientes e requisições
- ✅ Contador de visitas e analytics
- ✅ Sistema de depoimentos e moderação
- ✅ Carrossel de imagens dinâmico
- ✅ Reset de senha seguro
- ✅ Previsões de horários de pico
- ✅ Sistema de auditoria e logs
- ✅ Notificações administrativas
- ✅ Configurações do sistema

Dados de exemplo incluídos para testes.
*/
