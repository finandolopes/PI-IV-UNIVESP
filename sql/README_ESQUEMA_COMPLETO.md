# 📋 ESQUEMA COMPLETO - CONFINTER
## Sistema de Análise e Gestão para Correspondente Bancário

**Data:** 06 de Setembro de 2025  
**Versão:** 2.0  
**Arquivo:** `sql/esquema_completo_confinter.sql`

---

## 🎯 Visão Geral

Este é o **esquema SQL completo e consolidado** do sistema CONFINTER, contendo todas as tabelas, índices, views, procedures, functions e triggers necessários para o funcionamento pleno da aplicação.

---

## 📊 Tabelas Incluídas

### 🏗️ **Estrutura Core (15 tabelas)**

| Tabela | Descrição | Registros Exemplo |
|--------|-----------|-------------------|
| `usuarios` | Sistema de autenticação e perfis | 2 usuários |
| `adm` | Tabela legacy para compatibilidade | 1 admin |
| `clientes` | Cadastro de clientes | 3 clientes |
| `contador_visitas` | Analytics de visitas | 3 registros |
| `depoimentos` | Sistema de avaliações | 3 depoimentos |
| `empresa` | Informações da empresa | 1 registro |
| `enderecos` | Endereços da empresa | 1 endereço |
| `imagens_carrossel` | Carrossel dinâmico | 5 imagens |
| `requisicoes` | Solicitações de crédito | 3 requisições |
| `tempo_visita` | Controle de tempo no site | - |
| `reset_senha` | Recuperação de senha | - |
| `previsoes_pico` | ML - Previsões | - |
| `logs_sistema` | Auditoria completa | - |
| `configuracoes_sistema` | Configs globais | 7 configurações |
| `notificacoes` | Sistema de alertas | - |

---

## 🚀 Instalação

### Pré-requisitos
- MySQL 5.7+ ou MariaDB 10.0+
- PHP 7.4+ com extensão mysqli
- Servidor web (Apache/Nginx)

### Passos de Instalação

```bash
# 1. Criar banco de dados
mysql -u root -p
CREATE DATABASE confinter CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
EXIT;

# 2. Executar o esquema completo
mysql -u root -p confinter < sql/esquema_completo_confinter.sql

# 3. Verificar instalação
mysql -u root -p confinter
SHOW TABLES;
SELECT COUNT(*) FROM usuarios;
```

### Configuração PHP
```php
// php/conexao.php
<?php
$host = "localhost";
$user = "root";
$pass = "sua_senha";
$dbname = "confinter";

$con = mysqli_connect($host, $user, $pass, $dbname);
$conexao = mysqli_connect($host, $user, $pass, $dbname); // Para compatibilidade
?>
```

---

## 🔧 Funcionalidades Implementadas

### ✅ **Sistema Completo**
- **Autenticação**: Login/logout com perfis (admin/usuário)
- **Gestão de Usuários**: CRUD completo com auditoria
- **Clientes**: Cadastro e gerenciamento de clientes
- **Requisições**: Sistema de solicitações de crédito
- **Analytics**: Contador de visitas com dados detalhados
- **Depoimentos**: Sistema de avaliações com moderação
- **Carrossel**: Gestão dinâmica de imagens
- **Reset de Senha**: Recuperação segura via admin
- **Previsões ML**: Análise de horários de pico
- **Auditoria**: Logs completos de todas as ações
- **Notificações**: Sistema de alertas administrativos

### ✅ **Recursos Avançados**
- **Views**: `vw_estatisticas_visitas`, `vw_dashboard_admin`
- **Procedures**: Limpeza automática de dados antigos
- **Functions**: Cálculo de idade
- **Triggers**: Auditoria automática de alterações
- **Índices**: Otimização de performance
- **Configurações**: Sistema parametrizável

---

## 📈 Dados de Exemplo Incluídos

### Usuários de Teste
```sql
-- Admin
Usuário: admin
Senha: admin
Email: admin@confinter.com

-- Usuário comum
Usuário: fnando
Senha: 0m3g4r3d
Email: fnando0506@gmail.com
```

### Dados de Demonstração
- 3 clientes cadastrados
- 3 requisições de crédito
- 5 imagens no carrossel
- 3 depoimentos (1 aprovado, 2 pendentes)
- Dados de visitas para analytics

---

## 🔗 Relacionamentos das Tabelas

```
usuarios (1) ──── (N) reset_senha
    │
    ├── (1) ──── (N) logs_sistema
    └── (1) ──── (N) notificacoes

clientes (1) ──── (N) requisicoes
    │
    └── (1) ──── (N) depoimentos

empresa (1) ──── (N) enderecos

contador_visitas ──── tempo_visita
```

---

## ⚡ Otimizações de Performance

### Índices Estratégicos
```sql
-- Consultas frequentes otimizadas
CREATE INDEX idx_contador_visitas_data_ip ON contador_visitas(data_visita, ip_address);
CREATE INDEX idx_depoimentos_status_data ON depoimentos(status_mod, data_envio);
CREATE INDEX idx_requisicoes_status_data ON requisicoes(status, data_requisicao);
```

### Views para Relatórios
```sql
-- Estatísticas rápidas
vw_estatisticas_visitas - Análise de visitas
vw_dashboard_admin - Dados do painel administrativo
```

---

## 🔒 Segurança Implementada

### Camadas de Proteção
- **Senhas**: Hash MD5 (recomendado atualizar para bcrypt)
- **Tokens**: Geração segura para reset de senha
- **Auditoria**: Logs de todas as ações do sistema
- **Validação**: Controle de acesso por perfil
- **SQL Injection**: Prepared statements recomendados

### Dados Sensíveis
- Emails de usuários criptografados quando necessário
- IPs de visitantes anonimizados
- Tokens de sessão com expiração

---

## 📊 Monitoramento e Manutenção

### Procedures Automáticas
```sql
-- Limpeza programada
CALL limpar_dados_antigos();
-- Remove dados antigos automaticamente
```

### Triggers de Auditoria
```sql
-- Rastreamento automático
- Alterações em usuários
- Tentativas de login
- Modificações críticas
```

---

## 🎛️ Configurações do Sistema

### Parâmetros Globais
```sql
-- Configurações editáveis
site_titulo: "CONFINTER - Consolidando sonhos"
reset_senha_validade: 24 horas
monitoramento_ativo: true
backup_automatico: true
```

---

## 🚀 Próximos Passos

### Após Instalação
1. ✅ **Testar conexão**: `php/conexao.php`
2. ✅ **Verificar dados**: Acessar tabelas via phpMyAdmin
3. ✅ **Testar login**: `admin/login.php`
4. ✅ **Configurar**: Ajustar configurações do sistema
5. ✅ **Backup**: Criar rotina de backup

### Desenvolvimento
- **API REST**: Para integração com outros sistemas
- **Dashboard**: Gráficos interativos com Chart.js
- **Email**: Sistema de notificações por email
- **Mobile**: Responsividade completa

---

## 📞 Suporte

**Sistema:** CONFINTER v2.0  
**Data:** 06/09/2025  
**Contato:** admin@confinter.com  

---

## 📋 Checklist de Verificação

- [ ] Banco de dados criado
- [ ] Esquema executado com sucesso
- [ ] Todas as 15 tabelas criadas
- [ ] Dados de exemplo inseridos
- [ ] Índices criados
- [ ] Views funcionais
- [ ] Procedures testadas
- [ ] Triggers ativos
- [ ] Configurações aplicadas
- [ ] Conexão PHP funcionando
- [ ] Login administrativo OK

---

**✅ ESQUEMA COMPLETO E PRONTO PARA USO!**
