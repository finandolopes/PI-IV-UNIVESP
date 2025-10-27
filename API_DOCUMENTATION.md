# CONFINTER - Documentação Técnica das APIs

## 📋 Visão Geral

O sistema CONFINTER implementa uma arquitetura RESTful com APIs JSON para comunicação entre frontend e backend, proporcionando dados em tempo real e integração com sistemas externos.

## 🔌 APIs Implementadas

### 1. API de Dados em Tempo Real
**Endpoint:** `GET /api/get_dados_tempo_real.php`

#### Descrição
Fornece métricas atualizadas do sistema para dashboards e monitoramento em tempo real.

#### Resposta JSON
```json
{
  "visitas_hoje": 1247,
  "visitas_ultima_hora": 89,
  "requisicoes_hoje": 23,
  "taxa_conversao": 7.1,
  "visitas_por_hora": [12, 8, 15, 23, 45, 67, 89, 123, 156, 134, 98, 87, 76, 65, 54, 43, 32, 45, 67, 89, 123, 98, 76, 54],
  "alertas": [
    {
      "tipo": "pico",
      "titulo": "🚨 Pico de Visitas!",
      "mensagem": "Detectamos 89 visitas na última hora. Prepare equipe adicional"
    },
    {
      "tipo": "pico",
      "titulo": "⚡ Hora de Pico!",
      "mensagem": "Esta hora teve 123 visitas. Momento de alta atividade"
    }
  ],
  "timestamp": "2025-09-06 14:30:00"
}
```

#### Campos da Resposta
- `visitas_hoje`: Número total de visitas no dia atual
- `visitas_ultima_hora`: Visitas registradas na última hora
- `requisicoes_hoje`: Requisições de crédito recebidas hoje
- `taxa_conversao`: Percentual de conversão (requisições/visitas)
- `visitas_por_hora`: Array com 24 posições (visitas por hora do dia)
- `alertas`: Array de objetos de alerta (opcional)
- `timestamp`: Data/hora da geração dos dados

#### Sistema de Alertas
- **pico**: Alertas de tráfego elevado
- **erro**: Alertas de sistema
- **info**: Informações gerais

---

### 2. API de Simulação de Empréstimo (Planejada)
**Endpoint:** `POST /api/simulacao.php`

#### Descrição
Simula condições de empréstimo baseado em valor solicitado e prazo.

#### Request JSON
```json
{
  "valor": 10000.00,
  "parcelas": 12,
  "taxa_juros": 2.5
}
```

#### Response JSON
```json
{
  "valor_solicitado": 10000.00,
  "parcelas": 12,
  "taxa_juros": 2.5,
  "valor_parcela": 895.42,
  "valor_total": 10745.04,
  "data_simulacao": "2025-09-06 14:30:00"
}
```

---

## 🔧 Funcionalidades AJAX Implementadas

### 1. Moderação de Depoimentos
**Arquivo:** `admin/mod_depoimentos.php`

#### Endpoints AJAX
- `POST /admin/processar_depoimento.php`
  - Aprovar/rejeitar depoimentos
  - Parâmetros: `id_depoimento`, `acao` (aprovar/rejeitar)

#### Exemplo de Request
```javascript
fetch('processar_depoimento.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'id_depoimento=123&acao=aprovar'
})
.then(response => response.json())
.then(data => {
    if (data.success) {
        // Atualizar interface
        location.reload();
    }
});
```

### 2. Gestão de Usuários
**Arquivo:** `admin/listarusuario.php`

#### Funcionalidades AJAX
- Alteração de status de usuários
- Exclusão de usuários
- Edição inline de dados

#### Endpoints
- `POST /admin/alterar_status_usuario.php`
- `POST /admin/excluir_usuario.php`
- `POST /admin/editar_usuario.php`

### 3. Upload de Imagens
**Arquivo:** `admin/upload_imagens.php`

#### Funcionalidades
- Upload múltiplo de imagens
- Preview antes do envio
- Validação de tipo e tamanho
- Progress bar

#### Tecnologias
- FormData API
- XMLHttpRequest
- FileReader API

---

## 📊 WebSockets (Planejado)

### Arquitetura Proposta
```
Cliente Browser ↔ WebSocket Server ↔ Banco de Dados
                      ↕
                Redis Cache
```

### Eventos em Tempo Real
- Atualização de métricas
- Notificações de novos depoimentos
- Alertas de sistema
- Atualização de contadores

### Implementação Sugerida
```javascript
// Cliente
const ws = new WebSocket('ws://localhost:8080');

ws.onmessage = function(event) {
    const data = JSON.parse(event.data);
    updateDashboard(data);
};
```

---

