# 🚀 DEPLOY NO INFINITYFREE - CONFINTER

## 📋 Pré-requisitos

- Conta ativa no [InfinityFree](https://infinityfree.net)
- Acesso ao painel de controle (cPanel)
- Credenciais do banco de dados fornecidas

## 🗄️ Configuração do Banco de Dados

### 1. Acesse o phpMyAdmin
1. Faça login no seu painel InfinityFree
2. Clique em **"Databases"** → **"phpMyAdmin"**

### 2. Execute o Schema
1. No phpMyAdmin, selecione o banco `if0_40254608_piiv`
2. Clique na aba **"Import"**
3. Selecione o arquivo `schema.sql` do projeto
4. Clique em **"Go"** para executar

### 3. Verifique as Tabelas
Após a execução, verifique se as seguintes tabelas foram criadas:
- ✅ usuarios
- ✅ adm
- ✅ clientes
- ✅ contador_visitas
- ✅ depoimentos
- ✅ empresa
- ✅ enderecos
- ✅ imagens_carrossel
- ✅ slider_imagens
- ✅ requisicoes
- ✅ tempo_visita
- ✅ reset_senha
- ✅ reset_senha_solicitacoes
- ✅ previsoes_pico
- ✅ logs_sistema
- ✅ logs_auditoria
- ✅ logs
- ✅ configuracoes
- ✅ configuracoes_sistema
- ✅ notificacoes
- ✅ newsletter

## 📁 Upload dos Arquivos

### 1. Via FTP
1. Use um cliente FTP (FileZilla, WinSCP, etc.)
2. Conecte-se ao servidor FTP do InfinityFree
3. Faça upload de todos os arquivos do projeto para a pasta `htdocs` ou `public_html`

### 2. Via File Manager
1. No cPanel do InfinityFree, acesse **"File Manager"**
2. Navegue até a pasta raiz do site
3. Faça upload dos arquivos via interface web

## ⚙️ Configurações do Sistema

### 1. Arquivo de Conexão
O arquivo `php/conexao.php` já está configurado com as credenciais do InfinityFree:

```php
$host = 'sql113.infinityfree.com';
$dbname = 'if0_40254608_piiv';
$username = 'if0_40254608';
$password = 'z6qbj0BsTqOe1ak';
```

### 2. Permissões de Arquivos
Certifique-se de que as pastas têm permissões adequadas:
- `assets/` - 755
- `admin/uploads/` - 755 (se existir)
- Arquivos PHP - 644

## 🧪 Testes Pós-Deploy

### 1. Teste de Conexão
Acesse: `http://seusite.infinityfree.com/teste_conexao_infinityfree.php`

### 2. Teste do Sistema
- **Página Inicial:** `http://seusite.infinityfree.com/index.php`
- **Painel Admin:** `http://seusite.infinityfree.com/admin/index.php`
  - Usuário: `admin`
  - Senha: `admin`

### 3. Funcionalidades a Testar
- ✅ Login no painel admin
- ✅ Visualização de estatísticas
- ✅ Gestão de usuários
- ✅ Sistema de depoimentos
- ✅ Relatórios e gráficos
- ✅ Configurações do sistema

## 🔧 Troubleshooting

### Erro de Conexão
- Verifique se as credenciais estão corretas
- Confirme se o banco foi criado no InfinityFree
- Execute o `teste_conexao_infinityfree.php`

### Erro 500 - Internal Server Error
- Verifique permissões dos arquivos
- Confirme se o PHP 7.4+ está ativo
- Verifique logs de erro no cPanel

### Problemas com Banco de Dados
- Execute novamente o `schema.sql`
- Verifique se todas as tabelas foram criadas
- Confirme os dados iniciais (usuário admin)

## 📞 Suporte

Para dúvidas ou problemas:
1. Verifique os logs de erro no cPanel
2. Teste localmente primeiro
3. Consulte a documentação em `MANUAL_IMPLEMENTACAO.md`

## ✅ Checklist Final

- [ ] Schema executado no phpMyAdmin
- [ ] Arquivos enviados para o servidor
- [ ] Conexão testada com sucesso
- [ ] Login admin funcionando
- [ ] Todas as funcionalidades testadas
- [ ] Backup dos dados locais realizado

---
**CONFINTER v3.0** - Sistema de Gestão para Correspondente Bancário
**Data do Deploy:** Outubro 2025