<?php
session_start();
include_once('../php/conexao.php');

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

if (!isset($_GET['id'])) {
    die('<div class="alert alert-danger">ID da requisição não informado.</div>');
}

$id_requisicao = (int)$_GET['id'];

// Buscar dados completos da requisição
$query = "
    SELECT
        r.*,
        c.nome as cliente_nome,
        c.email as cliente_email,
        c.telefone as cliente_telefone,
        c.cpf,
        c.rg,
        c.data_nascimento,
        c.endereco,
        c.bairro,
        c.cidade,
        c.estado,
        c.cep,
        c.data_cadastro as cliente_data_cadastro,
        a.nome as analista_nome
    FROM requisicoes r
    JOIN clientes c ON r.id_cliente = c.id_cliente
    LEFT JOIN adm a ON r.analista_id = a.id_usuario
    WHERE r.id_requisicao = ?
";

$stmt = $conexao->prepare($query);
$stmt->bind_param('i', $id_requisicao);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die('<div class="alert alert-danger">Requisição não encontrada.</div>');
}

$requisicao = $result->fetch_assoc();
$stmt->close();

// Calcular score de crédito
function calcularScoreCredito($requisicao) {
    $score = 0;

    // Categoria (peso maior para servidores públicos)
    if ($requisicao['categoria'] === 'Servidor Público') {
        $score += 30;
    } elseif ($requisicao['categoria'] === 'Aposentado') {
        $score += 25;
    } elseif ($requisicao['categoria'] === 'Pensionista') {
        $score += 20;
    }

    // Valor solicitado (valores menores têm melhor score)
    $valor = $requisicao['valor_solicitado'];
    if ($valor <= 5000) {
        $score += 25;
    } elseif ($valor <= 10000) {
        $score += 20;
    } elseif ($valor <= 20000) {
        $score += 15;
    } else {
        $score += 10;
    }

    // Prazo (prazos maiores têm melhor score)
    $prazo = $requisicao['prazo'];
    if ($prazo >= 36) {
        $score += 20;
    } elseif ($prazo >= 24) {
        $score += 15;
    } elseif ($prazo >= 12) {
        $score += 10;
    } else {
        $score += 5;
    }

    // Contato prévio
    if ($requisicao['cotacao'] === 'Sim') {
        $score += 15;
    }

    // Interesse em contratação
    if ($requisicao['contratacao'] === 'Sim') {
        $score += 10;
    }

    return min($score, 100);
}

function determinarRisco($score) {
    if ($score >= 80) return ['nivel' => 'Baixo', 'cor' => 'success', 'icone' => 'check-circle', 'recomendacao' => 'Aprovação recomendada'];
    if ($score >= 60) return ['nivel' => 'Médio', 'cor' => 'warning', 'icone' => 'exclamation-triangle', 'recomendacao' => 'Análise adicional necessária'];
    if ($score >= 40) return ['nivel' => 'Alto', 'cor' => 'danger', 'icone' => 'times-circle', 'recomendacao' => 'Aprovação não recomendada'];
    return ['nivel' => 'Muito Alto', 'cor' => 'dark', 'icone' => 'skull-crossbones', 'recomendacao' => 'Rejeição recomendada'];
}

$score = calcularScoreCredito($requisicao);
$risco = determinarRisco($score);

// Calcular prestação estimada (juros simples aproximado)
$taxa_juros_anual = 0.12; // 12% ao ano
$taxa_juros_mensal = $taxa_juros_anual / 12;
$prestacao_estimada = $requisicao['valor_solicitado'] * ($taxa_juros_mensal * pow(1 + $taxa_juros_mensal, $requisicao['prazo'])) / (pow(1 + $taxa_juros_mensal, $requisicao['prazo']) - 1);

$status_colors = [
    'pendente' => 'warning',
    'em_analise' => 'info',
    'aprovado' => 'success',
    'reprovado' => 'danger',
    'cancelado' => 'secondary'
];
?>
?>

