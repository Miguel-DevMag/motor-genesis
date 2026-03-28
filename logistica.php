<?php
include("seguranca.php");
include("conexao.php");

$msg = "";
$erro = "";

// Processar ações POST
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["acao"])) {
    try {
        if ($_POST["acao"] === "cadastrar_transportadora") {
            $nome = trim($_POST["nome"] ?? "");
            $cnpj = trim($_POST["cnpj"] ?? "");
            $tipo = trim($_POST["tipo"] ?? "");
            $status = trim($_POST["status"] ?? "ATIVO");
            
            if ($nome !== "") {
                $sql = "INSERT INTO Transportadoras (nome, cnpj, tipo, status) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    $erro = "Erro na preparação: " . $conn->error;
                } else {
                    $stmt->bind_param("ssss", $nome, $cnpj, $tipo, $status);
                    if ($stmt->execute()) {
                        $msg = "Transportadora cadastrada com sucesso!";
                    } else {
                        $erro = "Erro ao cadastrar: " . $stmt->error;
                    }
                    $stmt->close();
                }
            } else {
                $erro = "Informe o nome da transportadora.";
            }
        }
        
        elseif ($_POST["acao"] === "excluir_transportadora") {
            $id = intval($_POST["id_transportadora"] ?? 0);
            if ($id > 0) {
                $sql = "DELETE FROM Transportadoras WHERE id_transportadora=?";
                $stmt = $conn->prepare($sql);
                if ($stmt) {
                    $stmt->bind_param("i", $id);
                    if ($stmt->execute()) {
                        $msg = "Transportadora excluída com sucesso!";
                    } else {
                        $erro = "Não foi possível excluir (existem envios vinculados).";
                    }
                    $stmt->close();
                }
            }
        }
        
        elseif ($_POST["acao"] === "cadastrar_envio") {
            $codigo_moto = trim($_POST["codigo_moto"] ?? "");
            $tipo = trim($_POST["tipo"] ?? "");
            $id_transp = intval($_POST["id_transportadora"] ?? 0);
            $destino = trim($_POST["destino"] ?? "");
            $previsao = !empty($_POST["previsao_entrega"]) ? $_POST["previsao_entrega"] : null;
            
            if ($codigo_moto !== "" && $tipo !== "" && $id_transp > 0) {
                $sql = "INSERT INTO Envios (codigo_moto, tipo, id_transportadora, destino, previsao_entrega) VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                if ($stmt) {
                    $stmt->bind_param("ssiss", $codigo_moto, $tipo, $id_transp, $destino, $previsao);
                    if ($stmt->execute()) {
                        $msg = "Envio criado com sucesso!";
                    } else {
                        $erro = "Erro ao criar envio: " . $stmt->error;
                    }
                    $stmt->close();
                }
            } else {
                $erro = "Preencha os campos obrigatórios (Código, Tipo, Transportadora).";
            }
        }
        
        elseif ($_POST["acao"] === "iniciar_envio") {
            $id = intval($_POST["id_envio"] ?? 0);
            if ($id > 0) {
                $sql = "UPDATE Envios SET status='A CAMINHO', data_inicio=NOW() WHERE id_envio=?";
                $stmt = $conn->prepare($sql);
                if ($stmt) {
                    $stmt->bind_param("i", $id);
                    if ($stmt->execute()) {
                        $msg = "Envio iniciado!";
                    } else {
                        $erro = "Erro ao iniciar: " . $stmt->error;
                    }
                    $stmt->close();
                }
            }
        }
        
        elseif ($_POST["acao"] === "finalizar_envio") {
            $id = intval($_POST["id_envio"] ?? 0);
            if ($id > 0) {
                $sql = "UPDATE Envios SET status='ENTREGUE', data_fim=NOW() WHERE id_envio=?";
                $stmt = $conn->prepare($sql);
                if ($stmt) {
                    $stmt->bind_param("i", $id);
                    if ($stmt->execute()) {
                        $msg = "Envio finalizado!";
                    } else {
                        $erro = "Erro ao finalizar: " . $stmt->error;
                    }
                    $stmt->close();
                }
            }
        }
        
        elseif ($_POST["acao"] === "marcar_atrasado") {
            $id = intval($_POST["id_envio"] ?? 0);
            if ($id > 0) {
                $sql = "UPDATE Envios SET status='ATRASADO' WHERE id_envio=?";
                $stmt = $conn->prepare($sql);
                if ($stmt) {
                    $stmt->bind_param("i", $id);
                    if ($stmt->execute()) {
                        $msg = "Envio marcado como atrasado!";
                    } else {
                        $erro = "Erro ao marcar: " . $stmt->error;
                    }
                    $stmt->close();
                }
            }
        }
    } catch (Exception $e) {
        $erro = "Erro: " . $e->getMessage();
    }
}

