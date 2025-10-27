# CONFINTER - Sistema de Análise e Visualização de Dados

## 🆕 **Atualizações Recentes (Setembro 2025)**
- ✅ **Validação de Sessão**: Implementada em todas as páginas admin para segurança
- ✅ **Logs de Auditoria**: Sistema completo de rastreamento de ações (login/logout/backup)
- ✅ **Backup Automático**: Página admin para backup do banco de dados
- ✅ **Segurança Aprimorada**: Hash de senhas, prepared statements, proteção contra SQL injection
- ✅ **Layout Responsivo**: Melhorias na interface para dispositivos móveis
- ✅ **Acessibilidade**: Adição de atributos ARIA e navegação por teclado
- ✅ **Paginação**: Tabelas com paginação automática via DataTables
- ✅ **Relatórios Avançados**: Dashboard de relatórios com gráficos de visitas, requisições e auditoria
- ✅ **Análise de Acesso**: Tempo de acesso por página, usuários ativos, categorias de requisição

## Visão Geral
Este projeto implementa um sistema completo de análise de dados para o site da CONFINTER, incluindo dashboard interativo, análise exploratória e modelos de machine learning para previsão de horários de pico.

## 🏗️ **Arquitetura do Sistema**

### 📁 **Estrutura Completa do Projeto**
```
├── 🗄️ sql/
│   ├── confinter.sql                    # Banco de dados original
│   ├── esquema_completo_confinter.sql  # ✅ Esquema completo consolidado
│   ├── reset_senha.sql                 # Script para reset de senha
│   ├── README_ESQUEMA_COMPLETO.md      # ✅ Documentação completa do BD
│   └── atualizacoes_analise.sql        # Atualizações para análise
├── 🔧 php/
│   ├── conexao.php                     # Conexão com BD
│   ├── process.php                     # Processamento de formulários
│   ├── processa_reset_senha.php        # ✅ Sistema de reset de senha
│   ├── etl_limpeza.php                 # Script ETL de limpeza
│   └── atualizar_bd.php                # Script de atualização do BD
├── 🛡️ admin/
│   ├── reset_senha.php                 # ✅ Gestão de reset de senha
│   ├── monitoramento.php               # Monitoramento em tempo real
│   └── ...                            # Outros arquivos admin
├── 📊 dashboard/
│   └── app.py                          # Dashboard Dash/Plotly (opcional)
├── 🤖 ml_php_avancado.php               # Exemplo ML avançado (PHP-ML)
├── ⚡ tempo_real.html                   # Interface tempo real
├── 🔌 api/
│   └── get_dados_tempo_real.php        # API REST para dados
├── 📈 previsao_php.php                 # Previsão de picos em PHP
├── 🎛️ dashboard_php.php                # Dashboard interativo em PHP
├── 🔍 analise_exploratoria.py           # Análise exploratória (Python)
├── 🎯 previsao_pico.py                 # Modelo ML (Python)
├── 📋 requirements.txt                 # Dependências Python
├── ⚙️ config.php                        # ✅ Configurações centralizadas
├── 🚀 install.sh                        # ✅ Script de instalação automática
├── 🔒 .htaccess                         # ✅ Segurança e otimização web
├── 🤖 robots.txt                        # ✅ Controle de indexação SEO
├── 🗺️ sitemap.xml                       # ✅ Mapa do site para SEO
├── 🌐 nginx.conf                        # ✅ Configuração Nginx (exemplo)
├── 🐳 docker-compose.yml                # ✅ Implantação com Docker
├── 🐳 Dockerfile                        # ✅ Imagem personalizada
├── 🚫 .gitignore                        # ✅ Controle de versionamento
└── 📖 README.md                         # Este arquivo
```

## 🗄️ **Esquema de Banco de Dados Completo**

### 🎯 **Instalação Rápida (Recomendado)**
```bash
# Executar script de instalação automática
chmod +x install.sh
./install.sh
```

**O script fará automaticamente:**
- ✅ Criação do banco de dados
- ✅ Instalação do esquema completo
- ✅ Configuração das conexões PHP
- ✅ Criação do arquivo .htaccess
- ✅ Verificação da instalação

### 📋 **Instalação Manual**
```bash
# 1. Criar banco
mysql -u root -p -e "CREATE DATABASE confinter;"

# 2. Executar esquema
mysql -u root -p confinter < sql/esquema_completo_confinter.sql

# 3. Configurar conexões
cp config.php php/conexao.php
# Edite as credenciais no arquivo
```