<div class='row'>
    <!-- INFORMAÇÕES DA REQUISIÇÃO -->
    <div class='col-md-8'>
        <div class='card card-primary'>
            <div class='card-header'>
                <h5 class='card-title mb-0'>
                    <i class='fas fa-file-invoice-dollar mr-1'></i>
                    Requisição #<?php echo $requisicao['id']; ?>
                </h5>
            </div>
            <div class='card-body'>
                <div class='row'>
                    <div class='col-md-6'>
                        <h6><i class='fas fa-calendar mr-1'></i> Data da Requisição</h6>
                        <p><?php echo date('d/m/Y \à\s H:i', strtotime($requisicao['data_hora'])); ?></p>

                        <h6><i class='fas fa-tag mr-1'></i> Tipo de Crédito</h6>
                        <p><?php echo htmlspecialchars($requisicao['tipo'] ?? 'Não informado'); ?></p>

                        <h6><i class='fas fa-users mr-1'></i> Categoria</h6>
                        <p><?php echo $requisicao['categoria'] ?? 'Não informado'; ?></p>
                    </div>
                    <div class='col-md-6'>
                        <h6><i class='fas fa-money-bill-wave mr-1'></i> Valor Solicitado</h6>
                        <p class='h4 text-primary'>R$ <?php echo number_format($requisicao['valor_solicitado'], 2, ',', '.'); ?></p>

                        <h6><i class='fas fa-clock mr-1'></i> Prazo</h6>
                        <p><?php echo $requisicao['prazo']; ?> meses</p>

                        <h6><i class='fas fa-calculator mr-1'></i> Prestação Estimada</h6>
                        <p class='text-info'>R$ <?php echo number_format($prestacao_estimada, 2, ',', '.'); ?>/mês</p>
                    </div>
                </div>

                <hr>

                <div class='row'>
                    <div class='col-md-6'>
                        <h6><i class='fas fa-phone mr-1'></i> Horário de Contato</h6>
                        <p><?php echo $requisicao['horario_contato'] ?? 'Não informado'; ?></p>

                        <h6><i class='fas fa-search mr-1'></i> Já fez cotação?</h6>
                        <p><?php echo $requisicao['cotacao']; ?></p>
                    </div>
                    <div class='col-md-6'>
                        <h6><i class='fas fa-handshake mr-1'></i> Interesse em contratar?</h6>
                        <p><?php echo $requisicao['contratacao']; ?></p>

                        <?php if (!empty($requisicao['outros_info'])): ?>
                            <h6><i class='fas fa-info-circle mr-1'></i> Informações Adicionais</h6>
                            <p><?php echo htmlspecialchars($requisicao['outros_info']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (!empty($requisicao['observacoes'])): ?>
                    <hr>
                    <h6><i class='fas fa-comment mr-1'></i> Observações do Analista</h6>
                    <p><?php echo htmlspecialchars($requisicao['observacoes']); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- SCORE DE CRÉDITO E STATUS -->
    <div class='col-md-4'>
        <!-- SCORE DE CRÉDITO -->
        <div class='card card-<?php echo $risco['cor']; ?>'>
            <div class='card-header'>
                <h5 class='card-title mb-0'>
                    <i class='fas fa-chart-line mr-1'></i>
                    Score de Crédito
                </h5>
            </div>
            <div class='card-body text-center'>
                <div class='h1 mb-2'><?php echo $score; ?>/100</div>
                <div class='mb-3'>
                    <span class='badge badge-<?php echo $risco['cor']; ?> badge-lg'>
                        <i class='fas fa-<?php echo $risco['icone']; ?> mr-1'></i>
                        <?php echo $risco['nivel']; ?>
                    </span>
                </div>
                <p class='mb-0'><?php echo $risco['recomendacao']; ?></p>
            </div>
        </div>

        <!-- STATUS ATUAL -->
        <div class='card'>
            <div class='card-header'>
                <h5 class='card-title mb-0'>
                    <i class='fas fa-tasks mr-1'></i>
                    Status Atual
                </h5>
            </div>
            <div class='card-body text-center'>
                <span class='badge badge-<?php echo $status_colors[$requisicao['status']] ?? 'secondary'; ?> badge-lg mb-2'>
                    <?php echo ucfirst(str_replace('_', ' ', $requisicao['status'])); ?>
                </span>

                <?php if ($requisicao['analista_nome']): ?>
                    <p class='mb-1'><small>Analisado por:</small></p>
                    <p class='mb-0'><strong><?php echo htmlspecialchars($requisicao['analista_nome']); ?></strong></p>
                <?php endif; ?>

                <?php if ($requisicao['data_analise']): ?>
                    <p class='mb-0'><small><?php echo date('d/m/Y H:i', strtotime($requisicao['data_analise'])); ?></small></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- DADOS DO CLIENTE -->
<div class='row mt-3'>
    <div class='col-12'>
        <div class='card card-info'>
            <div class='card-header'>
                <h5 class='card-title mb-0'>
                    <i class='fas fa-user mr-1'></i>
                    Dados do Cliente
                </h5>
            </div>
            <div class='card-body'>
                <div class='row'>
                    <div class='col-md-6'>
                        <h6><i class='fas fa-id-card mr-1'></i> Informações Pessoais</h6>
                        <table class='table table-sm table-borderless'>
                            <tr>
                                <td width='120'><strong>Nome:</strong></td>
                                <td><?php echo htmlspecialchars($requisicao['cliente_nome']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>CPF:</strong></td>
                                <td><?php echo htmlspecialchars($requisicao['cpf']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>RG:</strong></td>
                                <td><?php echo htmlspecialchars($requisicao['rg']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Data Nasc.:</strong></td>
                                <td><?php echo date('d/m/Y', strtotime($requisicao['data_nascimento'])); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Cadastro:</strong></td>
                                <td><?php echo date('d/m/Y', strtotime($requisicao['cliente_data_cadastro'])); ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class='col-md-6'>
                        <h6><i class='fas fa-address-book mr-1'></i> Contato</h6>
                        <table class='table table-sm table-borderless'>
                            <tr>
                                <td width='120'><strong>Email:</strong></td>
                                <td><?php echo htmlspecialchars($requisicao['cliente_email']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Telefone:</strong></td>
                                <td><?php echo htmlspecialchars($requisicao['cliente_telefone']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Endereço:</strong></td>
                                <td><?php echo htmlspecialchars($requisicao['endereco']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Bairro:</strong></td>
                                <td><?php echo htmlspecialchars($requisicao['bairro']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Cidade/UF:</strong></td>
                                <td><?php echo htmlspecialchars($requisicao['cidade'] . '/' . $requisicao['estado']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>CEP:</strong></td>
                                <td><?php echo htmlspecialchars($requisicao['cep']); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ANÁLISE DETALHADA DO SCORE -->
<div class='row mt-3'>
    <div class='col-12'>
        <div class='card card-warning'>
            <div class='card-header'>
                <h5 class='card-title mb-0'>
                    <i class='fas fa-balance-scale mr-1'></i>
                    Análise Detalhada do Score
                </h5>
            </div>
            <div class='card-body'>
                <div class='row'>
                    <div class='col-md-3'>
                        <div class='text-center'>
                            <div class='h5 mb-1'>Categoria</div>
                            <div class='h3 mb-1'>
                                <?php
                                $categoria_score = 0;
                                if ($requisicao['categoria'] === 'Servidor Público') $categoria_score = 30;
                                elseif ($requisicao['categoria'] === 'Aposentado') $categoria_score = 25;
                                elseif ($requisicao['categoria'] === 'Pensionista') $categoria_score = 20;
                                echo $categoria_score;
                                ?>/100
                            </div>
                            <small><?php echo $requisicao['categoria']; ?></small>
                        </div>
                    </div>
                    <div class='col-md-3'>
                        <div class='text-center'>
                            <div class='h5 mb-1'>Valor</div>
                            <div class='h3 mb-1'>
                                <?php
                                $valor_score = 0;
                                $valor = $requisicao['valor_solicitado'];
                                if ($valor <= 5000) $valor_score = 25;
                                elseif ($valor <= 10000) $valor_score = 20;
                                elseif ($valor <= 20000) $valor_score = 15;
                                else $valor_score = 10;
                                echo $valor_score;
                                ?>/100
                            </div>
                            <small>R$ <?php echo number_format($valor, 0, ',', '.'); ?></small>
                        </div>
                    </div>
                    <div class='col-md-3'>
                        <div class='text-center'>
                            <div class='h5 mb-1'>Prazo</div>
                            <div class='h3 mb-1'>
                                <?php
                                $prazo_score = 0;
                                $prazo = $requisicao['prazo'];
                                if ($prazo >= 36) $prazo_score = 20;
                                elseif ($prazo >= 24) $prazo_score = 15;
                                elseif ($prazo >= 12) $prazo_score = 10;
                                else $prazo_score = 5;
                                echo $prazo_score;
                                ?>/100
                            </div>
                            <small><?php echo $prazo; ?> meses</small>
                        </div>
                    </div>
                    <div class='col-md-3'>
                        <div class='text-center'>
                            <div class='h5 mb-1'>Engajamento</div>
                            <div class='h3 mb-1'>
                                <?php
                                $engajamento_score = 0;
                                if ($requisicao['cotacao'] === 'Sim') $engajamento_score += 15;
                                if ($requisicao['contratacao'] === 'Sim') $engajamento_score += 10;
                                echo $engajamento_score;
                                ?>/100
                            </div>
                            <small><?php echo $requisicao['cotacao'] === 'Sim' ? 'Cotou' : 'Não cotou'; ?> | <?php echo $requisicao['contratacao'] === 'Sim' ? 'Interessado' : 'Não interessado'; ?></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