// Buscar transportadoras
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
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Logística - Motor Genesis</title>
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
        <a href="logistica.php" class="active"><i class="fas fa-truck"></i> Logística</a>
        <a href="orcamentos.php"><i class="fas fa-file-invoice"></i> Orçamentos</a>
        <a href="relatorios.php"><i class="fas fa-chart-bar"></i> Relatórios</a>
        <a href="usuarios.php"><i class="fas fa-users"></i> Usuários</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a>
    </div>
</div>

<div class="main">

<h1><i class="fas fa-truck"></i> Logística e Envios</h1>

<?php if ($msg !== "") { ?>
    <div class="success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($msg); ?></div>
<?php } ?>

<?php if ($erro !== "") { ?>
    <div style="background: rgba(225, 6, 0, 0.1); border-left: 4px solid #e10600; padding: 15px; border-radius: 4px; margin: 15px 0; color: #ff6b6b;">
        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($erro); ?>
    </div>
<?php } ?>

<div style="margin: 20px 0;">
    <button onclick="toggleForm('transportadora')" style="margin-right: 10px;"><i class="fas fa-plus"></i> Nova Transportadora</button>
    <button onclick="toggleForm('envio')"><i class="fas fa-plus"></i> Novo Envio</button>
</div>

<?php if (isset($_GET["novo"]) && $_GET["novo"] === "transportadora") { ?>
    <h2><i class="fas fa-building"></i> Cadastrar Transportadora</h2>
    <form method="POST" style="background: #161a20; padding: 20px; border-radius: 8px; margin: 20px 0;">
        <input type="hidden" name="acao" value="cadastrar_transportadora">
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px;"><i class="fas fa-building"></i> Nome *</label>
            <input type="text" name="nome" placeholder="Nome da transportadora" required style="width: 100%; padding: 10px; background: #222; border: 1px solid #333; color: #fff; border-radius: 4px;">
        </div>
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px;"><i class="fas fa-id-card"></i> CNPJ</label>
            <input type="text" name="cnpj" placeholder="00.000.000/0000-00" style="width: 100%; padding: 10px; background: #222; border: 1px solid #333; color: #fff; border-radius: 4px;">
        </div>
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px;"><i class="fas fa-truck"></i> Tipo</label>
            <input type="text" name="tipo" placeholder="Ex: Rodoviário, Aéreo, Marítimo" style="width: 100%; padding: 10px; background: #222; border: 1px solid #333; color: #fff; border-radius: 4px;">
        </div>
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px;"><i class="fas fa-toggle-on"></i> Status</label>
            <select name="status" style="width: 100%; padding: 10px; background: #222; border: 1px solid #333; color: #fff; border-radius: 4px;">
                <option value="ATIVO">ATIVO</option>
                <option value="INATIVO">INATIVO</option>
            </select>
        </div>
        <button type="submit" style="background: #2962ff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; margin-right: 10px;"><i class="fas fa-check"></i> Salvar</button>
        <a href="logistica.php" style="background: #333; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block;"><i class="fas fa-times"></i> Cancelar</a>
    </form>
<?php } ?>

