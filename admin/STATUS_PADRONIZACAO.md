# Status de Padronização - Páginas Admin CONFINTER
**Data:** 04/01/2025

## ✅ Páginas JÁ PADRONIZADAS (AdminLTE)

| Página | Layout | Status |
|--------|--------|--------|
| admin.php | ✅ AdminLTE | OK |
| requisicoes.php | ✅ AdminLTE | OK |
| listaclientes.php | ✅ AdminLTE | OK |
| monitoramento.php | ✅ AdminLTE | OK |
| relatorios.php | ✅ AdminLTE | OK |

## ⚠️ Páginas PRECISAM SER PADRONIZADAS (Template Antigo)

| Página | Layout Atual | Prioridade |
|--------|--------------|------------|
| mod_depoimentos.php | page-wrapper | ALTA |
| listarusuario.php | page-wrapper | ALTA |
| novousuario.php | page-wrapper | ALTA |
| editusuario.php | page-wrapper | ALTA |
| perfil.php | page-wrapper | MÉDIA |
| upload_imagens.php | DOCTYPE apenas | ALTA |
| clientedit.php | page-wrapper | MÉDIA |
| addusuario.php | page-wrapper | BAIXA |
| contador.php | ? | ALTA |
| buscar_empresa.php | ? | BAIXA |
| reset_senha.php | ? | MÉDIA |

## 📋 Checklist de Padronização

Para cada página, verificar:

1. **Session Management**
   ```php
   session_start();
   if (!isset($_SESSION['username'])) {
       header('Location: login.php');
       exit();
   }
   ```

2. **Includes Corretos**
   ```php
   include_once('../php/conexao.php');
   ```

3. **HTML Structure**
   ```html
   <!DOCTYPE html>
   <html lang="pt-BR">
   <head>
       <title>Nome da Página - CONFINTER</title>
       <!-- AdminLTE CDN -->
       <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
       <link rel="stylesheet" href="assets/css/custom-admin.css">
   </head>
   <body class="hold-transition sidebar-mini layout-fixed">
   ```

4. **Content Wrapper**
   ```html
   <div class="wrapper">
       <?php include 'navbar.php'; ?>
       <?php include 'sidebar.php'; ?>
       <div class="content-wrapper">
           <!-- Conteúdo -->
       </div>
       <?php include 'footer.php'; ?>
   </div>
   ```

5. **Scripts**
   ```html
   <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
   <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
   ```

## 🎯 Ações Imediatas

### 1. mod_depoimentos.php
- [ ] Substituir `page-wrapper` por `content-wrapper`
- [ ] Adicionar AdminLTE CDN
- [ ] Adicionar custom-admin.css
- [ ] Corrigir breadcrumbs
- [ ] Adicionar DataTables se necessário

### 2. listarusuario.php
- [ ] Mesmo processo acima
- [ ] Verificar query SQL (usar $conexao)
- [ ] Adicionar botões de ação padronizados

### 3. novousuario.php / editusuario.php
- [ ] Padronizar formulários com AdminLTE
- [ ] Adicionar validação JavaScript
- [ ] Estilizar botões

### 4. upload_imagens.php
- [ ] Criar estrutura completa
- [ ] Adicionar drag-and-drop
- [ ] Preview de imagens

### 5. contador.php
- [ ] Verificar se existe
- [ ] Criar página de estatísticas de visitas
- [ ] Gráficos com Chart.js

## 📝 Ordem de Execução

1. **Fase 1 - Críticas** (fazer agora)
   - mod_depoimentos.php
   - listarusuario.php
   - novousuario.php
   - upload_imagens.php
   - contador.php

2. **Fase 2 - Importantes** (depois)
   - editusuario.php
   - perfil.php
   - reset_senha.php

3. **Fase 3 - Complementares** (final)
   - clientedit.php
   - addusuario.php
   - buscar_empresa.php

## ✅ Próximos Passos

Após padronização de cada página:
1. Testar funcionalidade
2. Verificar responsividade
3. Validar queries SQL
4. Confirmar session management
5. Testar navegação breadcrumbs
6. Verificar botões e links
7. Atualizar RELATORIO_CORRECOES.md
