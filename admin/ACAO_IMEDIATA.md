# 🎯 AÇÃO IMEDIATA - Padronização Admin CONFINTER
**Criado em:** 04/01/2025  
**Urgência:** ALTA

---

## ✅ O QUE JÁ ESTÁ FUNCIONANDO

### Páginas 100% Padronizadas:
1. ✅ **admin.php** - Dashboard principal com cards e gráficos
2. ✅ **requisicoes.php** - Lista de requisições com filtros e export XML
3. ✅ **listaclientes.php** - Lista de clientes com CRUD
4. ✅ **monitoramento.php** - Já tem AdminLTE correto
5. ✅ **relatorios.php** - Já tem AdminLTE correto

### Componentes Funcionais:
- ✅ navbar.php - Navegação superior
- ✅ sidebar.php - Menu lateral
- ✅ footer.php - Rodapé
- ✅ custom-admin.css - Estilos personalizados
- ✅ Login/Logout - Autenticação MD5

---

## ⚠️ PÁGINAS QUE PRECISAM SER AJUSTADAS

### 🔴 PRIORIDADE MÁXIMA (Fazer AGORA)

#### 1. mod_depoimentos.php
**Problema:** Usa `page-wrapper` ao invés de `content-wrapper`  
**Ação:**
```php
// TROCAR ESTA LINHA:
<div class="page-wrapper">

// POR ESTA:
<div class="content-wrapper">
```
**E adicionar no <head>:**
```html
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
<link rel="stylesheet" href="assets/css/custom-admin.css">
```

#### 2. listarusuario.php
**Mesmo problema acima**  
**Ações adicionais:**
- Verificar se usa `$conexao` (não `$conn`)
- Adicionar breadcrumbs
- Usar AdminLTE table classes

#### 3. novousuario.php
**Problema:** Template antigo  
**Ação:** Formulário precisa usar classes AdminLTE:
```html
<div class="form-group">
    <label>Nome</label>
    <input type="text" name="nome" class="form-control" required>
</div>
```

#### 4. upload_imagens.php
**Problema:** Estrutura incompleta  
**Ação:** Criar página completa com:
- Formulário de upload
- Preview de imagens
- Lista de imagens enviadas

#### 5. contador.php
**Problema:** Precisa verificar estrutura  
**Ação:** Criar se não existir, ou padronizar

---

## 🔧 TEMPLATE DE CORREÇÃO RÁPIDA

Para cada página com `page-wrapper`, use este script de correção:

### Passo 1: Substituir o Header
```html
<!-- ANTES -->
<link rel="stylesheet" href="assets/css/bootstrap.min.css">
<link rel="stylesheet" href="assets/css/style.css">

<!-- DEPOIS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
<link rel="stylesheet" href="assets/css/custom-admin.css">
```

### Passo 2: Substituir o Body Tag
```html
<!-- ANTES -->
<body>

<!-- DEPOIS -->
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
```

### Passo 3: Substituir o Wrapper
```html
<!-- ANTES -->
<div class="page-wrapper">
    <div class="page-content">
        <div class="container-fluid">

<!-- DEPOIS -->
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Título da Página</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="admin.php">Home</a></li>
                        <li class="breadcrumb-item active">Página Atual</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">
```

### Passo 4: Fechar Corretamente
```html
<!-- ANTES -->
        </div>
    </div>
</div>
</body>
</html>

<!-- DEPOIS -->
        </div>
    </section>
</div>

<?php include 'footer.php'; ?>

</div> <!-- /.wrapper -->

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>
</html>
```

---

## 📊 COMANDOS RÁPIDOS DE VALIDAÇÃO

### Verificar quais páginas usam template antigo:
```powershell
cd c:\wamp64\www\PI-IV\admin
Select-String -Path "*.php" -Pattern "page-wrapper" | Select-Object -Property Filename -Unique
```

### Verificar quais usam AdminLTE:
```powershell
Select-String -Path "*.php" -Pattern "hold-transition sidebar-mini" | Select-Object -Property Filename -Unique
```

### Verificar uso de $conn vs $conexao:
```powershell
Select-String -Path "*.php" -Pattern '\$conn->' | Select-Object -Property Filename, LineNumber
```

---

## 🎨 CLASSES CSS IMPORTANTES

### Botões:
```html
<button class="btn btn-primary">Salvar</button>
<button class="btn btn-success">Aprovar</button>
<button class="btn btn-danger">Excluir</button>
<button class="btn btn-warning">Editar</button>
<button class="btn btn-info">Info</button>
```

### Cards:
```html
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Título</h3>
    </div>
    <div class="card-body">
        Conteúdo
    </div>
</div>
```

### Tabelas:
```html
<table class="table table-bordered table-striped table-hover">
    <thead>
        <tr>
            <th>Coluna 1</th>
            <th>Coluna 2</th>
        </tr>
    </thead>
    <tbody>
        <!-- Dados -->
    </tbody>
</table>
```

### Formulários:
```html
<div class="form-group">
    <label>Campo</label>
    <input type="text" class="form-control" name="campo">
</div>
```

---

## 🚀 ORDEM DE EXECUÇÃO RECOMENDADA

### Hoje (Crítico):
1. ✅ Abrir `mod_depoimentos.php`
2. ✅ Substituir `page-wrapper` por `content-wrapper`
3. ✅ Adicionar AdminLTE CDN no `<head>`
4. ✅ Testar a página
5. ✅ Repetir para `listarusuario.php`, `novousuario.php`

### Amanhã:
1. ✅ Corrigir `upload_imagens.php`
2. ✅ Verificar `contador.php`
3. ✅ Padronizar `editusuario.php`
4. ✅ Padronizar `perfil.php`

### Depois:
1. ✅ Finalizar páginas secundárias
2. ✅ Testes completos
3. ✅ Documentação final

---

## ✅ CHECKLIST DE TESTE

Após ajustar cada página, verificar:

- [ ] Página carrega sem erros
- [ ] Navbar aparece corretamente
- [ ] Sidebar funciona
- [ ] Breadcrumbs navegam corretamente
- [ ] Formulários funcionam (se aplicável)
- [ ] Tabelas exibem dados (se aplicável)
- [ ] Botões executam ações
- [ ] Layout responsivo
- [ ] Sem erros no console do navegador
- [ ] Session management funciona (redireciona se não logado)

---

## 📞 REFERÊNCIAS RÁPIDAS

**Exemplo Completo:** Veja `admin.php` ou `requisicoes.php`  
**Documentação:** `GUIA_PADRONIZACAO.md`  
**Status:** `STATUS_PADRONIZACAO.md`  
**Relatório:** `RELATORIO_CORRECOES.md`  

---

## 🎯 RESULTADO ESPERADO

Ao final, TODAS as páginas do sidebar devem:
- ✅ Usar AdminLTE 3.2
- ✅ Ter navbar e sidebar consistentes
- ✅ Usar custom-admin.css
- ✅ Ter breadcrumbs funcionais
- ✅ Ser responsivas
- ✅ Ter session management
- ✅ Usar variável $conexao

---

**COMECE AGORA com `mod_depoimentos.php`!**