<?php if (isset($_GET["novo"]) && $_GET["novo"] === "envio") { ?>
    <h2><i class="fas fa-box"></i> Novo Envio</h2>
    <form method="POST" style="background: #161a20; padding: 20px; border-radius: 8px; margin: 20px 0;">
        <input type="hidden" name="acao" value="cadastrar_envio">
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px;"><i class="fas fa-barcode"></i> Código da Moto *</label>
            <input type="text" name="codigo_moto" placeholder="Código da moto" required style="width: 100%; padding: 10px; background: #222; border: 1px solid #333; color: #fff; border-radius: 4px;">
        </div>
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px;"><i class="fas fa-tag"></i> Tipo *</label>
            <select name="tipo" required style="width: 100%; padding: 10px; background: #222; border: 1px solid #333; color: #fff; border-radius: 4px;">
                <option value="">-- Selecione --</option>
                <option value="MOTO">MOTO COMPLETA</option>
                <option value="PECAS">PEÇAS</option>
            </select>
        </div>
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px;"><i class="fas fa-truck"></i> Transportadora *</label>
            <select name="id_transportadora" required style="width: 100%; padding: 10px; background: #222; border: 1px solid #333; color: #fff; border-radius: 4px;">
                <option value="">-- Selecione --</option>
                <?php if ($transportadorasSelect) { while($t = $transportadorasSelect->fetch_assoc()){ ?>
                    <option value="<?php echo $t["id_transportadora"]; ?>"><?php echo htmlspecialchars($t["nome"]); ?></option>
                <?php } } ?>
            </select>
        </div>
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px;"><i class="fas fa-map-marker-alt"></i> Destino *</label>
            <input type="text" name="destino" placeholder="Cidade/UF, Endereço" required style="width: 100%; padding: 10px; background: #222; border: 1px solid #333; color: #fff; border-radius: 4px;">
        </div>
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px;"><i class="fas fa-calendar"></i> Previsão de Entrega</label>
            <input type="date" name="previsao_entrega" style="width: 100%; padding: 10px; background: #222; border: 1px solid #333; color: #fff; border-radius: 4px;">
        </div>
        <button type="submit" style="background: #2962ff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; margin-right: 10px;"><i class="fas fa-check"></i> Criar</button>
        <a href="logistica.php" style="background: #333; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block;"><i class="fas fa-times"></i> Cancelar</a>
    </form>
<?php } ?>

<h2 style="margin-top: 30px;"><i class="fas fa-building"></i> Transportadoras</h2>
<table style="width: 100%; border-collapse: collapse; margin: 15px 0;">
    <thead>
    <tr style="background: #222; border-bottom: 2px solid #333;">
        <th style="padding: 12px; text-align: left; color: #2962ff;">ID</th>
        <th style="padding: 12px; text-align: left; color: #2962ff;">Nome</th>
        <th style="padding: 12px; text-align: left; color: #2962ff;">CNPJ</th>
        <th style="padding: 12px; text-align: left; color: #2962ff;">Tipo</th>
        <th style="padding: 12px; text-align: left; color: #2962ff;">Envios</th>
        <th style="padding: 12px; text-align: left; color: #2962ff;">Entregues</th>
        <th style="padding: 12px; text-align: left; color: #2962ff;">Status</th>
        <th style="padding: 12px; text-align: left; color: #2962ff;">Ação</th>
    </tr>
    </thead>
    <tbody>
    <?php if ($transportadoras) { while($tr = $transportadoras->fetch_assoc()){ ?>
    <tr style="border-bottom: 1px solid #333;">
        <td style="padding: 12px;"><?php echo $tr["id_transportadora"]; ?></td>
        <td style="padding: 12px;"><?php echo htmlspecialchars($tr["nome"]); ?></td>
        <td style="padding: 12px;"><?php echo htmlspecialchars($tr["cnpj"] ?? "---"); ?></td>
        <td style="padding: 12px;"><?php echo htmlspecialchars($tr["tipo"] ?? "---"); ?></td>
        <td style="padding: 12px;"><?php echo intval($tr["total_envios"]); ?></td>
        <td style="padding: 12px;"><?php echo intval($tr["entregues"] ?? 0); ?></td>
        <td style="padding: 12px;"><span style="background: <?php echo $tr["status"] === 'ATIVO' ? '#00c853' : '#e10600'; ?>; color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px;"><?php echo $tr["status"]; ?></span></td>
        <td style="padding: 12px;">
            <form method="POST" onsubmit="return confirm('Excluir esta transportadora?');" style="display:inline">
                <input type="hidden" name="acao" value="excluir_transportadora">
                <input type="hidden" name="id_transportadora" value="<?php echo $tr["id_transportadora"]; ?>">
                <button type="submit" style="background: #e10600; color: white; padding: 6px 12px; border: none; border-radius: 4px; cursor: pointer;"><i class="fas fa-trash"></i></button>
            </form>
        </td>
    </tr>
    <?php } } else { ?>
    <tr style="border-bottom: 1px solid #333;">
        <td colspan="8" style="padding: 20px; text-align: center; color: #999;">Nenhuma transportadora cadastrada</td>
    </tr>
    <?php } ?>
    </tbody>
