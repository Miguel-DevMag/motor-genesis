<?php
include("seguranca.php");
include("conexao.php");

$msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["acao"])) {
    if ($_POST["acao"] === "cadastrar_transportadora") {
        $nome = trim($_POST["nome"] ?? "");
        $cnpj = trim($_POST["cnpj"] ?? "");
        $tipo = trim($_POST["tipo"] ?? "");
        $status = trim($_POST["status"] ?? "ATIVO");
        if ($nome !== "") {
            $sql = "INSERT INTO Transportadoras (nome, cnpj, tipo, status) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $nome, $cnpj, $tipo, $status);
            if ($stmt->execute()) { $msg = "Transportadora cadastrada."; } else { $msg = "Erro ao cadastrar transportadora."; }
        } else {
            $msg = "Informe o nome da transportadora.";
        }
    }
    if ($_POST["acao"] === "excluir_transportadora") {
        $id = intval($_POST["id_transportadora"] ?? 0);
        if ($id > 0) {
            $stmt = $conn->prepare("DELETE FROM Transportadoras WHERE id_transportadora=?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) { $msg = "Transportadora excluída."; } else { $msg = "Não foi possível excluir. Existem envios vinculados."; }
        }
    }
    if ($_POST["acao"] === "cadastrar_envio") {
        $codigo_moto = trim($_POST["codigo_moto"] ?? "");
        $tipo = trim($_POST["tipo"] ?? "");
        $id_transp = intval($_POST["id_transportadora"] ?? 0);
        $destino = trim($_POST["destino"] ?? "");
        $previsao = $_POST["previsao_entrega"] ?? null;
        if ($codigo_moto !== "" && $tipo !== "" && $id_transp > 0) {
            $sql = "INSERT INTO Envios (codigo_moto, tipo, id_transportadora, destino, previsao_entrega) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssiss", $codigo_moto, $tipo, $id_transp, $destino, $previsao);
            if ($stmt->execute()) { $msg = "Envio criado."; } else { $msg = "Erro ao criar envio."; }
        } else {
            $msg = "Preencha Código, Tipo e Transportadora.";
        }
    }
    if ($_POST["acao"] === "iniciar_envio") {
        $id = intval($_POST["id_envio"] ?? 0);
        if ($id > 0) {
            $stmt = $conn->prepare("UPDATE Envios SET status='A CAMINHO', data_inicio=NOW() WHERE id_envio=?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
        }
    }
    if ($_POST["acao"] === "finalizar_envio") {
        $id = intval($_POST["id_envio"] ?? 0);
        if ($id > 0) {
            $stmt = $conn->prepare("UPDATE Envios SET status='ENTREGUE', data_fim=NOW() WHERE id_envio=?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
        }
    }
    if ($_POST["acao"] === "marcar_atrasado") {
        $id = intval($_POST["id_envio"] ?? 0);
        if ($id > 0) {
            $stmt = $conn->prepare("UPDATE Envios SET status='ATRASADO' WHERE id_envio=?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
        }
    }
}

$transportadoras = $conn->query("
    SELECT t.id_transportadora, t.nome, t.cnpj, t.tipo, t.status, t.criado_em,
           COUNT(e.id_envio) AS total_envios,
           SUM(CASE WHEN e.status='ENTREGUE' THEN 1 ELSE 0 END) AS entregues
    FROM Transportadoras t
    LEFT JOIN Envios e ON e.id_transportadora = t.id_transportadora
    GROUP BY t.id_transportadora, t.nome, t.cnpj, t.tipo, t.status, t.criado_em
    ORDER BY t.nome ASC
");

$transportadorasSelect = $conn->query("SELECT id_transportadora, nome FROM Transportadoras WHERE status='ATIVO' ORDER BY nome ASC");

$emTransporte = $conn->query("
    SELECT e.id_envio, e.codigo_moto, e.tipo, e.destino, e.previsao_entrega, e.status, t.nome AS transportadora
    FROM Envios e
    JOIN Transportadoras t ON t.id_transportadora = e.id_transportadora
    WHERE e.status IN ('PENDENTE','A CAMINHO','ATRASADO')
    ORDER BY e.data_criacao DESC
");

$entregues = $conn->query("
    SELECT e.id_envio, e.codigo_moto, e.tipo, e.destino, e.previsao_entrega, e.status, t.nome AS transportadora, e.data_inicio, e.data_fim
    FROM Envios e
    JOIN Transportadoras t ON t.id_transportadora = e.id_transportadora
    WHERE e.status = 'ENTREGUE'
    ORDER BY e.data_fim DESC
");
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Logística</title>
<link rel="stylesheet" href="css/logistica.css">
</head>
<body>

<div class="sidebar">
    <div class="logo">
        <img src="img/logo.png" alt="logo">
    </div>

    <div class="menu">
        <a href="dashboard.php">Dashboard</a>
        <a href="estoque.php">Estoque</a>
        <a href="producao.php">Produção</a>
        <a href="#">Logística</a>
        <a href="#">Orçamentos</a>
        <a href="#">Relatórios</a>
        <a href="#">Usuários</a>
        <a href="logout.php">Sair</a>
    </div>
</div>

<div class="main">

<h1>Logística</h1>

<?php if ($msg !== "") { ?>
    <div><?php echo htmlspecialchars($msg); ?></div>
<?php } ?>

<form method="GET" style="display:inline">
    <button type="submit" name="novo" value="transportadora">+ Cadastrar Transportadora</button>
    <button type="submit" name="novo" value="envio">+ Novo Envio</button>
    <?php if (isset($_GET["novo"])) { ?>
        <a href="logistica.php">Ocultar</a>
    <?php } ?>
</form>

<?php if (isset($_GET["novo"]) && $_GET["novo"] === "transportadora") { ?>
    <h2>Cadastrar Transportadora</h2>
    <form method="POST">
        <input type="hidden" name="acao" value="cadastrar_transportadora">
        <input type="text" name="nome" placeholder="Nome" required>
        <input type="text" name="cnpj" placeholder="CNPJ">
        <input type="text" name="tipo" placeholder="Tipo (rodoviario, aereo, etc)">
        <label>Status</label>
        <select name="status">
            <option value="ATIVO" selected>ATIVO</option>
            <option value="INATIVO">INATIVO</option>
        </select>
        <button type="submit">Salvar Transportadora</button>
    </form>
<?php } ?>

<?php if (isset($_GET["novo"]) && $_GET["novo"] === "envio") { ?>
    <h2>Novo Envio</h2>
    <form method="POST">
        <input type="hidden" name="acao" value="cadastrar_envio">
        <label>Código da Moto</label>
        <input type="text" name="codigo_moto" placeholder="Código da moto" required>
        <label>Tipo</label>
        <select name="tipo" required>
            <option value="MOTO">MOTO</option>
            <option value="PECAS">PECAS</option>
        </select>
        <label>Transportadora</label>
        <select name="id_transportadora" required>
            <option value="">Selecione</option>
            <?php while($t = $transportadorasSelect->fetch_assoc()){ ?>
                <option value="<?php echo $t["id_transportadora"]; ?>"><?php echo htmlspecialchars($t["nome"]); ?></option>
            <?php } ?>
        </select>
        <label>Destino</label>
        <input type="text" name="destino" placeholder="Cidade/UF, endereço">
        <label>Previsão de Entrega</label>
        <input type="date" name="previsao_entrega">
        <button type="submit">Criar Envio</button>
    </form>
<?php } ?>

<h2>Transportadoras</h2>
<table border="1" cellpadding="6" cellspacing="0">
    <tr>
        <th>ID</th>
        <th>Nome</th>
        <th>CNPJ</th>
        <th>Tipo</th>
        <th>Criado em</th>
        <th>Entregas</th>
        <th>Desempenho (%)</th>
        <th>Status</th>
        <th>Ação</th>
    </tr>
    <?php while($tr = $transportadoras->fetch_assoc()){
        $total = intval($tr["total_envios"]);
        $ok = intval($tr["entregues"]);
        $perc = $total > 0 ? round(($ok / $total) * 100, 2) : 0;
    ?>
    <tr>
        <td><?php echo $tr["id_transportadora"]; ?></td>
        <td><?php echo htmlspecialchars($tr["nome"]); ?></td>
        <td><?php echo htmlspecialchars($tr["cnpj"]); ?></td>
        <td><?php echo htmlspecialchars($tr["tipo"]); ?></td>
        <td><?php echo $tr["criado_em"]; ?></td>
        <td><?php echo $total; ?></td>
        <td><?php echo $perc; ?></td>
        <td><?php echo htmlspecialchars($tr["status"]); ?></td>
        <td>
            <form method="POST" onsubmit="return confirm('Excluir transportadora?');" style="display:inline">
                <input type="hidden" name="acao" value="excluir_transportadora">
                <input type="hidden" name="id_transportadora" value="<?php echo $tr["id_transportadora"]; ?>">
                <button type="submit">Excluir</button>
            </form>
        </td>
    </tr>
    <?php } ?>
</table>

<h2>Em Transporte</h2>
<table border="1" cellpadding="6" cellspacing="0">
    <tr>
        <th>ID</th>
        <th>Código da Moto</th>
        <th>Tipo</th>
        <th>Transportadora</th>
        <th>Status</th>
        <th>Destino</th>
        <th>Previsão</th>
        <th>Ações</th>
    </tr>
    <?php while($e = $emTransporte->fetch_assoc()){ ?>
    <tr>
        <td><?php echo $e["id_envio"]; ?></td>
        <td><?php echo htmlspecialchars($e["codigo_moto"]); ?></td>
        <td><?php echo htmlspecialchars($e["tipo"]); ?></td>
        <td><?php echo htmlspecialchars($e["transportadora"]); ?></td>
        <td><?php echo htmlspecialchars($e["status"]); ?></td>
        <td><?php echo htmlspecialchars($e["destino"]); ?></td>
        <td><?php echo htmlspecialchars($e["previsao_entrega"]); ?></td>
        <td>
            <?php if ($e["status"] === "PENDENTE") { ?>
                <form method="POST" style="display:inline">
                    <input type="hidden" name="acao" value="iniciar_envio">
                    <input type="hidden" name="id_envio" value="<?php echo $e["id_envio"]; ?>">
                    <button type="submit">Iniciar</button>
                </form>
            <?php } ?>
            <?php if ($e["status"] === "A CAMINHO") { ?>
                <form method="POST" style="display:inline">
                    <input type="hidden" name="acao" value="finalizar_envio">
                    <input type="hidden" name="id_envio" value="<?php echo $e["id_envio"]; ?>">
                    <button type="submit">Finalizar</button>
                </form>
                <form method="POST" style="display:inline">
                    <input type="hidden" name="acao" value="marcar_atrasado">
                    <input type="hidden" name="id_envio" value="<?php echo $e["id_envio"]; ?>">
                    <button type="submit">Atrasado</button>
                </form>
            <?php } ?>
            <?php if ($e["status"] === "ATRASADO") { ?>
                <form method="POST" style="display:inline">
                    <input type="hidden" name="acao" value="finalizar_envio">
                    <input type="hidden" name="id_envio" value="<?php echo $e["id_envio"]; ?>">
                    <button type="submit">Finalizar</button>
                </form>
            <?php } ?>
        </td>
    </tr>
    <?php } ?>
</table>

<h2>Entregues</h2>
<table border="1" cellpadding="6" cellspacing="0">
    <tr>
        <th>ID</th>
        <th>Código da Moto</th>
        <th>Tipo</th>
        <th>Transportadora</th>
        <th>Destino</th>
        <th>Previsão</th>
        <th>Início</th>
        <th>Fim</th>
    </tr>
    <?php while($e = $entregues->fetch_assoc()){ ?>
    <tr>
        <td><?php echo $e["id_envio"]; ?></td>
        <td><?php echo htmlspecialchars($e["codigo_moto"]); ?></td>
        <td><?php echo htmlspecialchars($e["tipo"]); ?></td>
        <td><?php echo htmlspecialchars($e["transportadora"]); ?></td>
        <td><?php echo htmlspecialchars($e["destino"]); ?></td>
        <td><?php echo htmlspecialchars($e["previsao_entrega"]); ?></td>
        <td><?php echo $e["data_inicio"]; ?></td>
        <td><?php echo $e["data_fim"]; ?></td>
    </tr>
    <?php } ?>
</table>
</div>
</body>
</html>
