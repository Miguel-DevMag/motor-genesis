<?php
include("seguranca.php");
include("conexao.php");

$msg = "";
$erro = "";

// Processar ações
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $acao = $_POST["acao"] ?? "";
    
    if ($acao === "adicionar") {
        $nome = trim($_POST["nome"] ?? "");
        $codigo = trim($_POST["codigo"] ?? "");
        $quantidade = intval($_POST["quantidade"] ?? 0);
        $custo = floatval($_POST["custo"] ?? 0);
        $preco = floatval($_POST["preco"] ?? 0);
        $categoria = trim($_POST["categoria"] ?? "");
        
        if ($nome && $codigo && $quantidade >= 0) {
            $sql = "INSERT INTO Pecas (nome_peca, codigo_peca, quantidade_estoque, custo_unitario) 
                    VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("ssid", $nome, $codigo, $quantidade, $custo);
                if ($stmt->execute()) {
                    $msg = "Peça adicionada com sucesso!";
                } else {
                    $erro = "Erro ao adicionar: " . $stmt->error;
                }
                $stmt->close();
            }
        } else {
            $erro = "Preencha os campos obrigatórios!";
        }
    }
    
    elseif ($acao === "editar") {
        $id = intval($_POST["id_peca"] ?? 0);
        $nome = trim($_POST["nome"] ?? "");
        $codigo = trim($_POST["codigo"] ?? "");
        $quantidade = intval($_POST["quantidade"] ?? 0);
        $custo = floatval($_POST["custo"] ?? 0);
        $preco = floatval($_POST["preco"] ?? 0);
        $categoria = trim($_POST["categoria"] ?? "");
        
        if ($id > 0 && $nome && $codigo) {
            $sql = "UPDATE Pecas SET nome_peca=?, codigo_peca=?, quantidade_estoque=?, custo_unitario=? 
                    WHERE id_peca=?";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("ssidi", $nome, $codigo, $quantidade, $custo, $id);
                if ($stmt->execute()) {
                    $msg = "Peça atualizada com sucesso!";
                } else {
                    $erro = "Erro ao atualizar: " . $stmt->error;
                }
                $stmt->close();
            }
        }
    }
    
    elseif ($acao === "excluir") {
        $id = intval($_POST["id_peca"] ?? 0);
        if ($id > 0) {
            $sql = "DELETE FROM Pecas WHERE id_peca=?";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("i", $id);
                if ($stmt->execute()) {
                    $msg = "Peça removida com sucesso!";
                } else {
                    $erro = "Erro ao remover: " . $stmt->error;
                }
                $stmt->close();
            }
        }
    }
}

// Buscar peças
$busca = trim($_GET["busca"] ?? "");
if ($busca) {
    $termo = "%$busca%";
    $sql = "SELECT * FROM Pecas WHERE nome LIKE ? OR codigo LIKE ? ORDER BY id_peca DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $termo, $termo);
    $stmt->execute();
    $resultado = $stmt->get_result();
} else {
    $sql = "SELECT * FROM Pecas ORDER BY id_peca DESC";
    $resultado = $conn->query($sql);
}