</table>

<h2 style="margin-top: 30px;"><i class="fas fa-dolly"></i> Em Transporte</h2>
<table style="width: 100%; border-collapse: collapse; margin: 15px 0;">
    <thead>
    <tr style="background: #222; border-bottom: 2px solid #333;">
        <th style="padding: 12px; text-align: left; color: #2962ff;">ID</th>
        <th style="padding: 12px; text-align: left; color: #2962ff;">Código</th>
        <th style="padding: 12px; text-align: left; color: #2962ff;">Tipo</th>
        <th style="padding: 12px; text-align: left; color: #2962ff;">Transportadora</th>
        <th style="padding: 12px; text-align: left; color: #2962ff;">Destino</th>
        <th style="padding: 12px; text-align: left; color: #2962ff;">Status</th>
        <th style="padding: 12px; text-align: left; color: #2962ff;">Previsão</th>
        <th style="padding: 12px; text-align: left; color: #2962ff;">Ações</th>
    </tr>
    </thead>
    <tbody>
    <?php if ($emTransporte) { while($e = $emTransporte->fetch_assoc()){ ?>
    <tr style="border-bottom: 1px solid #333;">
        <td style="padding: 12px;"><?php echo $e["id_envio"]; ?></td>
        <td style="padding: 12px;"><?php echo htmlspecialchars($e["codigo_moto"]); ?></td>
        <td style="padding: 12px;"><?php echo htmlspecialchars($e["tipo"]); ?></td>
        <td style="padding: 12px;"><?php echo htmlspecialchars($e["transportadora"]); ?></td>
        <td style="padding: 12px;"><?php echo htmlspecialchars($e["destino"]); ?></td>
        <td style="padding: 12px;"><span style="background: #ffab00; color: #000; padding: 4px 12px; border-radius: 20px; font-size: 12px;"><?php echo htmlspecialchars($e["status"]); ?></span></td>
        <td style="padding: 12px;"><?php echo $e["previsao_entrega"] ? date('d/m/Y', strtotime($e["previsao_entrega"])) : '---'; ?></td>
        <td style="padding: 12px;">
            <?php if ($e["status"] === "PENDENTE") { ?>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="acao" value="iniciar_envio">
                    <input type="hidden" name="id_envio" value="<?php echo $e["id_envio"]; ?>">
                    <button type="submit" style="background: #2962ff; color: white; padding: 6px 12px; border: none; border-radius: 4px; cursor: pointer; font-size: 12px;"><i class="fas fa-play"></i> Iniciar</button>
                </form>
            <?php } elseif ($e["status"] === "A CAMINHO") { ?>
                <form method="POST" style="display:inline; margin-right: 5px;">
                    <input type="hidden" name="acao" value="finalizar_envio">
                    <input type="hidden" name="id_envio" value="<?php echo $e["id_envio"]; ?>">
                    <button type="submit" style="background: #00c853; color: white; padding: 6px 12px; border: none; border-radius: 4px; cursor: pointer; font-size: 12px;"><i class="fas fa-check"></i></button>
                </form>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="acao" value="marcar_atrasado">
                    <input type="hidden" name="id_envio" value="<?php echo $e["id_envio"]; ?>">
                    <button type="submit" style="background: #ffab00; color: #000; padding: 6px 12px; border: none; border-radius: 4px; cursor: pointer; font-size: 12px;"><i class="fas fa-clock"></i></button>
                </form>
            <?php } elseif ($e["status"] === "ATRASADO") { ?>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="acao" value="finalizar_envio">
                    <input type="hidden" name="id_envio" value="<?php echo $e["id_envio"]; ?>">
                    <button type="submit" style="background: #00c853; color: white; padding: 6px 12px; border: none; border-radius: 4px; cursor: pointer; font-size: 12px;"><i class="fas fa-check"></i> Entregar</button>
                </form>
            <?php } ?>
        </td>
    </tr>
    <?php } } else { ?>
    <tr style="border-bottom: 1px solid #333;">
        <td colspan="8" style="padding: 20px; text-align: center; color: #999;">Nenhum envio em transporte</td>
    </tr>
    <?php } ?>
    </tbody>
