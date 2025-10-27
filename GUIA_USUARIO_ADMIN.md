# CONFINTER - Guia do Usuário - Painel Administrativo

## 📋 Visão Geral

Este guia apresenta todas as funcionalidades do painel administrativo do sistema CONFINTER, explicando como utilizar cada recurso de forma prática e eficiente.

## 🔐 Acesso ao Sistema

### Login
1. Acesse: `http://localhost/admin/login.php`
2. Digite suas credenciais:
   - **Usuário:** admin (ou seu usuário cadastrado)
   - **Senha:** admin (ou sua senha)
3. Clique em "Entrar"

### Recuperação de Senha
1. No site principal, clique em "Esqueceu a senha?"
2. Digite seu email cadastrado
3. Aguarde aprovação do administrador
4. Receba a nova senha por email

---

## 🏠 Dashboard Principal

### Visão Geral
O dashboard apresenta métricas em tempo real e gráficos interativos do sistema.

### Métricas Disponíveis
- **Visitas Hoje:** Número total de visitantes únicos
- **Requisições:** Solicitações de crédito recebidas
- **Taxa de Conversão:** Percentual de visitantes que fazem requisições
- **Usuários:** Total de usuários cadastrados

### Gráficos
- **Visitas por Dia:** Evolução diária dos últimos 30 dias
- **Horários de Pico:** Distribuição de visitas por hora
- **Heatmap:** Padrões de visita por dia da semana e hora

### Navegação
- Use o menu lateral para acessar diferentes módulos
- Todas as páginas carregam dentro do iframe principal
- Mantenha múltiplas abas abertas simultaneamente

---

## 👥 Gestão de Usuários

### Listar Usuários
1. Acesse: Menu Lateral → Usuários
2. Visualize tabela com paginação automática
3. Use filtros de busca por nome, email ou status

### Adicionar Novo Usuário
1. Clique em "Novo Usuário"
2. Preencha os campos obrigatórios:
   - Nome completo
   - Email (único)
   - Usuário (único)
   - Senha
   - Perfil (admin/usuário/moderador)
3. Clique em "Salvar"

### Editar Usuário
1. Na tabela, clique no botão "Editar" (ícone lápis)
2. Modifique os dados desejados
3. Clique em "Salvar Alterações"

### Alterar Status
1. Clique no botão de status (Ativo/Inativo)
2. Confirme a alteração
3. O status muda imediatamente via AJAX

### Excluir Usuário
1. Clique no botão "Excluir" (ícone lixeira)
2. Confirme a exclusão
3. O usuário será removido permanentemente

### Perfil do Usuário
1. Acesse: Menu Lateral → Perfil
2. Visualize suas informações pessoais
3. Faça upload de avatar (formatos: JPG, PNG, GIF)
4. Altere senha se necessário

---

## 💬 Moderação de Depoimentos

### Visualizar Depoimentos Pendentes
1. Acesse: Menu Lateral → Depoimentos
2. Visualize lista com status coloridos:
   - 🟡 **Pendente:** Aguardando moderação
   - 🟢 **Aprovado:** Publicados no site
   - 🔴 **Rejeitado:** Não publicados

### Moderação Individual
1. Clique em "Ver Detalhes" no depoimento desejado
2. Leia o conteúdo completo no modal
3. Clique em:
   - ✅ **Aprovar:** Publica o depoimento
   - ❌ **Rejeitar:** Remove o depoimento

### Moderação em Massa
1. Marque múltiplos depoimentos
2. Use botões superiores para ação coletiva
3. Confirme a operação

### Estatísticas
- **Total de Depoimentos:** Contagem geral
- **Pendentes:** Requerem atenção
- **Aprovados:** Publicados
- **Rejeitados:** Removidos

---

## 📊 Sistema de Relatórios

### Tipos de Relatório Disponíveis

#### 1. Relatório de Visitas
- Período personalizado
- Gráfico de evolução diária
- Top páginas visitadas
- Origem dos visitantes

#### 2. Relatório de Requisições
- Análise por categoria
- Horários preferidos
- Conversão por fonte
- Status das solicitações

#### 3. Relatório de Usuários
- Novos cadastros por período
- Atividade dos usuários
- Perfis mais ativos
- Taxa de retenção

