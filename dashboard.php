<?php
// protegemos a rota usando o arquivo de segurança
include("seguranca.php");
include("conexao.php");

/* DADOS DINÂMICOS DO BANCO */

// Valor total estoque
$sql = $conn->query("SELECT SUM(quantidade_estoque * custo_unitario) AS total FROM Pecas");
$estoque = $sql->fetch_assoc()['total'] ?? 0;

// Peças críticas (menos que 10)
$sql2 = $conn->query("SELECT COUNT(*) AS criticas FROM Pecas WHERE quantidade_estoque < 10");
$criticas = $sql2->fetch_assoc()['criticas'] ?? 0;

// Funcionários
$sql3 = $conn->query("SELECT COUNT(*) AS total_func FROM Funcionarios");
$funcionarios = $sql3->fetch_assoc()['total_func'] ?? 0;

// Usuários ativos
$sql4 = $conn->query("SELECT COUNT(*) AS ativos FROM Usuarios WHERE ativo=1");
$usuarios = $sql4->fetch_assoc()['ativos'] ?? 0;

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Motor Genesis - Dashboard</title>
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
        <a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="estoque.php"><i class="fas fa-boxes"></i> Estoque</a>
        <a href="producao.php"><i class="fas fa-industry"></i> Produção</a>
        <a href="logistica.php"><i class="fas fa-truck"></i> Logística</a>
        <a href="orcamentos.php"><i class="fas fa-file-invoice"></i> Orçamentos</a>
        <a href="relatorios.php"><i class="fas fa-chart-bar"></i> Relatórios</a>
        <a href="usuarios.php"><i class="fas fa-users"></i> Usuários</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a>
    </div>
</div>

<div class="main">

    <div class="topbar">
        <input type="text" class="search" placeholder="Buscar peças, OPs, envios...">
        <div class="user">
            <i class="fas fa-user-circle"></i> <?php echo $_SESSION["login"]; ?> |
            <span class="status-online"><i class="fas fa-check-circle"></i> Sistema Online</span>
        </div>
    </div>

    <h1><i class="fas fa-chart-line"></i> Dashboard Executivo</h1>
    <h4>Visão geral do seu negócio</h4>

    <div class="cards">

        <div class="card green">
            <h3><i class="fas fa-money-bill-wave"></i> VALOR EM ESTOQUE</h3>
            <div class="value">R$ <?php echo number_format($estoque,2,",","."); ?></div>
        </div>

        <div class="card red">
            <h3><i class="fas fa-exclamation-triangle"></i> PEÇAS CRÍTICAS</h3>
            <div class="value"><?php echo $criticas; ?></div>
        </div>

        <div class="card blue">
            <h3><i class="fas fa-users"></i> FUNCIONÁRIOS</h3>
            <div class="value"><?php echo $funcionarios; ?></div>
        </div>

        <div class="card yellow">
            <h3><i class="fas fa-user-check"></i> USUÁRIOS ATIVOS</h3>
            <div class="value"><?php echo $usuarios; ?></div>
        </div>

    </div>

</div>

</body>
</html>