### ⚙️ **Configuração do Sistema**
1. **Arquivo `config.php`**: Todas as configurações centralizadas
2. **Arquivo `php/conexao.php`**: Credenciais do banco de dados
3. **Arquivo `.htaccess`**: Regras de segurança e otimização

---

## 🚀 **Métodos de Implantação**

### 🐳 **Opção 1: Docker (Recomendado para Desenvolvimento)**
```bash
# Construir e iniciar containers
docker-compose up -d

# Acessar aplicação
# Web: http://localhost:8080
# phpMyAdmin: http://localhost:8081
# MySQL: localhost:3306
```

### 🌐 **Opção 2: Servidor Web Tradicional**
```bash
# Apache + PHP
sudo apt install apache2 php mysql-server

# Nginx + PHP-FPM
sudo apt install nginx php-fpm mysql-server
```

### ☁️ **Opção 3: Nuvem (AWS/Google Cloud/Azure)**
- Use os arquivos `docker-compose.yml` e `nginx.conf`
- Configure variáveis de ambiente
- Use serviços gerenciados de banco de dados

---

## 🔒 **Segurança e Otimização**

### 🛡️ **Configurações de Segurança**
- **`.htaccess`**: Proteção contra acesso não autorizado
- **`robots.txt`**: Controle de indexação por motores de busca
- **`nginx.conf`**: Configuração segura para Nginx
- **`config.php`**: Credenciais centralizadas e seguras

### ⚡ **Otimizações Implementadas**
- **Compressão GZIP**: Redução de tamanho de arquivos
- **Cache de navegador**: Melhoria de performance
- **Otimização de imagens**: Carregamento mais rápido
- **Minificação**: Redução de CSS/JavaScript

### 🔍 **SEO e Performance**
- **`sitemap.xml`**: Mapa do site para motores de busca
- **Meta tags otimizadas**: Melhoria de indexação
- **URLs amigáveis**: Estrutura de links otimizada
- **Performance monitoring**: Métricas em tempo real

---

## 📊 **Funcionalidades do Sistema**

### ✅ **Análise Exploratória (`analise_php.php`)**
- Visitas por dia/hora
- Requisições por categoria
- Taxa de conversão
- Top páginas visitadas
- Análise por dia da semana

### ✅ **Previsão de Picos (`previsao_php.php`)**
- Algoritmo de tendência linear
- Média móvel simples
- Fatores de ajuste (dia útil, horário comercial)
- Classificação de picos (Alto/Médio/Normal)
- Salvamento automático no banco

### ✅ **Dashboard Interativo (`dashboard_php.php`)**
- Gráficos com Chart.js
- Métricas em tempo real
- Heatmap de horários por dia
- Tabela de dados recentes
- Interface responsiva

### ✅ **Monitoramento em Tempo Real**
- Atualização automática a cada 30 segundos
- Alertas para picos de visita
- Métricas live (visitas hoje, última hora, conversão)
- API REST para integração

### ✅ **Sistema de Reset de Senha**
- Solicitação segura por usuários
- Aprovação por administradores
- Geração automática de novas senhas
- Gestão completa de solicitações

---

## 🛠️ **Configuração e Instalação**

### 1. **Pré-requisitos**
```bash
# PHP 8.1+
# MySQL 8.0+
# Apache/Nginx
# Composer (opcional)
```

### 2. **Instalação Automática**
```bash
# Tornar script executável
chmod +x install.sh

# Executar instalação
./install.sh
```

### 3. **Configuração Manual**
```bash
# 1. Configurar banco de dados
mysql -u root -p < sql/esquema_completo_confinter.sql

# 2. Configurar credenciais
cp config.php php/conexao.php
# Editar credenciais no arquivo

# 3. Configurar permissões
chmod 755 .
chmod 644 *.php
chmod 644 *.html
```

### 4. **Testar Instalação**
```bash
# Testar conexão com banco
php php/conexao.php

# Testar contador de visitas
curl http://localhost/index.php

# Verificar logs
tail -f /var/log/apache2/error.log
```

---

## 🎯 **Uso do Sistema**

### 📱 **Interface Web**
- **Página Principal**: `http://localhost/index.php`
- **Dashboard**: `http://localhost/dashboard_php.php`
- **Análise**: `http://localhost/analise_php.php`
- **Previsão**: `http://localhost/previsao_php.php`
- **Admin**: `http://localhost/admin/login.php`

