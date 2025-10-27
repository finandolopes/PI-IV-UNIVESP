# Como Testar o Sistema de Iframe do Admin

## Problema Identificado
As funções do sidebar não estavam abrindo as telas porque:
1. A função `loadInIframe` estava sendo definida após o carregamento do sidebar
2. Os caminhos dos arquivos estavam incorretos (usando `/PI-IV/admin/` em vez de caminhos relativos)

## Correções Aplicadas
1. ✅ Movida a definição da função `loadInIframe` para antes do include do sidebar
2. ✅ Corrigidos todos os caminhos para serem relativos ao diretório admin
3. ✅ Adicionado arquivo de teste `teste_iframe.php` para verificar funcionamento
4. ✅ Adicionados logs de console para debug

## Como Testar

### 1. Acesse o Admin
- Vá para: `http://localhost/PI-IV-main/admin/admin.php`
- Faça login se necessário

### 2. Teste o Sistema de Iframe
- Clique em "Teste Iframe" no sidebar (seção INFORMAÇÕES RÁPIDAS)
- Deve abrir uma página de teste dentro do iframe
- Verifique o console do navegador (F12) para logs

### 3. Teste Outras Funcionalidades
- Clique em "Gerenciar Reset" - deve abrir o sistema de reset de senha
- Clique em "Listar Usuários" - deve abrir a lista de usuários
- Clique em "Dashboard" - deve voltar ao dashboard principal

### 4. Verificar Console
- Abra o console do navegador (F12 → Console)
- Deve ver mensagens como:
  ```
  loadInIframe function called with: teste_iframe.php Teste Iframe
  Elements found, proceeding...
  Loading URL: teste_iframe.php?iframe=true
  Iframe loaded successfully
  ```

## Possíveis Problemas e Soluções

### Se ainda não funcionar:
1. **Verifique se os arquivos existem**: Todos os arquivos PHP devem estar no diretório `admin/`
2. **Verifique o console**: Procure por erros de JavaScript
3. **Teste URLs diretamente**: Tente acessar `http://localhost/PI-IV-main/admin/teste_iframe.php?iframe=true`
4. **Verifique permissões**: Certifique-se que o Apache/WAMP tem permissão para acessar os arquivos

### Se o iframe não carrega:
1. Verifique se o elemento `content-iframe` existe na página
2. Verifique se não há erros de CORS
3. Teste com arquivos HTML simples primeiro

## Arquivos Modificados
- `admin/admin.php` - Função loadInIframe movida e logs adicionados
- `admin/sidebar.php` - Caminhos corrigidos para relativos
- `admin/teste_iframe.php` - Novo arquivo de teste criado