#### 4. Relatório Financeiro
- Simulações realizadas
- Taxa de aprovação
- Valores médios
- Performance por período

### Como Gerar Relatórios
1. Acesse: Menu Lateral → Relatórios
2. Selecione o tipo de relatório
3. Defina o período (datas inicial e final)
4. Aplique filtros adicionais se disponíveis
5. Clique em "Gerar Relatório"

### Exportar Dados
1. Após gerar o relatório, clique em:
   - 📊 **Excel:** Planilha completa
   - 📄 **PDF:** Relatório formatado
   - 📋 **CSV:** Dados brutos
2. O arquivo será baixado automaticamente

### Agendamento (Planejado)
- Configurar relatórios automáticos
- Envio por email
- Frequência: diária, semanal, mensal

---

## 👁️ Monitoramento em Tempo Real

### Dashboard de Monitoramento
1. Acesse: Menu Lateral → Monitoramento
2. Visualize métricas atualizadas a cada 30 segundos

### Métricas Monitoradas
- **Visitas Hoje:** Contador em tempo real
- **Última Hora:** Atividade recente
- **Requisições Hoje:** Solicitações recebidas
- **Taxa de Conversão:** Performance atual

### Sistema de Alertas
- 🚨 **Pico de Visitas:** Quando > 10 visitas/hora
- ⚡ **Hora de Pico:** Quando > 15 visitas na hora atual
- 🔴 **Erros do Sistema:** Problemas técnicos
- 🟡 **Avisos:** Manutenção necessária

### Controles Interativos
- **Pausar:** Interrompe atualizações automáticas
- **Retomar:** Retorna ao modo automático
- **Atualizar:** Força atualização manual
- **Exportar:** Salva dados atuais

### Logs de Atividade
- Registro de todas as ações dos usuários
- Filtros por data, usuário e tipo de ação
- Exportação para auditoria

---

## 🔑 Sistema de Reset de Senha

### Para Administradores

#### Gerenciar Solicitações
1. Acesse: Menu Lateral → Reset Senha
2. Visualize solicitações pendentes
3. Para cada solicitação:
   - Clique em "Gerar Senha"
   - Sistema cria senha temporária automaticamente
   - Clique em "Marcar como Processada"

#### Informar Usuário
1. Anote a senha gerada
2. Entre em contato com o usuário
3. Forneça a nova senha
4. Oriente sobre alteração posterior

### Para Usuários

#### Solicitar Reset
1. No site principal, clique em "Esqueceu a senha?"
2. Digite seu email cadastrado
3. Clique em "Enviar Solicitação"
4. Aguarde aprovação do administrador

#### Receber Nova Senha
1. Aguarde contato do administrador
2. Receba a senha temporária
3. Faça login com a nova senha
4. Altere para uma senha pessoal

---

## 🖼️ Gestão de Galeria

### Upload de Imagens
1. Acesse: Menu Lateral → Galeria
2. Clique em "Upload de Imagens"
3. Selecione múltiplas imagens (JPG, PNG, GIF)
4. Visualize preview antes do envio
5. Clique em "Enviar Imagens"

### Gerenciar Imagens
1. Visualize grid de imagens existentes
2. Para cada imagem:
   - **Editar:** Alterar título, descrição, ordem
   - **Excluir:** Remover permanentemente
   - **Ativar/Desativar:** Controlar exibição

### Carrossel/Slider
1. Organize a ordem das imagens
2. Ative/desative imagens específicas
3. Configure intervalo de transição
4. Preview do carrossel em tempo real

---

## ⚙️ Configurações do Sistema

### Configurações Gerais
1. Acesse: Menu Lateral → Configurações
2. Ajuste parâmetros do sistema:
   - Limite de upload de arquivos
   - Configurações de email
   - Parâmetros de segurança
   - Configurações de backup

### Backup do Sistema
1. Acesse: Menu Lateral → Backup
2. Clique em "Fazer Backup Agora"
3. Escolha tipo de backup:
   - **Completo:** Banco + arquivos
   - **Apenas Banco:** Dados do MySQL
   - **Apenas Arquivos:** Uploads e configurações

### Restaurar Backup
1. Na lista de backups, clique em "Restaurar"
2. Confirme a operação
3. Sistema será restaurado automaticamente

