<?php
include("proteger.php");
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
<html>
<head>
    <title>Motor Genesis - Dashboard</title>
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>

<div class="sidebar">
    <div class="logo">🚀 Motor Genesis</div>

    <div class="menu">
        <a href="#">Dashboard</a>
        <a href="#">Estoque</a>
        <a href="#">Produção</a>
        <a href="#">Logística</a>
        <a href="#">Orçamentos</a>
        <a href="#">Relatórios</a>
        <a href="#">Usuários</a>
        <a href="logout.php">Sair</a>
    </div>
</div>

<div class="main">

    <div class="topbar">
        <input type="text" class="search" placeholder="Buscar peças, OPs, envios...">
        <div class="user">
            <?php echo $_SESSION["login"]; ?> |
            <span class="status-online">● Sistema Online</span>
        </div>
    </div>

    <h1>Dashboard Executivo</h1>
    <br>

    <div class="cards">

        <div class="card green">
            <h3>VALOR EM ESTOQUE</h3>
            <div class="value">R$ <?php echo number_format($estoque,2,",","."); ?></div>
        </div>

        <div class="card red">
            <h3>PEÇAS CRÍTICAS</h3>
            <div class="value"><?php echo $criticas; ?></div>
        </div>

        <div class="card blue">
            <h3>FUNCIONÁRIOS</h3>
            <div class="value"><?php echo $funcionarios; ?></div>
        </div>

        <div class="card yellow">
            <h3>USUÁRIOS ATIVOS</h3>
            <div class="value"><?php echo $usuarios; ?></div>
        </div>

    </div>

</div>

</body>
</html>