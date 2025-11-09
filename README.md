# CONFINTER - Sistema de Análise e Visualização de Dados

## Demonstração

Acesse o site de demonstração hospedado gratuitamente para testes: [www.confinter.rg.gd](http://www.confinter.rg.gd)

## Atualizações Recentes (Setembro 2025)
- Validacao de Sessao: Implementada em todas as paginas admin para seguranca
- Logs de Auditoria: Sistema completo de rastreamento de acoes (login/logout/backup)
- Backup Automatico: Pagina admin para backup do banco de dados
- Seguranca Aprimorada: Hash de senhas, prepared statements, protecao contra SQL injection
- Layout Responsivo: Melhorias na interface para dispositivos moveis
- Acessibilidade: Adicao de atributos ARIA e navegacao por teclado
- Paginacao: Tabelas com paginacao automatica via DataTables
- Relatorios Avancados: Dashboard de relatorios com graficos de visitas, requisicoes e auditoria
- Analise de Acesso: Tempo de acesso por pagina, usuarios ativos, categorias de requisicao

## Visão Geral
Este projeto implementa um sistema completo de análise de dados para o site da CONFINTER, incluindo dashboard interativo, análise exploratória e modelos de machine learning para previsão de horários de pico.

## Arquitetura do Sistema

### Estrutura Completa do Projeto
```
├── sql/
│   ├── confinter.sql                    # Banco de dados original
│   ├── esquema_completo_confinter.sql  # Esquema completo consolidado
│   ├── reset_senha.sql                 # Script para reset de senha
│   ├── README_ESQUEMA_COMPLETO.md      # Documentacao completa do BD
│   └── atualizacoes_analise.sql        # Atualizacoes para analise
├── php/
│   ├── conexao.php                     # Conexao com BD
│   ├── process.php                     # Processamento de formularios
│   ├── processa_reset_senha.php        # Sistema de reset de senha
│   ├── etl_limpeza.php                 # Script ETL de limpeza
│   └── atualizar_bd.php                # Script de atualizacao do BD
├── admin/
│   ├── reset_senha.php                 # Gestao de reset de senha
│   ├── monitoramento.php               # Monitoramento em tempo real
│   └── ...                            # Outros arquivos admin
├── dashboard/
│   └── app.py                          # Dashboard Dash/Plotly (opcional)
├── ml_php_avancado.php               # Exemplo ML avancado (PHP-ML)
├── tempo_real.html                   # Interface tempo real
├── api/
│   └── get_dados_tempo_real.php        # API REST para dados
├── previsao_php.php                 # Previsao de picos em PHP
├── dashboard_php.php                # Dashboard interativo em PHP
├── analise_exploratoria.py           # Analise exploratoria (Python)
├── previsao_pico.py                 # Modelo ML (Python)
├── requirements.txt                 # Dependencias Python
├── config.php                        # Configuracoes centralizadas
├── install.sh                        # Script de instalacao automatica
├── .htaccess                         # Seguranca e otimizacao web
├── robots.txt                        # Controle de indexacao SEO
├── sitemap.xml                       # Mapa do site para SEO
├── nginx.conf                        # Configuracao Nginx (exemplo)
├── docker-compose.yml                # Implantacao com Docker
├── Dockerfile                        # Imagem personalizada
├── .gitignore                        # Controle de versionamento
└── README.md                         # Este arquivo
```

## Esquema de Banco de Dados Completo

### Instalacao Rapida (Recomendado)
```bash
# Executar script de instalacao automatica
chmod +x install.sh
./install.sh
```

O script fara automaticamente:
- Criacao do banco de dados
- Instalacao do esquema completo
- Configuracao das conexoes PHP
- Criacao do arquivo .htaccess
- Verificacao da instalacao

### Instalacao Manual
```bash
# 1. Criar banco
mysql -u root -p -e "CREATE DATABASE confinter;"

# 2. Executar esquema
mysql -u root -p confinter < sql/esquema_completo_confinter.sql

# 3. Configurar conexoes
cp config.php php/conexao.php
# Edite as credenciais no arquivo
```

### Configuracao do Sistema
1. Arquivo `config.php`: Todas as configuracoes centralizadas
2. Arquivo `php/conexao.php`: Credenciais do banco de dados
3. Arquivo `.htaccess`: Regras de seguranca e otimizacao

---

## Metodos de Implantacao

### Opcao 1: Docker (Recomendado para Desenvolvimento)
```bash
# Construir e iniciar containers
docker-compose up -d

# Acessar aplicacao
# Web: http://localhost:8080
# phpMyAdmin: http://localhost:8081
# MySQL: localhost:3306
```

### Opcao 2: Servidor Web Tradicional
```bash
# Apache + PHP
sudo apt install apache2 php mysql-server

# Nginx + PHP-FPM
sudo apt install nginx php-fpm mysql-server
```

### Opcao 3: Nuvem (AWS/Google Cloud/Azure)
- Use os arquivos `docker-compose.yml` e `nginx.conf`
- Configure variaveis de ambiente
- Use servicos gerenciados de banco de dados

---

## Seguranca e Otimizacao

### Configuracoes de Seguranca
- `.htaccess`: Protecao contra acesso nao autorizado
- `robots.txt`: Controle de indexacao por motores de busca
- `nginx.conf`: Configuracao segura para Nginx
- `config.php`: Credenciais centralizadas e seguras

### Otimizacoes Implementadas
- Compressao GZIP: Reducao de tamanho de arquivos
- Cache de navegador: Melhoria de performance
- Otimizacao de imagens: Carregamento mais rapido
- Minificacao: Reducao de CSS/JavaScript

### SEO e Performance
- `sitemap.xml`: Mapa do site para motores de busca
- Meta tags otimizadas: Melhoria de indexacao
- URLs amigaveis: Estrutura de links otimizada
- Performance monitoring: Metricas em tempo real

---

## Funcionalidades do Sistema

### Analise Exploratoria (analise_php.php)
- Visitas por dia/hora
- Requisicoes por categoria
- Taxa de conversao
- Top paginas visitadas
- Analise por dia da semana

### Previsao de Picos (previsao_php.php)
- Algoritmo de tendencia linear
- Media movel simples
- Fatores de ajuste (dia util, horario comercial)
- Classificacao de picos (Alto/Medio/Normal)
- Salvamento automatico no banco

### Dashboard Interativo (dashboard_php.php)
- Graficos com Chart.js
- Metricas em tempo real
- Heatmap de horarios por dia
- Tabela de dados recentes
- Interface responsiva

### Monitoramento em Tempo Real
- Atualizacao automatica a cada 30 segundos
- Alertas para picos de visita
- Metricas live (visitas hoje, ultima hora, conversao)
- API REST para integracao

### Sistema de Reset de Senha
- Solicitacao segura por usuarios
- Aprovacao por administradores
- Geracao automatica de novas senhas
- Gestao completa de solicitacoes

---

## Configuracao e Instalacao

### 1. Pre-requisitos
```bash
# PHP 8.1+
# MySQL 8.0+
# Apache/Nginx
# Composer (opcional)
```

### 2. Instalacao Automatica
```bash
# Tornar script executavel
chmod +x install.sh

# Executar instalacao
./install.sh
```

### 3. Configuracao Manual
```bash
# 1. Configurar banco de dados
mysql -u root -p < sql/esquema_completo_confinter.sql

# 2. Configurar credenciais
cp config.php php/conexao.php
# Editar credenciais no arquivo

# 3. Configurar permissoes
chmod 755 .
chmod 644 *.php
chmod 644 *.html
```

### 4. Testar Instalacao
```bash
# Testar conexao com banco
php php/conexao.php

# Testar contador de visitas
curl http://localhost/index.php

# Verificar logs
tail -f /var/log/apache2/error.log
```

---

## Uso do Sistema

### Interface Web
- Pagina Principal: http://localhost/index.php
- Dashboard: http://localhost/dashboard_php.php
- Analise: http://localhost/analise_php.php
- Previsao: http://localhost/previsao_php.php
- Admin: http://localhost/admin/login.php

### API REST
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

### Dashboard Python (Opcional)
```bash
# Instalar dependencias
pip install -r requirements.txt

# Executar dashboard
python dashboard/app.py

# Acessar: http://localhost:8050
```

---

## Manutencao e Monitoramento

### Tarefas de Manutencao
```bash
# Backup do banco
mysqldump -u user -p confinter > backup_$(date +%Y%m%d).sql

# Limpeza de logs antigos
php php/etl_limpeza.php

# Otimizacao de tabelas
mysql -u user -p confinter -e "OPTIMIZE TABLE contador_visitas;"

# Verificacao de integridade
php -l *.php
```

### Monitoramento
- Logs de erro: /var/log/apache2/error.log
- Logs de acesso: /var/log/apache2/access.log
- Metricas do sistema: Acesse admin/monitoramento.php
- Performance: Use ferramentas como New Relic ou similar

---

## Solucao de Problemas

### Erro de Conexao com Banco
```bash
# Verificar se MySQL esta rodando
sudo systemctl status mysql

# Testar conexao
mysql -u user -p -e "SELECT 1;"

# Verificar credenciais em config.php
cat config.php | grep -E "(DB_|MYSQL_)"
```

### Erro 500 - Internal Server Error
```bash
# Verificar logs
tail -f /var/log/apache2/error.log

# Verificar permissoes
ls -la *.php

# Testar sintaxe PHP
php -l arquivo.php
```

### Pagina Nao Carrega
```bash
# Verificar .htaccess
cat .htaccess

# Testar sem .htaccess
mv .htaccess .htaccess.bak
# Testar novamente
mv .htaccess.bak .htaccess
```

---

## Documentacao Tecnica

### Banco de Dados
- Arquivo: sql/README_ESQUEMA_COMPLETO.md
- Tabelas: 15 tabelas principais
- Relacionamentos: Chaves estrangeiras definidas
- Indices: Otimizados para performance

### Configuracoes
- Arquivo: config.php
- Parametros: 50+ configuracoes centralizadas
- Seguranca: Credenciais protegidas
- Performance: Cache e otimizacao

### Seguranca
- **Arquivo**: `.htaccess`
- **Proteções**: XSS, CSRF, SQL Injection
- **Acesso**: Controle de diretórios sensíveis
- **Headers**: Segurança HTTP

---

## Contribuição e Desenvolvimento

### Ambiente de Desenvolvimento
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

### Padrões de Código
- **PHP**: PSR-12
- **JavaScript**: ESLint
- **SQL**: Uppercase keywords
- **HTML**: Semantic HTML5

### Testes
```bash
# Executar testes PHP
vendor/bin/phpunit

# Testes de carga
ab -n 1000 -c 10 http://localhost/

# Validação de HTML
curl -s http://localhost/ | tidy -q -e
```

---

## Suporte e Contato

### Canais de Suporte
- Issues: GitHub Issues
- Wiki: Documentacao completa
- Email: suporte@confinter.com

### Recursos Adicionais
- [Documentacao da API](api/README.md)
- [Guia de Instalacao](INSTALL.md)
- [FAQ](FAQ.md)
- [Changelog](CHANGELOG.md)

---

## Licenca
Este projeto esta sob a licenca MIT. Veja o arquivo `LICENSE` para mais detalhes.

---

Status: Sistema 100% funcional e documentado
Versao: 2.0.0
Data: 06/09/2025
Mantenedor: Equipe CONFINTER


