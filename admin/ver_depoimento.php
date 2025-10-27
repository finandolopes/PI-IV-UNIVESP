<?php
session_start();
include_once('../php/conexao.php');

// Verifica se o usuário está logado
if (!isset($_SESSION['username'])) {
    die('<div class="alert alert-danger">Sessão expirada. Faça login novamente.</div>');
}

if (!isset($_GET['id'])) {
    die('<div class="alert alert-danger">ID do depoimento não informado.</div>');
}

$id = (int)$_GET['id'];

// Buscar depoimento completo
$query = "
    SELECT
        d.*,
        d.nome_cliente as cliente_nome,
        '' as cliente_email,
        '' as cliente_telefone
    FROM depoimentos d
    WHERE d.id_depoimento = ?
";

$stmt = $conexao->prepare($query);
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die('<div class="alert alert-danger">Depoimento não encontrado.</div>');
}

$depoimento = $result->fetch_assoc();
$stmt->close();
?>

<div class='row'>
    <!-- INFORMAÇÕES DO DEPOIMENTO -->
    <div class='col-md-8'>
        <div class='card card-primary'>
            <div class='card-header'>
                <h5 class='card-title mb-0'>
                    <i class='fas fa-quote-left mr-1'></i>
                    Depoimento #<?php echo $depoimento['id_depoimento']; ?>
                </h5>
            </div>
            <div class='card-body'>
                <div class='mb-3'>
                    <h6><i class='fas fa-calendar mr-1'></i> Data do Envio</h6>
                    <p><?php echo date('d/m/Y \à\s H:i', strtotime($depoimento['data_envio'])); ?></p>
                </div>

                <div class='mb-3'>
                    <h6><i class='fas fa-star mr-1'></i> Avaliação</h6>
                    <div class='rating-stars'>
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class='fas fa-star <?php echo $i <= $depoimento['avaliacao'] ? 'text-warning' : 'text-muted'; ?>'></i>
                        <?php endfor; ?>
                        <span class='ml-2'>(<?php echo $depoimento['avaliacao']; ?>/5)</span>
                    </div>
                </div>

                <div class='mb-3'>
                    <h6><i class='fas fa-comment mr-1'></i> Depoimento</h6>
                    <div class='bg-light p-3 rounded'>
                        <blockquote class='blockquote mb-0'>
                            <p class='mb-0'><?php echo nl2br(htmlspecialchars($depoimento['depoimento'])); ?></p>
                        </blockquote>
                    </div>
                </div>

                <?php if (!empty($depoimento['motivo_reprovacao'])): ?>
                    <div class='mb-3'>
                        <h6><i class='fas fa-exclamation-triangle mr-1 text-danger'></i> Motivo da Reprovação</h6>
                        <div class='alert alert-danger'>
                            <?php echo htmlspecialchars($depoimento['motivo_reprovacao']); ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- INFORMAÇÕES DO CLIENTE E STATUS -->
    <div class='col-md-4'>
        <!-- DADOS DO CLIENTE -->
        <div class='card card-info'>
            <div class='card-header'>
                <h5 class='card-title mb-0'>
                    <i class='fas fa-user mr-1'></i>
                    Cliente
                </h5>
            </div>
            <div class='card-body'>
                <h6><?php echo htmlspecialchars($depoimento['cliente_nome']); ?></h6>
                <p class='mb-1'><i class='fas fa-envelope mr-1'></i> <?php echo htmlspecialchars($depoimento['cliente_email']); ?></p>
                <p class='mb-0'><i class='fas fa-phone mr-1'></i> <?php echo htmlspecialchars($depoimento['cliente_telefone']); ?></p>
            </div>
        </div>

        <!-- STATUS ATUAL -->
        <div class='card'>
            <div class='card-header'>
                <h5 class='card-title mb-0'>
                    <i class='fas fa-tasks mr-1'></i>
                    Status
                </h5>
            </div>
            <div class='card-body text-center'>
                <?php
                $status_class = '';
                $status_icon = '';
                $status_text = '';
                switch ($depoimento['status']) {
                    case 'aprovado':
                        $status_class = 'badge-success';
                        $status_icon = 'check-circle';
                        $status_text = 'Aprovado';
                        break;
                    case 'pendente':
                        $status_class = 'badge-warning';
                        $status_icon = 'clock';
                        $status_text = 'Pendente';
                        break;
                    case 'reprovado':
                        $status_class = 'badge-danger';
                        $status_icon = 'times-circle';
                        $status_text = 'Reprovado';
                        break;
                }
                ?>
                <span class='badge <?php echo $status_class; ?> badge-lg mb-2'>
                    <i class='fas fa-<?php echo $status_icon; ?> mr-1'></i>
                    <?php echo $status_text; ?>
                </span>

                <?php if ($depoimento['data_moderacao']): ?>
                    <p class='mb-0'><small>Moderado em:<br><?php echo date('d/m/Y H:i', strtotime($depoimento['data_moderacao'])); ?></small></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- AÇÕES RÁPIDAS -->
        <?php if ($depoimento['status'] === 'pendente'): ?>
            <div class='card'>
                <div class='card-header'>
                    <h5 class='card-title mb-0'>
                        <i class='fas fa-bolt mr-1'></i>
                        Ações
                    </h5>
                </div>
                <div class='card-body'>
                    <button type='button' class='btn btn-success btn-block mb-2' onclick='aprovarDepoimentoModal(<?php echo $depoimento['id_depoimento']; ?>)'>
                        <i class='fas fa-check mr-1'></i> Aprovar Depoimento
                    </button>
                    <button type='button' class='btn btn-danger btn-block' onclick='reprovarDepoimentoModal(<?php echo $depoimento['id_depoimento']; ?>)'>
                        <i class='fas fa-times mr-1'></i> Reprovar Depoimento
                    </button>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>