</table>

<h2 style="margin-top: 30px;"><i class="fas fa-check-circle"></i> Entregues</h2>
<table style="width: 100%; border-collapse: collapse; margin: 15px 0;">
    <thead>
    <tr style="background: #222; border-bottom: 2px solid #333;">
        <th style="padding: 12px; text-align: left; color: #2962ff;">ID</th>
        <th style="padding: 12px; text-align: left; color: #2962ff;">Código</th>
        <th style="padding: 12px; text-align: left; color: #2962ff;">Tipo</th>
        <th style="padding: 12px; text-align: left; color: #2962ff;">Transportadora</th>
        <th style="padding: 12px; text-align: left; color: #2962ff;">Destino</th>
        <th style="padding: 12px; text-align: left; color: #2962ff;">Data Início</th>
        <th style="padding: 12px; text-align: left; color: #2962ff;">Data Entrega</th>
    </tr>
    </thead>
    <tbody>
    <?php if ($entregues) { while($e = $entregues->fetch_assoc()){ ?>
    <tr style="border-bottom: 1px solid #333;">
        <td style="padding: 12px;"><?php echo $e["id_envio"]; ?></td>
        <td style="padding: 12px;"><?php echo htmlspecialchars($e["codigo_moto"]); ?></td>
        <td style="padding: 12px;"><?php echo htmlspecialchars($e["tipo"]); ?></td>
        <td style="padding: 12px;"><?php echo htmlspecialchars($e["transportadora"]); ?></td>
        <td style="padding: 12px;"><?php echo htmlspecialchars($e["destino"]); ?></td>
        <td style="padding: 12px;"><?php echo $e["data_inicio"] ? date('d/m/Y H:i', strtotime($e["data_inicio"])) : '---'; ?></td>
        <td style="padding: 12px;"><?php echo $e["data_fim"] ? date('d/m/Y H:i', strtotime($e["data_fim"])) : '---'; ?></td>
    </tr>
    <?php } } else { ?>
    <tr style="border-bottom: 1px solid #333;">
        <td colspan="7" style="padding: 20px; text-align: center; color: #999;">Nenhum envio entregue</td>
    </tr>
    <?php } ?>
    </tbody>
</table>

</div>

<script>
function toggleForm(type) {
    window.location.href = 'logistica.php?novo=' + type;
}
</script>

</body>
</html>