// Dados para edição
$edicao = null;
if (isset($_GET["editar"])) {
    $id = intval($_GET["editar"]);
    $sql = "SELECT * FROM Pecas WHERE id_peca=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $edicao = $stmt->get_result()->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Estoque - Motor Genesis</title>
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
        <a href="estoque.php" class="active"><i class="fas fa-boxes"></i> Estoque</a>
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
        <form method="GET" style="display: flex; gap: 10px; flex: 1;">
            <input type="text" name="busca" placeholder="Buscar peças por nome ou código..." value="<?php echo htmlspecialchars($busca); ?>">
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Buscar</button>
            <?php if ($busca): ?>
                <a href="estoque.php" class="btn btn-secondary"><i class="fas fa-times"></i> Limpar</a>
            <?php endif; ?>
        </form>
        <div class="user">
            <i class="fas fa-user-circle"></i> <?php echo $_SESSION["login"]; ?> |
            <span class="status-online"><i class="fas fa-check-circle"></i> Online</span>
        </div>
    </div>

    <h1><i class="fas fa-boxes"></i> Gestão de Estoque</h1>

    <?php if ($msg): ?>
        <div class="success"><i class="fas fa-check-circle"></i> <?php echo $msg; ?></div>
    <?php endif; ?>
    
    <?php if ($erro): ?>
        <div class="error"><i class="fas fa-exclamation-circle"></i> <?php echo $erro; ?></div>
    <?php endif; ?>

    <form method="POST" style="margin-bottom: 30px;">
        <input type="hidden" name="acao" value="<?php echo $edicao ? 'editar' : 'adicionar'; ?>">
        <?php if ($edicao): ?>
            <input type="hidden" name="id_peca" value="<?php echo $edicao['id_peca']; ?>">
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
            <div>
                <label><i class="fas fa-tag"></i> Nome *</label>
                <input type="text" name="nome" value="<?php echo $edicao['nome_peca'] ?? ''; ?>" required>
            </div>
            <div>
                <label><i class="fas fa-barcode"></i> Código *</label>
                <input type="text" name="codigo" value="<?php echo $edicao['codigo_peca'] ?? ''; ?>" required>
            </div>
            <div>
                <label><i class="fas fa-cube"></i> Quantidade *</label>
                <input type="number" name="quantidade" value="<?php echo $edicao['quantidade_estoque'] ?? '0'; ?>" required>
            </div>
            <div>
                <label><i class="fas fa-dollar-sign"></i> Custo Unitário</label>
                <input type="number" name="custo" step="0.01" value="<?php echo $edicao['custo_unitario'] ?? '0'; ?>">
            </div>
        </div>

        <div style="margin-top: 15px; display: flex; gap: 10px;">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-<?php echo $edicao ? 'edit' : 'plus'; ?>"></i>
                <?php echo $edicao ? 'Atualizar' : 'Adicionar'; ?> Peça
            </button>
            <?php if ($edicao): ?>
                <a href="estoque.php" class="btn btn-secondary"><i class="fas fa-times"></i> Cancelar</a>
            <?php endif; ?>
        </div>
    </form>

    <h2><i class="fas fa-list"></i> Peças Cadastradas</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Código</th>
                <th>Quantidade</th>
                <th>Custo Unit.</th>
                <th>Valor Total</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($resultado && $resultado->num_rows > 0): ?>
                <?php while ($peca = $resultado->fetch_assoc()): 
                    $valor_total = $peca['quantidade_estoque'] * $peca['custo_unitario'];
                ?>
                <tr>
                    <td><?php echo $peca['id_peca']; ?></td>
                    <td><?php echo htmlspecialchars($peca['nome_peca']); ?></td>
                    <td><?php echo htmlspecialchars($peca['codigo_peca']); ?></td>
                    <td><?php echo $peca['quantidade_estoque']; ?></td>
                    <td>R$ <?php echo number_format($peca['custo_unitario'], 2, ',', '.'); ?></td>
                    <td><strong>R$ <?php echo number_format($valor_total, 2, ',', '.'); ?></strong></td>
                    <td>
                        <a href="estoque.php?editar=<?php echo $peca['id_peca']; ?>" class="btn btn-primary" style="font-size: 0.85em; padding: 6px 10px;">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form method="POST" style="display: inline;" onsubmit="return confirm('Excluir esta peça?');">
                            <input type="hidden" name="acao" value="excluir">
                            <input type="hidden" name="id_peca" value="<?php echo $peca['id_peca']; ?>">
                            <button type="submit" class="btn btn-danger" style="font-size: 0.85em; padding: 6px 10px;">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9" style="text-align: center; padding: 30px; color: #999;">
                        <i class="fas fa-inbox" style="font-size: 2em; display: block; margin-bottom: 10px;"></i>
                        Nenhuma peça cadastrada
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>