## 🔒 Segurança das APIs

### Autenticação
- **Bearer Token**: Para APIs administrativas
- **API Key**: Para integrações externas
- **Rate Limiting**: Controle de frequência de requests

### Validação
- **Input Sanitization**: Limpeza de dados de entrada
- **Type Validation**: Validação de tipos de dados
- **SQL Injection Prevention**: Prepared statements

### Headers de Segurança
```php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
```

---

## 📈 Monitoramento e Logs

### Logs de API
```php
// Em cada endpoint
file_put_contents('logs/api.log',
    sprintf("[%s] %s %s %s\n",
        date('Y-m-d H:i:s'),
        $_SERVER['REQUEST_METHOD'],
        $_SERVER['REQUEST_URI'],
        json_encode($_REQUEST)
    ),
    FILE_APPEND
);
```

### Métricas de Performance
- Tempo de resposta
- Taxa de erro
- Throughput
- Latência

### Dashboard de Monitoramento
- Requests por minuto
- Erros por endpoint
- Tempo médio de resposta
- Status dos serviços

---

## 🚀 Escalabilidade

### Otimizações Implementadas
- **Database Indexing**: Índices em campos de busca frequente
- **Query Optimization**: Consultas otimizadas com EXPLAIN
- **Caching**: Redis para dados frequentes
- **CDN**: Para assets estáticos

### Estratégias de Escalabilidade
- **Load Balancing**: Distribuição de carga
- **Database Sharding**: Particionamento horizontal
- **Microservices**: Separação por domínio
- **Container Orchestration**: Kubernetes/Docker Swarm

---

## 🧪 Testes das APIs

### Testes Unitários
```php
class ApiTest extends PHPUnit_Framework_TestCase {
    public function testDadosTempoReal() {
        // Simular request
        ob_start();
        include 'api/get_dados_tempo_real.php';
        $output = ob_get_clean();

        $data = json_decode($output, true);

        // Assertions
        $this->assertArrayHasKey('visitas_hoje', $data);
        $this->assertArrayHasKey('timestamp', $data);
        $this->assertIsNumeric($data['visitas_hoje']);
    }
}
```

### Testes de Integração
```bash
# Teste da API de dados tempo real
curl -X GET http://localhost/api/get_dados_tempo_real.php \
  -H "Accept: application/json" \
  -w "@curl-format.txt"

# Teste com autenticação
curl -X POST http://localhost/api/admin/usuarios \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"nome":"João","email":"joao@teste.com"}'
```

### Testes de Carga
```bash
# Apache Bench
ab -n 1000 -c 10 http://localhost/api/get_dados_tempo_real.php

# Siege
siege -c 50 -t 30s http://localhost/api/get_dados_tempo_real.php
```

---

## 📚 Documentação Interativa

### Swagger/OpenAPI (Planejado)
```yaml
openapi: 3.0.0
info:
  title: CONFINTER API
  version: 1.0.0
  description: API REST do sistema CONFINTER

paths:
  /api/get_dados_tempo_real.php:
    get:
      summary: Dados em tempo real
      responses:
        '200':
          description: Dados retornados com sucesso
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/DadosTempoReal'
```

### Postman Collection
- Coleção completa de requests
- Variáveis de ambiente
- Testes automatizados
- Documentação integrada

---

## 🔄 Versionamento da API

### Estratégia de Versionamento
- **URL Path**: `/api/v1/get_dados_tempo_real.php`
- **Header**: `Accept: application/vnd.confinter.v1+json`
- **Query Parameter**: `?version=1`

### Backward Compatibility
- Manutenção de versões anteriores
- Deprecation warnings
- Migração gradual de clientes

### Changelog da API
```
v1.1.0 - 2025-09-06
- Adicionado campo 'alertas' na resposta
- Melhorada validação de entrada

v1.0.0 - 2025-08-15
- Lançamento inicial da API
- Endpoint de dados tempo real
```

---

## 🌐 Integrações Futuras

### APIs de Terceiros
- **PagSeguro/Stripe**: Processamento de pagamentos
- **Google Analytics**: Dados de comportamento
- **Mailchimp**: Email marketing
- **Twilio**: SMS e notificações
- **AWS S3**: Armazenamento de arquivos

### Webhooks
- Notificações de eventos
- Sincronização bidirecional
- Automação de processos

### GraphQL (Planejado)
```graphql
query GetDashboardData {
  dashboard {
    visitasHoje
    requisicoesHoje
    taxaConversao
    alertas {
      tipo
      titulo
      mensagem
    }
  }
}
```

---

**Data:** 06 de Setembro de 2025
**Versão:** 1.0
**Status:** ✅ Implementado e Documentado