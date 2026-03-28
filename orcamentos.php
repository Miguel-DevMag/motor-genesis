<?php
include("seguranca.php");
include("conexao.php");

$msg = "";
$erro = "";

// Criar tabela Orcamentos se não existir
$sql_tabela = "CREATE TABLE IF NOT EXISTS Orcamentos (
    id_orcamento INT PRIMARY KEY AUTO_INCREMENT,
    numero VARCHAR(50) UNIQUE,
    cliente VARCHAR(150) NOT NULL,
    descricao TEXT,
    valor_total DECIMAL(10, 2),
    status VARCHAR(50) DEFAULT 'PENDENTE',
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_validade DATE
)";
$conn->query($sql_tabela);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $acao = $_POST["acao"] ?? "";
    
    if ($acao === "adicionar") {
        $numero = trim($_POST["numero"] ?? "");
        $cliente = trim($_POST["cliente"] ?? "");
        $descricao = trim($_POST["descricao"] ?? "");
        $valor = floatval($_POST["valor"] ?? 0);
        $status = trim($_POST["status"] ?? "PENDENTE");
        $validade = trim($_POST["validade"] ?? "");
        
        if ($numero && $cliente && $valor > 0) {
            $sql = "INSERT INTO Orcamentos (numero, cliente, descricao, valor_total, status, data_validade) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("sssdss", $numero, $cliente, $descricao, $valor, $status, $validade);
                if ($stmt->execute()) {
                    $msg = "Orçamento adicionado com sucesso!";
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
        $id = intval($_POST["id_orcamento"] ?? 0);
        $numero = trim($_POST["numero"] ?? "");
        $cliente = trim($_POST["cliente"] ?? "");
        $descricao = trim($_POST["descricao"] ?? "");
        $valor = floatval($_POST["valor"] ?? 0);
        $status = trim($_POST["status"] ?? "PENDENTE");
        $validade = trim($_POST["validade"] ?? "");
        
        if ($id > 0 && $numero && $cliente) {
            $sql = "UPDATE Orcamentos SET numero=?, cliente=?, descricao=?, valor_total=?, status=?, data_validade=? 
                    WHERE id_orcamento=?";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("ssdsssi", $numero, $cliente, $descricao, $valor, $status, $validade, $id);
                if ($stmt->execute()) {
                    $msg = "Orçamento atualizado com sucesso!";
                } else {
                    $erro = "Erro ao atualizar: " . $stmt->error;
                }
                $stmt->close();
            }
        }
    }
    
    elseif ($acao === "excluir") {
        $id = intval($_POST["id_orcamento"] ?? 0);
        if ($id > 0) {
            $sql = "DELETE FROM Orcamentos WHERE id_orcamento=?";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("i", $id);
                if ($stmt->execute()) {
                    $msg = "Orçamento removido com sucesso!";
                } else {
                    $erro = "Erro ao remover: " . $stmt->error;
                }
                $stmt->close();
            }
        }
    }
}

// Buscar orçamentos
$busca = trim($_GET["busca"] ?? "");
if ($busca) {
    $termo = "%$busca%";
    $sql = "SELECT * FROM Orcamentos WHERE numero LIKE ? OR cliente LIKE ? ORDER BY id_orcamento DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $termo, $termo);
    $stmt->execute();
    $resultado = $stmt->get_result();
} else {
    $sql = "SELECT * FROM Orcamentos ORDER BY id_orcamento DESC";
    $resultado = $conn->query($sql);
}