### 🔌 **API REST**
```bash
# Dados em tempo real
GET /api/get_dados_tempo_real.php

# Resposta JSON
{
  "visitas_hoje": 150,
  "visitas_hora": 25,
  "taxa_conversao": 3.2,
  "ultima_atualizacao": "2025-09-06 14:30:00"
}
```

### 📊 **Dashboard Python (Opcional)**
```bash
# Instalar dependências
pip install -r requirements.txt

# Executar dashboard
python dashboard/app.py

# Acessar: http://localhost:8050
```

---

## 🔧 **Manutenção e Monitoramento**

### 📋 **Tarefas de Manutenção**
```bash
# Backup do banco
mysqldump -u user -p confinter > backup_$(date +%Y%m%d).sql

# Limpeza de logs antigos
php php/etl_limpeza.php

# Otimização de tabelas
mysql -u user -p confinter -e "OPTIMIZE TABLE contador_visitas;"

# Verificação de integridade
php -l *.php
```

### 📊 **Monitoramento**
- **Logs de erro**: `/var/log/apache2/error.log`
- **Logs de acesso**: `/var/log/apache2/access.log`
- **Métricas do sistema**: Acesse admin/monitoramento.php
- **Performance**: Use ferramentas como New Relic ou similar

---

## 🐛 **Solução de Problemas**

### ❌ **Erro de Conexão com Banco**
```bash
# Verificar se MySQL está rodando
sudo systemctl status mysql

# Testar conexão
mysql -u user -p -e "SELECT 1;"

# Verificar credenciais em config.php
cat config.php | grep -E "(DB_|MYSQL_)"
```

### ❌ **Erro 500 - Internal Server Error**
```bash
# Verificar logs
tail -f /var/log/apache2/error.log

# Verificar permissões
ls -la *.php

# Testar sintaxe PHP
php -l arquivo.php
```

### ❌ **Página Não Carrega**
```bash
# Verificar .htaccess
cat .htaccess

# Testar sem .htaccess
mv .htaccess .htaccess.bak
# Testar novamente
mv .htaccess.bak .htaccess
```

---

## � **Documentação Técnica**

### 🗄️ **Banco de Dados**
- **Arquivo**: `sql/README_ESQUEMA_COMPLETO.md`
- **Tabelas**: 15 tabelas principais
- **Relacionamentos**: Chaves estrangeiras definidas
- **Índices**: Otimizados para performance

### ⚙️ **Configurações**
- **Arquivo**: `config.php`
- **Parâmetros**: 50+ configurações centralizadas
- **Segurança**: Credenciais protegidas
- **Performance**: Cache e otimização

### 🔒 **Segurança**
- **Arquivo**: `.htaccess`
- **Proteções**: XSS, CSRF, SQL Injection
- **Acesso**: Controle de diretórios sensíveis
- **Headers**: Segurança HTTP

---

## 🤝 **Contribuição e Desenvolvimento**

### 🛠️ **Ambiente de Desenvolvimento**
```bash
# Clonar repositório
git clone https://github.com/seu-usuario/confinter.git

# Configurar ambiente
cp config.php config.local.php
# Editar configurações locais

# Instalar dependências
composer install  # se usar PHP dependencies
npm install       # se usar Node.js
```

### 📝 **Padrões de Código**
- **PHP**: PSR-12
- **JavaScript**: ESLint
- **SQL**: Uppercase keywords
- **HTML**: Semantic HTML5

### 🧪 **Testes**
```bash
# Executar testes PHP
vendor/bin/phpunit

# Testes de carga
ab -n 1000 -c 10 http://localhost/

# Validação de HTML
curl -s http://localhost/ | tidy -q -e
```

---

## 📞 **Suporte e Contato**

### 📧 **Canais de Suporte**
- **Issues**: GitHub Issues
- **Wiki**: Documentação completa
- **Email**: suporte@confinter.com

### 📖 **Recursos Adicionais**
- [Documentação da API](api/README.md)
- [Guia de Instalação](INSTALL.md)
- [FAQ](FAQ.md)
- [Changelog](CHANGELOG.md)

---

## 📄 **Licença**
Este projeto está sob a licença MIT. Veja o arquivo `LICENSE` para mais detalhes.

---

**Status**: ✅ Sistema 100% funcional e documentado
**Versão**: 2.0.0
**Data**: 06/09/2025
**Mantenedor**: Equipe CONFINTER


