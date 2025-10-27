# Guia de Padronização - Páginas Administrativas CONFINTER

## ✅ Páginas Corrigidas e Padronizadas

### 1. admin.php (Dashboard Principal)
- ✅ Layout AdminLTE consistente
- ✅ CSS via custom-admin.css
- ✅ Cards de Depoimentos corrigidos
- ✅ Card de Configurações adicionado
- ✅ Gráficos Chart.js funcionais
- ✅ Small boxes com estatísticas

### 2. requisicoes.php
- ✅ Estrutura AdminLTE completa
- ✅ Session management implementado
- ✅ Filtro por data funcional
- ✅ Exportação XML corrigida
- ✅ DataTables em português
- ✅ Breadcrumbs navegáveis
- ✅ Botões de ação padronizados

### 3. listaclientes.php
- ✅ Estrutura AdminLTE completa
- ✅ Query SQL corrigida (removido JOIN desnecessário)
- ✅ Variável $conexao padronizada (antes $conn)
- ✅ Exportação XML funcional
- ✅ DataTables com ordenação
- ✅ Botões editar/excluir funcionais
- ✅ Confirmação de exclusão via JavaScript

---

## 📋 Template Padrão para Novas Páginas

### Estrutura HTML Básica:
```php
<?php
session_start();
include_once('../php/conexao.php');

// Verifica se o usuário está logado
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Lógica PHP da página aqui
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Título da Página - CONFINTER</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- DataTables (se necessário) -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <!-- Custom Admin CSS -->
    <link rel="stylesheet" href="assets/css/custom-admin.css">
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

<?php include 'navbar.php'; ?>
<?php include 'sidebar.php'; ?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Título da Página</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="admin.php">Home</a></li>
                        <li class="breadcrumb-item active">Título</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Conteúdo aqui -->
        </div>
    </section>
</div>

<?php include 'footer.php'; ?>

</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>
</html>
```

---

## 🔧 Páginas que Precisam de Padronização

### Alta Prioridade:
1. **monitoramento.php** - Monitoramento do sistema
2. **mod_depoimentos.php** - Moderação de depoimentos
3. **upload_imagens.php** - Upload de imagens

### Média Prioridade:
4. **listarusuario.php** - Lista de usuários
5. **novousuario.php** - Novo usuário
6. **editusuario.php** - Editar usuário
7. **contador.php** - Contador de visitas

### Baixa Prioridade (já podem estar corretas):
8. **perfil.php** - Perfil do usuário
9. **reset_senha.php** - Reset de senha

---

## ⚠️ Erros Comuns a Corrigir

### 1. Variável de Conexão
❌ **Errado:** `$conn->query()`  
✅ **Correto:** `$conexao->query()`

### 2. Query SQL com JOIN Desnecessário
❌ **Errado:**
```php
SELECT r.id_cliente, c.nome FROM clientes r 
INNER JOIN clientes c ON r.id_cliente = c.id_cliente
```
✅ **Correto:**
```php
SELECT id_cliente, nome FROM clientes
```

### 3. Tabela de Usuários
❌ **Errado:** `usuarios`  
✅ **Correto:** `adm`

### 4. CSS Externo
❌ **Errado:** Múltiplos CSS (bootstrap.min.css, style.css, etc)  
✅ **Correto:** Apenas AdminLTE CDN + custom-admin.css

### 5. Estrutura do Wrapper
❌ **Errado:** `<div class="page-wrapper">`  
✅ **Correto:** `<div class="content-wrapper">`

### 6. Session Management
❌ **Errado:** Sem verificação de login  
✅ **Correto:**
```php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}
```

---

## 📊 Componentes AdminLTE Disponíveis

### Small Boxes (Estatísticas)
```html
<div class="small-box bg-info">
    <div class="inner">
        <h3>150</h3>
        <p>Título</p>
    </div>
    <div class="icon">
        <i class="fas fa-icon"></i>
    </div>
    <a href="#" class="small-box-footer">
        Mais info <i class="fas fa-arrow-circle-right"></i>
    </a>
</div>
```

### Cards
```html
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Título</h3>
        <div class="card-tools">
            <button class="btn btn-sm btn-primary">Ação</button>
        </div>
    </div>
    <div class="card-body">
        Conteúdo
    </div>
</div>
```

### DataTables (Tabelas)
```html
<table id="exemplo-table" class="table table-bordered table-striped table-hover">
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

<script>
$('#exemplo-table').DataTables({
    "language": {
        "url": "//cdn.datatables.net/plug-ins/1.13.4/i18n/pt-BR.json"
    }
});
</script>
```

---

## 🎨 Classes CSS Importantes (custom-admin.css)

- **Navbar:** `height: 57px`, `left: 250px`, `z-index: 1032`
- **Sidebar:** `width: 250px`, `z-index: 1031`
- **Content-wrapper:** `margin-left: 250px`, `margin-top: 57px`
- **Small-box:** `min-height: 100px`, ícones `60px`
- **Cards:** `margin-bottom: 0.75rem`
- **Rows:** `margin: -0.375rem`
- **Columns:** `padding: 0.375rem`

---

## 📝 Checklist de Revisão

Antes de finalizar qualquer página, verificar:

- [ ] Session management implementado
- [ ] Variável $conexao usada consistentemente
- [ ] Includes navbar.php e sidebar.php presentes
- [ ] Breadcrumbs configurados corretamente
- [ ] Título da página no `<title>` e `<h1>`
- [ ] Custom-admin.css incluído
- [ ] Scripts jQuery, Bootstrap e AdminLTE carregados
- [ ] DataTables em português (se aplicável)
- [ ] Botões com ícones Font Awesome
- [ ] Tratamento de erros SQL
- [ ] Proteção contra SQL Injection (prepared statements)
- [ ] Fechamento da conexão $conexao->close()
- [ ] Responsivo para mobile

---

## 🔐 Segurança

### Proteção XSS
```php
echo htmlspecialchars($variavel);
```

### Proteção SQL Injection
```php
$stmt = $conexao->prepare("SELECT * FROM tabela WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
```

### Validação de Upload
```php
$allowed = ['jpg', 'jpeg', 'png', 'gif'];
$ext = pathinfo($_FILES['arquivo']['name'], PATHINFO_EXTENSION);
if (!in_array(strtolower($ext), $allowed)) {
    die('Tipo de arquivo não permitido');
}
```

---

## 📞 Suporte

Em caso de dúvidas:
1. Consultar `admin.php` como referência
2. Consultar `requisicoes.php` para páginas com tabelas
3. Consultar `listaclientes.php` para CRUD completo
4. Verificar `custom-admin.css` para estilos disponíveis

---

**Última Atualização:** 04/01/2025  
**Versão:** 1.0  
**Responsável:** Equipe de Desenvolvimento CONFINTER
