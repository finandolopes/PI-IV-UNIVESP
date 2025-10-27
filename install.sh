#!/bin/bash

# =====================================================
# SCRIPT DE INSTALAÇÃO RÁPIDA - CONFINTER
# Instalação completa do sistema em poucos passos
# =====================================================

echo "🚀 CONFINTER - Instalação Rápida"
echo "=================================="

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Verificar se MySQL está instalado
if ! command -v mysql &> /dev/null; then
    echo -e "${RED}❌ MySQL não encontrado. Instale o MySQL primeiro.${NC}"
    exit 1
fi

echo -e "${BLUE}📋 Verificando pré-requisitos...${NC}"

# Solicitar credenciais do banco
echo -e "${YELLOW}🔐 Configurações do Banco de Dados${NC}"
read -p "Host do MySQL [localhost]: " DB_HOST
DB_HOST=${DB_HOST:-localhost}

read -p "Usuário do MySQL [root]: " DB_USER
DB_USER=${DB_USER:-root}

read -s -p "Senha do MySQL: " DB_PASS
echo ""

read -p "Nome do banco [confinter]: " DB_NAME
DB_NAME=${DB_NAME:-confinter}

echo -e "${BLUE}🔍 Testando conexão com MySQL...${NC}"

# Testar conexão
mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" -e "SELECT 1;" &>/dev/null
if [ $? -ne 0 ]; then
    echo -e "${RED}❌ Falha na conexão com MySQL. Verifique as credenciais.${NC}"
    exit 1
fi

echo -e "${GREEN}✅ Conexão com MySQL estabelecida!${NC}"

# Criar banco de dados se não existir
echo -e "${BLUE}📦 Criando banco de dados...${NC}"
mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" -e "CREATE DATABASE IF NOT EXISTS \`$DB_NAME\` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Banco de dados criado/verificado!${NC}"
else
    echo -e "${RED}❌ Erro ao criar banco de dados.${NC}"
    exit 1
fi

# Executar esquema completo
echo -e "${BLUE}🏗️ Instalando esquema completo...${NC}"
mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" < "sql/esquema_completo_confinter.sql"

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Esquema instalado com sucesso!${NC}"
else
    echo -e "${RED}❌ Erro ao instalar esquema.${NC}"
    exit 1
fi

# Verificar tabelas criadas
echo -e "${BLUE}🔍 Verificando instalação...${NC}"
TABLES_COUNT=$(mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SHOW TABLES;" | wc -l)
TABLES_COUNT=$((TABLES_COUNT - 1)) # Subtrair header

if [ "$TABLES_COUNT" -ge 15 ]; then
    echo -e "${GREEN}✅ $TABLES_COUNT tabelas criadas com sucesso!${NC}"
else
    echo -e "${YELLOW}⚠️  Apenas $TABLES_COUNT tabelas encontradas. Verifique a instalação.${NC}"
fi

# Criar arquivo de configuração PHP
echo -e "${BLUE}⚙️ Criando arquivo de configuração...${NC}"

cat > "php/conexao.php" << EOF
<?php
// Configurações de conexão com o banco de dados
// Gerado automaticamente pelo script de instalação

\$host = "$DB_HOST";
\$user = "$DB_USER";
\$pass = "$DB_PASS";
\$dbname = "$DB_NAME";

// Conexão principal
\$con = mysqli_connect(\$host, \$user, \$pass, \$dbname);

// Conexão alternativa (para compatibilidade)
\$conexao = mysqli_connect(\$host, \$user, \$pass, \$dbname);

// Verificar conexão
if (mysqli_connect_errno()) {
    die("Falha na conexão com MySQL: " . mysqli_connect_error());
}

// Configurar charset
mysqli_set_charset(\$con, "utf8mb4");
if (\$conexao) {
    mysqli_set_charset(\$conexao, "utf8mb4");
}

echo "✅ Conexão estabelecida com sucesso!";
?>
EOF

echo -e "${GREEN}✅ Arquivo de configuração criado!${NC}"

# Verificar dados iniciais
echo -e "${BLUE}📊 Verificando dados iniciais...${NC}"

USERS_COUNT=$(mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SELECT COUNT(*) FROM usuarios;" | tail -n1)
CLIENTS_COUNT=$(mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SELECT COUNT(*) FROM clientes;" | tail -n1)

echo -e "${GREEN}✅ $USERS_COUNT usuários cadastrados${NC}"
echo -e "${GREEN}✅ $CLIENTS_COUNT clientes de exemplo${NC}"

# Criar arquivo .htaccess básico se não existir
if [ ! -f ".htaccess" ]; then
    echo -e "${BLUE}🔒 Criando arquivo .htaccess básico...${NC}"
    cat > ".htaccess" << 'EOF'
# CONFINTER - Configurações básicas
RewriteEngine On

# Redirecionar HTTP para HTTPS (descomente se usar SSL)
# RewriteCond %{HTTPS} off
# RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Proteger arquivos sensíveis
<Files "conexao.php">
    Order Deny,Allow
    Deny from all
</Files>

# Configurações de cache
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType image/png "access plus 1 month"
    ExpiresByType image/jpg "access plus 1 month"
    ExpiresByType image/jpeg "access plus 1 month"
    ExpiresByType image/gif "access plus 1 month"
</IfModule>
EOF
    echo -e "${GREEN}✅ Arquivo .htaccess criado!${NC}"
fi

echo ""
echo -e "${GREEN}🎉 INSTALAÇÃO CONCLUÍDA COM SUCESSO!${NC}"
echo "========================================"
echo ""
echo -e "${BLUE}📋 PRÓXIMOS PASSOS:${NC}"
echo "1. Configure seu servidor web (Apache/Nginx)"
echo "2. Acesse: http://localhost/seu_projeto/"
echo "3. Login admin: admin / admin"
echo "4. Teste todas as funcionalidades"
echo ""
echo -e "${BLUE}📞 SUPORTE:${NC}"
echo "Email: admin@confinter.com"
echo "Data: $(date)"
echo ""
echo -e "${GREEN}✅ SISTEMA CONFINTER PRONTO PARA USO!${NC}"