// Dados para edição
$edicao = null;
if (isset($_GET["editar"])) {
    $id = intval($_GET["editar"]);
    $sql = "SELECT * FROM Orcamentos WHERE id_orcamento=?";
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
<title>Orçamentos - Motor Genesis</title>
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
        <a href="orcamentos.php" class="active"><i class="fas fa-file-invoice"></i> Orçamentos</a>
        <a href="relatorios.php"><i class="fas fa-chart-bar"></i> Relatórios</a>
        <a href="usuarios.php"><i class="fas fa-users"></i> Usuários</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a>
    </div>
</div>

<div class="main">
    <div class="topbar">
        <form method="GET" style="display: flex; gap: 10px; flex: 1;">
            <input type="text" name="busca" placeholder="Buscar orçamentos..." value="<?php echo htmlspecialchars($busca); ?>">
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Buscar</button>
            <?php if ($busca): ?>
                <a href="orcamentos.php" class="btn btn-secondary"><i class="fas fa-times"></i> Limpar</a>
            <?php endif; ?>
        </form>
        <div class="user">
            <i class="fas fa-user-circle"></i> <?php echo $_SESSION["login"]; ?> |
            <span class="status-online"><i class="fas fa-check-circle"></i> Online</span>
        </div>
    </div>

    <h1><i class="fas fa-file-invoice"></i> Orçamentos</h1>

    <?php if ($msg): ?>
        <div class="success"><i class="fas fa-check-circle"></i> <?php echo $msg; ?></div>
    <?php endif; ?>
    
    <?php if ($erro): ?>
        <div class="error"><i class="fas fa-exclamation-circle"></i> <?php echo $erro; ?></div>
    <?php endif; ?>

    <form method="POST" style="margin-bottom: 30px;">
        <input type="hidden" name="acao" value="<?php echo $edicao ? 'editar' : 'adicionar'; ?>">
        <?php if ($edicao): ?>
            <input type="hidden" name="id_orcamento" value="<?php echo $edicao['id_orcamento']; ?>">
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
            <div>
                <label><i class="fas fa-hashtag"></i> Número *</label>
                <input type="text" name="numero" value="<?php echo $edicao['numero'] ?? ''; ?>" required>
            </div>
            <div>
                <label><i class="fas fa-user"></i> Cliente *</label>
                <input type="text" name="cliente" value="<?php echo $edicao['cliente'] ?? ''; ?>" required>
            </div>
            <div>
                <label><i class="fas fa-dollar-sign"></i> Valor Total *</label>
                <input type="number" name="valor" step="0.01" value="<?php echo $edicao['valor_total'] ?? '0'; ?>" required>
            </div>
            <div>
                <label><i class="fas fa-calendar"></i> Data de Validade</label>
                <input type="date" name="validade" value="<?php echo $edicao['data_validade'] ?? ''; ?>">
            </div>
            <div>
                <label><i class="fas fa-info-circle"></i> Status</label>
                <select name="status">
                    <option value="PENDENTE" <?php echo (!$edicao || $edicao['status'] == 'PENDENTE') ? 'selected' : ''; ?>>Pendente</option>
                    <option value="APROVADO" <?php echo ($edicao && $edicao['status'] == 'APROVADO') ? 'selected' : ''; ?>>Aprovado</option>
                    <option value="REJEITADO" <?php echo ($edicao && $edicao['status'] == 'REJEITADO') ? 'selected' : ''; ?>>Rejeitado</option>
                </select>
            </div>
        </div>

        <div class="form-group" style="margin-top: 15px;">
            <label><i class="fas fa-file-alt"></i> Descrição</label>
            <textarea name="descricao" placeholder="Descrição do orçamento"><?php echo $edicao['descricao'] ?? ''; ?></textarea>
        </div>

        <div style="margin-top: 15px; display: flex; gap: 10px;">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-<?php echo $edicao ? 'edit' : 'plus'; ?>"></i>
                <?php echo $edicao ? 'Atualizar' : 'Adicionar'; ?> Orçamento
            </button>
            <?php if ($edicao): ?>
                <a href="orcamentos.php" class="btn btn-secondary"><i class="fas fa-times"></i> Cancelar</a>
            <?php endif; ?>
        </div>
    </form>

    <h2><i class="fas fa-list"></i> Orçamentos Cadastrados</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Número</th>
                <th>Cliente</th>
                <th>Valor</th>
                <th>Status</th>
                <th>Data Criação</th>
                <th>Validade</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($resultado && $resultado->num_rows > 0): ?>
                <?php while ($orc = $resultado->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $orc['id_orcamento']; ?></td>
                    <td><?php echo htmlspecialchars($orc['numero']); ?></td>
                    <td><?php echo htmlspecialchars($orc['cliente']); ?></td>
                    <td><strong>R$ <?php echo number_format($orc['valor_total'], 2, ',', '.'); ?></strong></td>
                    <td>
                        <span class="status-badge" style="background: <?php 
                            if ($orc['status'] == 'PENDENTE') echo '#ff9800';
                            elseif ($orc['status'] == 'APROVADO') echo '#00c853';
                            else echo '#f44336';
                        ?>;">
                            <?php echo htmlspecialchars($orc['status']); ?>
                        </span>
                    </td>
                    <td><?php echo date('d/m/Y', strtotime($orc['data_criacao'])); ?></td>
                    <td><?php echo $orc['data_validade'] ? date('d/m/Y', strtotime($orc['data_validade'])) : '-'; ?></td>
                    <td>
                        <a href="orcamentos.php?editar=<?php echo $orc['id_orcamento']; ?>" class="btn btn-primary" style="font-size: 0.85em; padding: 6px 10px;">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form method="POST" style="display: inline;" onsubmit="return confirm('Excluir este orçamento?');">
                            <input type="hidden" name="acao" value="excluir">
                            <input type="hidden" name="id_orcamento" value="<?php echo $orc['id_orcamento']; ?>">
                            <button type="submit" class="btn btn-danger" style="font-size: 0.85em; padding: 6px 10px;">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" style="text-align: center; padding: 30px; color: #999;">
                        <i class="fas fa-inbox" style="font-size: 2em; display: block; margin-bottom: 10px;"></i>
                        Nenhum orçamento cadastrado
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
