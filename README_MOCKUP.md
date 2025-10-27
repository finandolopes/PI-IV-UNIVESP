# Mockup do Painel Administrativo - CONFINTER

Este é um mockup completo em HTML5 do painel administrativo do sistema CONFINTER, baseado na análise das funcionalidades existentes no projeto.

## 📋 Funcionalidades Implementadas

### 🏠 Dashboard
- **Métricas principais**: Receita aprovada, total de clientes, requisições, conversão do site
- **Gráficos**: Pipeline de vendas (placeholder), atividades recentes
- **Métricas adicionais**: Ticket médio, novos clientes, avaliação média
- **Seção de boas-vindas** com informações do usuário e status

### 👥 Gestão de Usuários
- **Listar usuários**: Tabela com dados completos, filtros e ações
- **Novo usuário**: Formulário completo de cadastro
- **Perfil**: Visualização e edição do perfil pessoal
- **Estatísticas**: Totais por perfil e status

### 🏢 Gestão de Clientes
- **Buscar clientes**: Filtros e resultados em tabela
- **Editar cliente**: Formulário de edição (placeholder)

### 📋 Requisições de Crédito
- **Lista de requisições**: Status, valores, ações de aprovação/reprovação
- **Estatísticas**: Pendentes, aprovadas, reprovadas, valor total
- **Modal de detalhes**: Informações completas da requisição

### 💬 Depoimentos
- **Moderação**: Timeline com depoimentos pendentes
- **Ações**: Aprovar/reprovar depoimentos
- **Estatísticas**: Avaliação média, totais

### 🖼️ Mídia
- **Upload de imagens**: Seleção múltipla com preview
- **Galeria**: Visualização em grid com ações

### 📊 Relatórios
- **Estatísticas**: Gráficos de acesso e conversões (placeholders)
- **Relatórios detalhados**: Estrutura preparada

### ⚙️ Sistema
- **Configurações**: Formulário de configurações gerais
- **Backup**: Criação e download de backups

## 🎨 Design e UX

### Layout
- **Sidebar responsivo**: Menu lateral colapsível
- **Navbar superior**: Busca, notificações, menu do usuário
- **AdminLTE 3**: Framework moderno e profissional
- **Bootstrap 4**: Componentes responsivos

### Tema Visual
- **Gradientes modernos**: Fundo com gradiente azul
- **Cards com glassmorphism**: Efeito de transparência
- **Ícones FontAwesome**: Interface intuitiva
- **Paleta de cores**: Azul, verde, amarelo, vermelho

### Interatividade
- **Navegação dinâmica**: Alternância entre páginas sem reload
- **Modais**: Detalhes de usuários e requisições
- **Notificações**: Sistema de toast messages
- **DataTables**: Tabelas interativas e pesquisáveis

## 🚀 Como Usar

1. **Abrir o arquivo**: `mockup_admin_panel.html`
2. **Navegar**: Use o sidebar ou navbar para alternar entre seções
3. **Interagir**: Clique nos botões para ver ações simuladas
4. **Responsividade**: Teste em diferentes tamanhos de tela

## ⌨️ Atalhos de Teclado

- `Ctrl + 1`: Dashboard
- `Ctrl + 2`: Usuários
- `Ctrl + 3`: Requisições

## 📱 Responsividade

O mockup é totalmente responsivo e se adapta a:
- **Desktop**: Layout completo com sidebar expandido
- **Tablet**: Sidebar colapsível
- **Mobile**: Menu hamburger, elementos empilhados

## 🛠️ Tecnologias Utilizadas

- **HTML5**: Estrutura semântica
- **CSS3**: Gradientes, animações, flexbox/grid
- **JavaScript/jQuery**: Interatividade dinâmica
- **Bootstrap 4**: Framework CSS responsivo
- **AdminLTE 3**: Template administrativo
- **FontAwesome**: Ícones vetoriais
- **DataTables**: Tabelas avançadas

## 📊 Dados de Exemplo

Todos os dados exibidos são fictícios e servem apenas para demonstração:
- Usuários, clientes e requisições simuladas
- Métricas calculadas com valores realistas
- Gráficos representados por placeholders

## 🎯 Funcionalidades Planejadas (Não Implementadas)

- Integração com backend real
- Autenticação de usuários
- Upload real de arquivos
- Gráficos funcionais (Chart.js)
- Persistência de dados
- API REST
- Notificações em tempo real

## 📝 Estrutura do Projeto

```
mockup_admin_panel.html
├── Header/Navbar
├── Sidebar Menu
├── Main Content
│   ├── Dashboard
│   ├── Usuários
│   ├── Clientes
│   ├── Requisições
│   ├── Depoimentos
│   ├── Mídia
│   ├── Relatórios
│   └── Sistema
├── Modals
├── Notifications
└── Scripts
```

## 🔧 Personalização

Para adaptar o mockup ao seu projeto:

1. **Cores**: Modifique as variáveis CSS no `:root`
2. **Logo**: Substitua o texto "CONFINTER" por uma imagem
3. **Dados**: Atualize os valores nos elementos HTML
4. **Funcionalidades**: Adicione JavaScript para integrações reais

## 📄 Licença

Este mockup é parte do projeto CONFINTER e segue a mesma licença do projeto principal.