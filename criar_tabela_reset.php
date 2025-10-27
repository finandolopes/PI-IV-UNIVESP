<?php
include_once('php/conexao.php');

// SQL para criar tabela de reset de senha
$sql = "CREATE TABLE IF NOT EXISTS `reset_senha` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `data_solicitacao` timestamp DEFAULT CURRENT_TIMESTAMP,
  `status` enum('pendente','processado','expirado') DEFAULT 'pendente',
  `nova_senha` varchar(255) DEFAULT NULL,
  `data_processamento` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_usuario` (`id_usuario`),
  KEY `token` (`token`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;";

if (mysqli_query($con, $sql)) {
    echo "Tabela reset_senha criada com sucesso!";
} else {
    echo "Erro ao criar tabela: " . mysqli_error($con);
}

mysqli_close($con);
?>
