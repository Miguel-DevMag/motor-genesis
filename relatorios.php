<?php
include("seguranca.php");
include("conexao.php");

$msg = "";
$erro = "";

// Criar tabela Relatorios se não existir (para armazenar relatórios gerados)
$sql_tabela = "CREATE TABLE IF NOT EXISTS Relatorios (
    id_relatorio INT PRIMARY KEY AUTO_INCREMENT,
    tipo VARCHAR(50),
    titulo VARCHAR(150),
    descricao TEXT,
    data_geracao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    usuario VARCHAR(150)
)";
$conn->query($sql_tabela);

// Estatísticas do Estoque
$estoque_stats = $conn->query("
    SELECT 
        COUNT(*) as total_pecas,
        SUM(quantidade_estoque) as quantidade_total,
        SUM(quantidade_estoque * custo_unitario) as valor_total_estoque
    FROM Pecas
");
$estoque_data = $estoque_stats->fetch_assoc();

// Estatísticas de Produção
$producao_stats = $conn->query("
    SELECT 
        status,
        COUNT(*) as total_ops,
        SUM(quantidade) as quantidade_total
    FROM OrdensProducao
    GROUP BY status
");

// Orçamentos (se existir tabela)
$orcamentos_stats = @$conn->query("
    SELECT 
        status,
        COUNT(*) as total,
        SUM(valor_total) as valor_total
    FROM Orcamentos
    GROUP BY status
");

// Ultimas movimentações de estoque
$ultimos_movimentos = $conn->query("
    SELECT id_peca, nome_peca, quantidade_estoque, codigo_peca
    FROM Pecas
    ORDER BY id_peca DESC
    LIMIT 10
");
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Relatórios - Motor Genesis</title>
<link rel="stylesheet" href="css/tema.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<div class="sidebar">
    <div class="logo">
        <img src="img/logo.png" alt="Motor Genesis">
        <h3>Motor Genesis</h3>
    </div>
    <div class="menu">
        <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="estoque.php"><i class="fas fa-boxes"></i> Estoque</a>
        <a href="producao.php"><i class="fas fa-industry"></i> Produção</a>
        <a href="logistica.php"><i class="fas fa-truck"></i> Logística</a>
        <a href="orcamentos.php"><i class="fas fa-file-invoice"></i> Orçamentos</a>
        <a href="relatorios.php" class="active"><i class="fas fa-chart-bar"></i> Relatórios</a>
        <a href="usuarios.php"><i class="fas fa-users"></i> Usuários</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a>
    </div>
</div>

<div class="main">
    <div class="topbar">
        <div></div>
        <div class="user">
            <i class="fas fa-user-circle"></i> <?php echo $_SESSION["login"]; ?> |
            <span class="status-online"><i class="fas fa-check-circle"></i> Online</span>
        </div>
    </div>

    <h1><i class="fas fa-chart-bar"></i> Relatórios</h1>

    <!-- Cards de Estatísticas Gerais -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin: 30px 0;">
        <div style="background: #262626; border-left: 4px solid #dc143c; padding: 20px; border-radius: 8px;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <p style="color: #999; margin: 0; font-size: 0.9em;">Total de Peças</p>
                    <h3 style="color: #dc143c; margin: 5px 0; font-size: 2em;"><?php echo $estoque_data['total_pecas'] ?? 0; ?></h3>
                </div>
                <i class="fas fa-boxes" style="font-size: 3em; color: #dc143c; opacity: 0.3;"></i>
            </div>
        </div>

        <div style="background: #262626; border-left: 4px solid #00c853; padding: 20px; border-radius: 8px;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <p style="color: #999; margin: 0; font-size: 0.9em;">Quantidade em Estoque</p>
                    <h3 style="color: #00c853; margin: 5px 0; font-size: 2em;"><?php echo $estoque_data['quantidade_total'] ?? 0; ?></h3>
                </div>
                <i class="fas fa-cube" style="font-size: 3em; color: #00c853; opacity: 0.3;"></i>
            </div>
        </div>

        <div style="background: #262626; border-left: 4px solid #ff9800; padding: 20px; border-radius: 8px;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <p style="color: #999; margin: 0; font-size: 0.9em;">Valor Total Estoque</p>
                    <h3 style="color: #ff9800; margin: 5px 0; font-size: 1.7em;">R$ <?php echo number_format($estoque_data['valor_total_estoque'] ?? 0, 2, ',', '.'); ?></h3>
                </div>
                <i class="fas fa-chart-line" style="font-size: 3em; color: #ff9800; opacity: 0.3;"></i>
            </div>
        </div>
    </div>

    <h2><i class="fas fa-industry"></i> Estatísticas de Produção</h2>
    <table>
        <thead>
            <tr>
                <th>Status</th>
                <th>Total de OPs</th>
                <th>Quantidade Total</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($producao_stats && $producao_stats->num_rows > 0): ?>
                <?php while ($prod = $producao_stats->fetch_assoc()): ?>
                <tr>
                    <td><span class="status-badge"><?php echo htmlspecialchars($prod['status']); ?></span></td>
                    <td><?php echo $prod['total_ops']; ?></td>
                    <td><?php echo $prod['quantidade_total']; ?> unidades</td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3" style="text-align: center; padding: 20px; color: #999;">Nenhuma OP cadastrada</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if ($orcamentos_stats && $orcamentos_stats->num_rows > 0): ?>
    <h2><i class="fas fa-file-invoice"></i> Estatísticas de Orçamentos</h2>
    <table>
        <thead>
            <tr>
                <th>Status</th>
                <th>Total</th>
                <th>Valor Total</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($orc = $orcamentos_stats->fetch_assoc()): ?>
            <tr>
                <td><span class="status-badge"><?php echo htmlspecialchars($orc['status']); ?></span></td>
                <td><?php echo $orc['total']; ?></td>
                <td>R$ <?php echo number_format($orc['valor_total'] ?? 0, 2, ',', '.'); ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <h2><i class="fas fa-boxes"></i> Últimas Peças Adicionadas</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Código</th>
                <th>Quantidade</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($peca = $ultimos_movimentos->fetch_assoc()): ?>
            <tr>
                <td><?php echo $peca['id_peca']; ?></td>
                <td><?php echo htmlspecialchars($peca['nome_peca']); ?></td>
                <td><?php echo htmlspecialchars($peca['codigo_peca']); ?></td>
                <td><?php echo $peca['quantidade_estoque']; ?> unidades</td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <div style="margin-top: 30px; padding: 20px; background: #262626; border-radius: 8px; text-align: center; color: #999;">
        <i class="fas fa-info-circle"></i> Relatórios atualizados em tempo real
        <br><small>Última atualização: <?php echo date('d/m/Y H:i:s'); ?></small>
    </div>
</div>

</body>
</html>