### Logs do Sistema
1. Visualize logs de erro e acesso
2. Filtre por data e tipo
3. Exporte para análise
4. Monitore performance

---

## 📈 Análises Avançadas

### Análise Exploratória
1. Acesse: Menu Lateral → Análises
2. Visualize estatísticas completas:
   - Visitas por dia/hora
   - Requisições por categoria
   - Taxa de conversão
   - Top páginas visitadas

### Previsões de Pico
1. Acesse: Menu Lateral → Previsões
2. Visualize previsões automáticas:
   - Algoritmo de tendência linear
   - Fatores de ajuste (dia útil, horário comercial)
   - Classificação de picos (Alto/Médio/Normal)

### Machine Learning (Planejado)
- Previsões mais avançadas com Random Forest
- Análise de padrões sazonais
- Recomendações automáticas
- Detecção de anomalias

---

## 🔍 Busca e Filtros

### Funcionalidades de Busca
- **Busca Global:** Campo de busca no topo
- **Filtros Avançados:** Em cada módulo
- **Ordenação:** Por qualquer coluna
- **Paginação:** Navegação automática

### Dicas de Uso
- Use palavras-chave específicas
- Combine múltiplos filtros
- Ordene por data para dados recentes
- Use paginação para performance

---

## 📱 Responsividade

### Dispositivos Suportados
- **Desktop:** Interface completa
- **Tablet:** Layout adaptável
- **Mobile:** Interface otimizada

### Navegação Mobile
- Menu hambúrguer
- Toques e gestos
- Formulários adaptados
- Gráficos responsivos

---

## 🆘 Solução de Problemas

### Problemas Comuns

#### Não consegue fazer login
- Verifique usuário e senha
- Contate administrador para reset
- Limpe cache do navegador

#### Dados não atualizam
- Verifique conexão com internet
- Recarregue a página (F5)
- Aguarde atualização automática

#### Upload de arquivos falha
- Verifique tamanho do arquivo (< 5MB)
- Formatos aceitos: JPG, PNG, GIF, PDF
- Permissões da pasta de upload

#### Relatórios não geram
- Verifique período selecionado
- Confirme existência de dados
- Tente período menor

### Suporte Técnico
- **Email:** suporte@confinter.com.br
- **Documentação:** README.md
- **Logs:** Verifique pasta logs/
- **Backup:** Sempre faça backup antes de alterações

---

## ⌨️ Atalhos de Teclado

### Navegação Geral
- **Ctrl + B:** Focar na busca
- **Ctrl + N:** Novo item (quando disponível)
- **Ctrl + S:** Salvar (em formulários)
- **Esc:** Fechar modais

### Tabelas
- **Setas:** Navegar entre células
- **Enter:** Editar célula
- **Tab:** Próxima célula
- **Shift + Tab:** Célula anterior

---

## 📊 Métricas de Uso

### Indicadores de Performance
- **Tempo Médio de Resposta:** < 2 segundos
- **Taxa de Disponibilidade:** > 99%
- **Satisfação do Usuário:** > 4.5/5
- **Conversão de Leads:** > 15%

### Monitoramento Contínuo
- Uptime do sistema
- Performance das queries
- Uso de recursos (CPU, memória)
- Logs de erro automatizados

---

## 🔄 Atualizações e Manutenção

### Manutenção Preventiva
- Backup automático diário
- Limpeza de logs antigos
- Otimização de banco de dados
- Atualização de dependências

### Atualizações do Sistema
- Notificações de novas versões
- Changelog detalhado
- Procedimentos de atualização
- Rollback automático em caso de falha

---

## 📞 Contato e Suporte

### Canais de Suporte
- **Email:** suporte@confinter.com.br
- **Chat:** Integrado no sistema (planejado)
- **Telefone:** (11) 9999-9999
- **WhatsApp:** (11) 9999-9999

### Horário de Atendimento
- **Segunda a Sexta:** 8h às 18h
- **Sábado:** 8h às 12h
- **Domingo:** Plantão 9h às 17h
- **Emergências:** 24/7

### SLA de Resposta
- **Crítico:** < 1 hora
- **Alto:** < 4 horas
- **Médio:** < 24 horas
- **Baixo:** < 72 horas

---

**Data:** 06 de Setembro de 2025
**Versão:** 1.0
**Status:** ✅ Documentação Completa