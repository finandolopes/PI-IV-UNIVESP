# Manual de Implementação - CONFINTER
## Sistema de Análise de Dados e Gestão para Correspondente Bancário

**Data:** 06 de Setembro de 2025  
**Versão:** 1.0  
**Projeto:** DRP01 - Projeto Integrador em Computação II - Turma 006  

---

## 📋 Sumário

1. [Introdução](#introdução)
2. [Pré-requisitos](#pré-requisitos)
3. [Arquitetura do Sistema](#arquitetura-do-sistema)
4. [Estrutura de Diretórios](#estrutura-de-diretórios)
5. [Instalação e Configuração](#instalação-e-configuração)
6. [Banco de Dados](#banco-de-dados)
7. [Implementação das Funcionalidades](#implementação-das-funcionalidades)
8. [APIs e Integrações](#apis-e-integrações)
9. [Testes](#testes)
10. [Deploy em Produção](#deploy-em-produção)
11. [Manutenção e Suporte](#manutenção-e-suporte)

---

## 🎯 Introdução

### Visão Geral do Projeto

O CONFINTER é um sistema completo para correspondente bancário que oferece:

- **Site Institucional**: Apresentação da empresa e serviços
- **Sistema de Análise de Crédito**: Avaliação automática de solicitações
- **Painel Administrativo**: Gestão completa de usuários, clientes e dados
- **Análises Avançadas**: Algoritmos de ML para previsões de pico
- **Monitoramento em Tempo Real**: Dashboard com métricas ao vivo
- **Sistema de Reset de Senha**: Recuperação segura de acesso
- **Integração com APIs**: Conexão com serviços externos

### Objetivos

- Facilitar o acesso a crédito consignado
- Automatizar processos de análise de crédito
- Fornecer insights através de dados
- Oferecer experiência completa ao usuário

---

## 🔧 Pré-requisitos

### Tecnologias Necessárias

- **Servidor Web**: Apache 2.4+ ou Nginx
- **PHP**: 8.0+ com extensões:
  - mysqli
  - pdo_mysql
  - mbstring
  - json
  - curl
- **Banco de Dados**: MySQL 8.0+ ou MariaDB 10.5+
- **Navegador**: Chrome 90+, Firefox 88+, Safari 14+

### Dependências PHP

```bash
# Instalar PHP e extensões
sudo apt update
sudo apt install php8.1 php8.1-mysql php8.1-mbstring php8.1-json php8.1-curl php8.1-zip
```

### Bibliotecas JavaScript

- **Chart.js**: Para gráficos e visualizações
- **Bootstrap 5**: Framework CSS
- **jQuery 3.6**: Manipulação DOM
- **SweetAlert2**: Notificações
- **AOS**: Animações

---

## 🏗️ Arquitetura do Sistema

### Padrões Utilizados

- **MVC**: Separação entre Model, View e Controller
- **REST API**: Comunicação entre frontend e backend
- **Prepared Statements**: Segurança contra SQL Injection
- **Sessions**: Controle de autenticação
- **AJAX**: Atualizações assíncronas

### Componentes Principais

```
├── Frontend (HTML/CSS/JS)
├── Backend (PHP)
├── Banco de Dados (MySQL)
├── APIs Externas
└── Sistema de Arquivos
```

---

## 📁 Estrutura de Diretórios

```
DRP01-Projeto-Integrador/
├── index.php                 # Página inicial
├── analise_php.php          # Análise exploratória
├── previsao_php.php         # Previsões ML
├── dashboard_php.php        # Dashboard interativo
├── tempo_real.html          # REMOVIDO - Agora integrado no admin
├── mockup.html              # Mockup do sistema
├── requirements.txt         # Dependências Python (se usado)
├── admin/                   # Painel administrativo
│   ├── admin.php           # Dashboard admin
│   ├── login.php           # Login admin
│   ├── monitoramento.php   # NOVO: Monitoramento em tempo real
│   ├── navbar.php          # Barra de navegação
│   ├── sidebar.php         # Menu lateral (atualizado)
│   ├── listarusuario.php   # Gestão de usuários
│   ├── listaclientes.php   # Gestão de clientes
│   └── ...
├── api/                    # APIs REST
│   └── get_dados_tempo_real.php
├── assets/                 # Recursos estáticos
│   ├── css/
│   │   ├── style.css      # Estilos principais
│   │   └── acb.css        # Estilos customizados
│   ├── js/
│   │   ├── acb.js         # Scripts customizados
│   │   └── main.js        # Script principal
│   ├── img/               # Imagens
│   └── vendor/            # Bibliotecas externas
├── php/                   # Scripts PHP
│   ├── conexao.php        # Conexão banco de dados
│   ├── process.php        # Processamento formulários
│   ├── funcoes_clientes.php
│   ├── funcoes_usuarios.php
│   └── ...
├── sql/                   # Scripts SQL
│   ├── confinter.sql      # Banco completo
│   └── atualizacoes_analise.sql
└── bkp/                   # Backup de arquivos
```

---

## 🚀 Instalação e Configuração

### 1. Clonagem do Repositório

```bash
git clone https://github.com/finandolopes/DRP01-Projeto-Integrador-em-Computa-o-II-Turma-006.git
cd DRP01-Projeto-Integrador-em-Computa-o-II-Turma-006
```

### 2. Configuração do Servidor Web

#### Apache
```apache
<VirtualHost *:80>
    ServerName confinter.local
    DocumentRoot /var/www/html/DRP01-Projeto-Integrador
    
    <Directory /var/www/html/DRP01-Projeto-Integrador>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

#### Nginx
```nginx
server {
    listen 80;
    server_name confinter.local;
    root /var/www/html/DRP01-Projeto-Integrador;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
    }
}
```

### 3. Configuração do Banco de Dados

```bash
# Criar banco de dados
mysql -u root -p
CREATE DATABASE confinter;
EXIT;

# Importar estrutura
mysql -u root -p confinter < sql/confinter.sql
```

### 4. Configuração PHP

```php
// php/conexao.php
<?php
$servername = "localhost";
$username = "seu_usuario";
$password = "sua_senha";
$dbname = "confinter";

$conexao = mysqli_connect($servername, $username, $password, $dbname);

if (!$conexao) {
    die("Conexão falhou: " . mysqli_connect_error());
}
?>
```

### 5. Configuração de Permissões

```bash
# Definir permissões corretas
sudo chown -R www-data:www-data /var/www/html/DRP01-Projeto-Integrador
sudo chmod -R 755 /var/www/html/DRP01-Projeto-Integrador
sudo chmod -R 777 /var/www/html/DRP01-Projeto-Integrador/assets/img/uploads/
```

---

## 🗄️ Banco de Dados

### Estrutura Principal

```sql
-- Tabelas principais
CREATE TABLE usuarios (
    id_usuario INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    usuario VARCHAR(50) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    perfil ENUM('admin', 'usuario', 'moderador') DEFAULT 'usuario',
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE clientes (
    id_cliente INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    telefone VARCHAR(20),
    data_nascimento DATE,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE requisicoes (
    id_requisicao INT PRIMARY KEY AUTO_INCREMENT,
    id_cliente INT,
    categoria TEXT,
    horario_contato TIME,
    data_requisicao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente)
);

CREATE TABLE contador_visitas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    ip_usuario VARCHAR(45),
    data_visita TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    tempo TIME
);

CREATE TABLE depoimentos (
    id_depoimento INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100),
    mensagem TEXT,
    status ENUM('aprovado', 'pendente', 'rejeitado') DEFAULT 'pendente',
    data_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Tabelas de Análise

```sql
CREATE TABLE previsoes_pico (
    id INT PRIMARY KEY AUTO_INCREMENT,
    data_previsao DATE,
    hora_pico TIME,
    confianca DECIMAL(5,2),
    algoritmo VARCHAR(50),
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE analises_mensais (
    id INT PRIMARY KEY AUTO_INCREMENT,
    mes_ano VARCHAR(7),
    total_visitas INT,
    total_requisicoes INT,
    taxa_conversao DECIMAL(5,2),
    data_analise TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

## ⚙️ Implementação das Funcionalidades

### 1. Sistema de Visitantes

#### Contador de Visitas
```php
// admin/contador.php
<?php
include('../php/conexao.php');

$ip = $_SERVER['REMOTE_ADDR'];
$data_visita = date('Y-m-d H:i:s');

// Verificar se já visitou hoje
$query = "SELECT * FROM contador_visitas WHERE ip_usuario = ? AND DATE(data_visita) = CURDATE()";
$stmt = mysqli_prepare($conexao, $query);
mysqli_stmt_bind_param($stmt, "s", $ip);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    // Nova visita
    $query = "INSERT INTO contador_visitas (ip_usuario, data_visita) VALUES (?, ?)";
    $stmt = mysqli_prepare($conexao, $query);
    mysqli_stmt_bind_param($stmt, "ss", $ip, $data_visita);
    mysqli_stmt_execute($stmt);
}

echo "Visita registrada";
?>
```

#### JavaScript para Contagem
```javascript
// assets/js/contador.js
document.addEventListener('DOMContentLoaded', function() {
    fetch('admin/contador.php', {
        method: 'GET',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    })
    .then(response => response.text())
    .then(data => console.log(data))
    .catch(error => console.error('Erro:', error));
});
```

### 2. Sistema de Análise de Crédito

#### Formulário de Requisição
```php
// index.php - Seção Requisição
<section id="requi" class="requisicoes section-bg">
    <div class="container">
        <div class="section-title">
            <h2>Requisição de Análise de Crédito</h2>
        </div>
        <form action="php/process.php" method="POST" id="form-requisicao">
            <!-- Campos do formulário -->
            <div class="form-group">
                <label for="nome">Nome completo:</label>
                <input type="text" class="form-control" id="nome" name="nome" required>
            </div>
            <!-- Outros campos -->
            <button type="submit" class="btn btn-primary">Enviar Requisição</button>
        </form>
    </div>
</section>
```

#### Processamento do Formulário
```php
// php/process.php
<?php
include('conexao.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = mysqli_real_escape_string($conexao, $_POST['nome']);
    $email = mysqli_real_escape_string($conexao, $_POST['email']);
    $telefone = mysqli_real_escape_string($conexao, $_POST['telefone']);
    
    // Inserir cliente
    $query = "INSERT INTO clientes (nome, email, telefone) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($conexao, $query);
    mysqli_stmt_bind_param($stmt, "sss", $nome, $email, $telefone);
    
    if (mysqli_stmt_execute($stmt)) {
        $id_cliente = mysqli_insert_id($conexao);
        
        // Inserir requisição
        $categoria = implode(", ", $_POST['categoria']);
        $query_req = "INSERT INTO requisicoes (id_cliente, categoria) VALUES (?, ?)";
        $stmt_req = mysqli_prepare($conexao, $query_req);
        mysqli_stmt_bind_param($stmt_req, "is", $id_cliente, $categoria);
        mysqli_stmt_execute($stmt_req);
        
        echo "Requisição enviada com sucesso!";
    }
}
?>
```

### 3. Painel Administrativo

#### Sistema de Login
```php
// admin/login.php
<?php
session_start();
include('../php/conexao.php');

if (isset($_POST['usuario']) && isset($_POST['senha'])) {
    $login = mysqli_real_escape_string($con, $_POST['usuario']);
    $senha = mysqli_real_escape_string($con, $_POST['senha']);
    
    $query = "SELECT id_usuario FROM adm WHERE usuario = '$login' AND senha = md5('$senha')";
    $res = mysqli_query($con, $query);
    
    if (mysqli_num_rows($res) == 1) {
        $_SESSION['usuario'] = $login;
        header('Location: admin.php');
        exit();
    } else {
        $_SESSION['nao_autenticado'] = true;
        header('Location: ../index.php?login=erro');
        exit();
    }
}
?>
```

#### Dashboard Admin
```php
// admin/admin.php
<?php
session_start();
include('../php/conexao.php');

// Verificar login
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

// Consultas para estatísticas
$sqlVisitas = "SELECT COUNT(*) as total FROM contador_visitas";
$resultVisitas = mysqli_query($conexao, $sqlVisitas);
$totalVisitas = mysqli_fetch_assoc($resultVisitas)['total'];

$sqlDepoimentos = "SELECT COUNT(*) as total FROM depoimentos";
$resultDepoimentos = mysqli_query($conexao, $sqlDepoimentos);
$totalDepoimentos = mysqli_fetch_assoc($resultDepoimentos)['total'];
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <title>CONFINTER - Painel Administrativo</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    <?php include 'sidebar.php'; ?>
    
    <div class="page-wrapper">
        <div class="content">
            <div class="row">
                <div class="col-lg-3 col-sm-6 col-12">
                    <div class="dash-count">
                        <div class="dash-counts">
                            <h3><?php echo $totalVisitas; ?></h3>
                            <h5>Total de Visitas</h5>
                        </div>
                    </div>
                </div>
                <!-- Outros cards -->
            </div>
        </div>
    </div>
</body>
</html>
```

### 4. Sistema de Análises

#### Análise Exploratória
```php
// analise_php.php
<?php
include('php/conexao.php');

// Consulta de visitas por dia
$query = "SELECT DATE(data_visita) as data, COUNT(*) as visitas 
          FROM contador_visitas 
          GROUP BY DATE(data_visita) 
          ORDER BY data DESC LIMIT 30";

$result = mysqli_query($conexao, $query);
$dados = array();
while ($row = mysqli_fetch_assoc($result)) {
    $dados[] = $row;
}

// Estatísticas gerais
$totalVisitas = mysqli_num_rows(mysqli_query($conexao, "SELECT * FROM contador_visitas"));
$totalRequisicoes = mysqli_num_rows(mysqli_query($conexao, "SELECT * FROM requisicoes"));
$taxaConversao = $totalVisitas > 0 ? ($totalRequisicoes / $totalVisitas) * 100 : 0;
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <title>Análise Exploratória - CONFINTER</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container">
        <h2>Análise Exploratória de Dados</h2>
        
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5>Total de Visitas</h5>
                        <h3><?php echo $totalVisitas; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5>Total de Requisições</h5>
                        <h3><?php echo $totalRequisicoes; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5>Taxa de Conversão</h5>
                        <h3><?php echo number_format($taxaConversao, 2); ?>%</h3>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <canvas id="visitasChart"></canvas>
            </div>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('visitasChart').getContext('2d');
        const visitasChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($dados, 'data')); ?>,
                datasets: [{
                    label: 'Visitas por Dia',
                    data: <?php echo json_encode(array_column($dados, 'visitas')); ?>,
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.1
                }]
            }
        });
    </script>
</body>
</html>
```

#### Previsões com Machine Learning
```php
// previsao_php.php
<?php
include('php/conexao.php');

// Algoritmo de regressão linear simples para previsões
function regressaoLinear($x, $y) {
    $n = count($x);
    $sumX = array_sum($x);
    $sumY = array_sum($y);
    $sumXY = 0;
    $sumXX = 0;
    
    for ($i = 0; $i < $n; $i++) {
        $sumXY += $x[$i] * $y[$i];
        $sumXX += $x[$i] * $x[$i];
    }
    
    $slope = ($n * $sumXY - $sumX * $sumY) / ($n * $sumXX - $sumX * $sumX);
    $intercept = ($sumY - $slope * $sumX) / $n;
    
    return ['slope' => $slope, 'intercept' => $intercept];
}

// Dados históricos
$query = "SELECT HOUR(data_visita) as hora, COUNT(*) as visitas 
          FROM contador_visitas 
          WHERE DATE(data_visita) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
          GROUP BY HOUR(data_visita)";

$result = mysqli_query($conexao, $query);
$horas = array();
$visitas = array();

while ($row = mysqli_fetch_assoc($result)) {
    $horas[] = $row['hora'];
    $visitas[] = $row['visitas'];
}

// Calcular regressão
$regressao = regressaoLinear($horas, $visitas);

// Prever picos
$picos = array();
for ($hora = 0; $hora < 24; $hora++) {
    $previsao = $regressao['intercept'] + $regressao['slope'] * $hora;
    if ($previsao > array_sum($visitas) / count($visitas)) {
        $picos[] = $hora;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <title>Previsões - CONFINTER</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container">
        <h2>Previsões de Horários de Pico</h2>
        
        <div class="row">
            <div class="col-md-6">
                <canvas id="previsaoChart"></canvas>
            </div>
            <div class="col-md-6">
                <h4>Horários de Pico Previstos:</h4>
                <ul>
                    <?php foreach ($picos as $pico): ?>
                        <li><?php echo $pico; ?>:00 - Alto tráfego esperado</li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('previsaoChart').getContext('2d');
        const previsaoChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($horas); ?>,
                datasets: [{
                    label: 'Visitas por Hora',
                    data: <?php echo json_encode($visitas); ?>,
                    borderColor: 'rgb(255, 99, 132)',
                    backgroundColor: 'rgba(255, 99, 132, 0.2)'
                }]
            }
        });
    </script>
</body>
</html>
```

### 5. Monitoramento em Tempo Real

#### API para Dados em Tempo Real
```php
// api/get_dados_tempo_real.php
<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
include('../php/conexao.php');

// Consultas para métricas em tempo real
$queries = [
    'visitas_hoje' => "SELECT COUNT(*) as total FROM contador_visitas WHERE DATE(data_visita) = CURDATE()",
    'requisicoes_hoje' => "SELECT COUNT(*) as total FROM requisicoes WHERE DATE(data_requisicao) = CURDATE()",
    'depoimentos_pendentes' => "SELECT COUNT(*) as total FROM depoimentos WHERE status = 'pendente'",
    'tempo_medio' => "SELECT SEC_TO_TIME(AVG(TIME_TO_SEC(tempo))) as media FROM contador_visitas"
];

$dados = array();
foreach ($queries as $key => $query) {
    $result = mysqli_query($conexao, $query);
    $dados[$key] = mysqli_fetch_assoc($result)['total'] ?? 0;
}

// Verificar alertas
$alertas = array();
if ($dados['visitas_hoje'] > 100) {
    $alertas[] = "Alto tráfego detectado hoje!";
}
if ($dados['depoimentos_pendentes'] > 5) {
    $alertas[] = "Depoimentos pendentes para moderação!";
}

$dados['alertas'] = $alertas;
$dados['timestamp'] = date('Y-m-d H:i:s');

echo json_encode($dados);
?>
```

#### Monitoramento em Tempo Real (Painel Admin)

O monitoramento em tempo real agora está integrado no painel administrativo em `admin/monitoramento.php`, seguindo o mesmo padrão de layout e design dos outros módulos do admin.

**Funcionalidades:**
- **Métricas em tempo real**: Visitas hoje, requisições, depoimentos pendentes
- **Gráficos interativos**: Chart.js para visualização de dados ao vivo
- **Alertas automáticos**: Notificações para picos de atividade
- **Controles de monitoramento**: Botões para pausar/retomar atualizações
- **Log de atividades**: Registro de eventos do sistema
- **Integração com API**: `api/get_dados_tempo_real.php`

**Estrutura do arquivo `admin/monitoramento.php`:**
```php
<?php
session_start();
require_once '../php/verifica_login.php';
require_once '../php/conexao.php';

// Verificar se usuário está logado e é admin
verificarLoginAdmin();

include 'navbar.php';
include 'sidebar.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <h2>Monitoramento em Tempo Real</h2>
        
        <!-- Cards de métricas -->
        <div class="row">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5>Visitas Hoje</h5>
                        <h3 id="visitas-hoje">0</h3>
                    </div>
                </div>
            </div>
            <!-- ... outros cards ... -->
        </div>
        
        <!-- Gráfico -->
        <canvas id="tempoRealChart"></canvas>
        
        <!-- Controles -->
        <button id="pause-btn" class="btn btn-warning">Pausar</button>
        <button id="resume-btn" class="btn btn-success" style="display:none;">Retomar</button>
    </div>
</div>

<script>
// JavaScript para atualização em tempo real
// (igual ao código anterior, mas integrado no admin)
</script>
```

**Vantagens da integração:**
- **Consistência visual**: Mesmo layout do painel admin
- **Controle de acesso**: Protegido por autenticação admin
- **Navegação unificada**: Acesso via menu lateral
- **Responsividade**: Design adaptável mantido
- **Manutenibilidade**: Código organizado na estrutura admin

### 6. Sistema de Reset de Senha

#### Funcionalidades Implementadas

O sistema de reset de senha permite que usuários recuperem o acesso de forma segura através do painel administrativo.

**Componentes:**
- **Modal de Solicitação**: Formulário no site para solicitar reset
- **Tabela de Solicitações**: Armazenamento seguro no banco de dados
- **Painel Admin**: Interface para gerenciar solicitações
- **Geração Automática**: Senhas temporárias geradas automaticamente

#### Estrutura da Tabela `reset_senha`

```sql
CREATE TABLE reset_senha (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_usuario INT NOT NULL,
  email VARCHAR(255) NOT NULL,
  token VARCHAR(255) NOT NULL,
  data_solicitacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  status ENUM('pendente','processado','expirado') DEFAULT 'pendente',
  nova_senha VARCHAR(255) DEFAULT NULL,
  data_processamento TIMESTAMP NULL
);
```

#### Fluxo de Funcionamento

1. **Usuário solicita reset** através do link "Esqueceu a senha?"
2. **Sistema registra** a solicitação na tabela `reset_senha`
3. **Administrador visualiza** solicitações no painel admin
4. **Admin gera nova senha** clicando em "Gerar Senha"
5. **Sistema atualiza** senha do usuário e marca solicitação como processada
6. **Admin informa** a nova senha ao usuário

#### Arquivos Implementados

- `php/processa_reset_senha.php`: Processa solicitações do formulário
- `admin/reset_senha.php`: Painel de gerenciamento para administradores
- `sql/reset_senha.sql`: Script para criação da tabela
- Modal integrado no `index.php`: Interface de solicitação

#### Segurança Implementada

- **Token único**: Cada solicitação gera um token aleatório
- **Validação de email**: Verifica se email existe na base
- **Controle de status**: Rastreia estado de cada solicitação
- **Logs de auditoria**: Registra data de processamento
- **Acesso restrito**: Apenas administradores podem gerar senhas

---

## 🔌 APIs e Integrações

### API de Simulação de Empréstimo

```php
// api/simulacao.php
<?php
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$valor = $input['valor'];
$parcelas = $input['parcelas'];

// Simulação simples (em produção, integrar com API real)
$taxa_juros = 0.02; // 2% ao mês
$parcela = ($valor * $taxa_juros) / (1 - pow(1 + $taxa_juros, -$parcelas));

$response = [
    'valor' => $valor,
    'parcelas' => $parcelas,
    'taxa' => ($taxa_juros * 100) . '%',
    'parcela' => number_format($parcela, 2, ',', '.'),
    'total' => number_format($parcela * $parcelas, 2, ',', '.')
];

echo json_encode($response);
?>
```

### Integração com APIs Externas

```php
// Exemplo: Integração com API de CEP
function consultarCEP($cep) {
    $url = "https://viacep.com.br/ws/$cep/json/";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}

// Exemplo: Integração com API de SMS
function enviarSMS($telefone, $mensagem) {
    $api_key = 'SUA_API_KEY';
    $url = 'https://api.sms.com/send';
    
    $data = [
        'to' => $telefone,
        'message' => $mensagem,
        'api_key' => $api_key
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}
```

---

## 🧪 Testes

### Testes Unitários

```php
// tests/test_conexao.php
<?php
require_once '../php/conexao.php';

class TestConexao extends PHPUnit_Framework_TestCase {
    public function testConexaoBancoDados() {
        global $conexao;
        $this->assertNotNull($conexao);
        $this->assertTrue(mysqli_ping($conexao));
    }
    
    public function testConsultaSimples() {
        global $conexao;
        $result = mysqli_query($conexao, "SELECT 1");
        $this->assertNotFalse($result);
        $this->assertEquals(1, mysqli_fetch_assoc($result)['1']);
    }
}
?>
```

### Testes de Integração

```php
// tests/test_formulario.php
<?php
class TestFormulario extends PHPUnit_Framework_TestCase {
    public function testEnvioFormulario() {
        // Simular envio de formulário
        $_POST = [
            'nome' => 'João Silva',
            'email' => 'joao@example.com',
            'telefone' => '(11) 99999-9999'
        ];
        
        // Incluir script de processamento
        ob_start();
        include '../php/process.php';
        $output = ob_get_clean();
        
        $this->assertContains('sucesso', $output);
    }
}
?>
```

### Testes de API

```bash
# Testar API de dados tempo real
curl -X GET http://localhost/api/get_dados_tempo_real.php

# Testar API de simulação
curl -X POST http://localhost/api/simulacao.php \
  -H "Content-Type: application/json" \
  -d '{"valor": 10000, "parcelas": 12}'
```

---

## 🚀 Deploy em Produção

### Configuração do Servidor

```bash
# Instalar Apache/Nginx
sudo apt update
sudo apt install apache2

# Configurar virtual host
sudo nano /etc/apache2/sites-available/confinter.conf

# Habilitar site
sudo a2ensite confinter.conf
sudo systemctl reload apache2
```

### Otimização de Performance

```apache
# .htaccess para otimização
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType image/png "access plus 1 month"
</IfModule>

<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
</IfModule>
```

### Backup Automático

```bash
# Script de backup
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u usuario -psenha confinter > backup_$DATE.sql
tar -czf backup_$DATE.tar.gz backup_$DATE.sql assets/img/uploads/
```

### Monitoramento

```bash
# Instalar ferramentas de monitoramento
sudo apt install htop iotop
sudo apt install apache2-utils # Para ab

# Teste de carga
ab -n 1000 -c 10 http://localhost/
```

---

## 🔧 Manutenção e Suporte

### Logs de Sistema

```php
// Sistema de logging
function logEvento($mensagem, $tipo = 'INFO') {
    $data = date('Y-m-d H:i:s');
    $log = "[$data] [$tipo] $mensagem\n";
    file_put_contents('logs/sistema.log', $log, FILE_APPEND);
}

// Uso
logEvento('Usuário logado: ' . $_SESSION['usuario']);
logEvento('Erro de conexão com banco', 'ERROR');
```

### Atualizações de Segurança

```bash
# Atualizar dependências
composer update

# Verificar vulnerabilidades
composer audit

# Atualizar sistema
sudo apt update && sudo apt upgrade
```

### Suporte ao Usuário

- **Documentação**: Manter documentação atualizada
- **Logs de Erro**: Monitorar logs do PHP e Apache
- **Backup Regular**: Executar backups diários
- **Monitoramento**: Configurar alertas para métricas críticas

---

## 📞 Contato e Suporte

**Equipe de Desenvolvimento:**  
DRP01 - Projeto Integrador em Computação II  
Turma 006  

**Contato:**  
- Email: contato@confinter.com.br  
- GitHub: https://github.com/finandolopes/DRP01-Projeto-Integrador-em-Computa-o-II-Turma-006  

**Documentação Técnica:**  
- [README.md](README.md)  
- [Mockup do Sistema](mockup.html)  
- [Scripts SQL](sql/)  

---

**Última atualização:** 06 de Setembro de 2025  
**Versão:** 1.0  
**Status:** Implementação Completa ✅
