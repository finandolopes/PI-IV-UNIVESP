<?php
session_start();
require_once '../php/verifica_login.php';
require_once '../php/conexao.php';

// Verificar se usuário está logado e é admin
verificarLoginAdmin();

include 'navbar.php';
include 'sidebar.php';

// Processar geração de nova senha
if (isset($_POST['gerar_senha'])) {
    $id_reset = mysqli_real_escape_string($con, $_POST['id_reset']);
    $nova_senha = bin2hex(random_bytes(4)); // Gera senha de 8 caracteres
    $senha_md5 = md5($nova_senha);

    // Buscar dados da solicitação
    $query_reset = "SELECT rs.*, u.usuario FROM reset_senha rs
                   JOIN usuarios u ON rs.id_usuario = u.id
                   WHERE rs.id = '$id_reset'";
    $result_reset = mysqli_query($con, $query_reset);

    if (mysqli_num_rows($result_reset) > 0) {
        $reset_data = mysqli_fetch_assoc($result_reset);

        // Atualizar senha do usuário
        $update_user = "UPDATE usuarios SET senha = '$senha_md5' WHERE id = '{$reset_data['id_usuario']}'";
        mysqli_query($con, $update_user);

        // Atualizar status da solicitação
        $update_reset = "UPDATE reset_senha SET
                        status = 'processado',
                        nova_senha = '$nova_senha',
                        data_processamento = NOW()
                        WHERE id = '$id_reset'";
        mysqli_query($con, $update_reset);

        $success_message = "Nova senha gerada com sucesso para o usuário {$reset_data['usuario']}: <strong>$nova_senha</strong>";
    }
}

// Buscar solicitações pendentes
$query = "SELECT rs.*, u.nome, u.usuario, u.email
          FROM reset_senha rs
          JOIN usuarios u ON rs.id_usuario = u.id
          WHERE rs.status = 'pendente'
          ORDER BY rs.data_solicitacao DESC";
$result = mysqli_query($con, $query);
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Gerenciamento de Reset de Senha</h2>
            <span class="badge bg-primary"><?php echo mysqli_num_rows($result); ?> solicitações pendentes</span>
        </div>

        <?php if (isset($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $success_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h5>Solicitações Pendentes</h5>
            </div>
            <div class="card-body">
                <?php if (mysqli_num_rows($result) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Data Solicitação</th>
                                <th>Usuário</th>
                                <th>Nome</th>
                                <th>Email</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?php echo date('d/m/Y H:i', strtotime($row['data_solicitacao'])); ?></td>
                                <td><?php echo htmlspecialchars($row['usuario']); ?></td>
                                <td><?php echo htmlspecialchars($row['nome']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="id_reset" value="<?php echo $row['id']; ?>">
                                        <button type="submit" name="gerar_senha" class="btn btn-success btn-sm"
                                                onclick="return confirm('Gerar nova senha para este usuário?')">
                                            <i class="fas fa-key"></i> Gerar Senha
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
                    <h5 class="mt-3">Nenhuma solicitação pendente</h5>
                    <p class="text-muted">Todas as solicitações foram processadas.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Histórico de resets processados -->
        <div class="card mt-4">
            <div class="card-header">
                <h5>Histórico de Resets Processados</h5>
            </div>
            <div class="card-body">
                <?php
                $query_historico = "SELECT rs.*, u.nome, u.usuario, u.email
                                   FROM reset_senha rs
                                   JOIN usuarios u ON rs.id_usuario = u.id
                                   WHERE rs.status = 'processado'
                                   ORDER BY rs.data_processamento DESC
                                   LIMIT 10";
                $result_historico = mysqli_query($con, $query_historico);
                ?>

                <?php if (mysqli_num_rows($result_historico) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Data Processamento</th>
                                <th>Usuário</th>
                                <th>Nome</th>
                                <th>Nova Senha</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($result_historico)): ?>
                            <tr>
                                <td><?php echo date('d/m/Y H:i', strtotime($row['data_processamento'])); ?></td>
                                <td><?php echo htmlspecialchars($row['usuario']); ?></td>
                                <td><?php echo htmlspecialchars($row['nome']); ?></td>
                                <td>
                                    <code><?php echo htmlspecialchars($row['nova_senha']); ?></code>
                                    <small class="text-muted">(copie e entregue ao usuário)</small>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-3">
                    <p class="text-muted">Nenhum reset processado ainda.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
