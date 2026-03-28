<?php
include("seguranca.php");
include("conexao.php");

$msg = "";
$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["acao"])) {
    if ($_POST["acao"] === "cadastrar_modelo") {
        $nome = trim($_POST["nome_modelo"] ?? "");
        $codigo = trim($_POST["codigo_modelo"] ?? "");
        if ($nome !== "") {
            $sql = "INSERT INTO Modelos (nome_modelo, codigo_modelo) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $nome, $codigo);
            if ($stmt->execute()) {
                $msg = "Modelo cadastrado com sucesso.";
            } else {
                $erro = "Erro ao cadastrar modelo.";
            }
        } else {
            $erro = "Informe o nome do modelo.";
        }
    }
    elseif ($_POST["acao"] === "editar_modelo") {
        $id = intval($_POST["id_modelo"] ?? 0);
        $nome = trim($_POST["nome_modelo"] ?? "");
        $codigo = trim($_POST["codigo_modelo"] ?? "");
        if ($id > 0 && $nome !== "") {
            $sql = "UPDATE Modelos SET nome_modelo=?, codigo_modelo=? WHERE id_modelo=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssi", $nome, $codigo, $id);
            if ($stmt->execute()) {
                $msg = "Modelo atualizado com sucesso.";
            } else {
                $erro = "Erro ao atualizar modelo.";
            }
        }
    }
    elseif ($_POST["acao"] === "excluir_modelo") {
        $id = intval($_POST["id_modelo"] ?? 0);
        if ($id > 0) {
            $sql = "DELETE FROM Modelos WHERE id_modelo=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $msg = "Modelo removido com sucesso.";
            } else {
                $erro = "Erro ao remover modelo.";
            }
        }
    }
    elseif ($_POST["acao"] === "cadastrar_op") {
        $id_modelo = intval($_POST["id_modelo"] ?? 0);
        $quantidade = intval($_POST["quantidade"] ?? 0);
        $cor = trim($_POST["cor"] ?? "");
        $prioridade = trim($_POST["prioridade"] ?? "MEDIA");
        $observacoes = trim($_POST["observacoes"] ?? "");
        if ($id_modelo > 0 && $quantidade > 0) {
            $sql = "INSERT INTO OrdensProducao (id_modelo, quantidade, cor, prioridade, observacoes) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iisss", $id_modelo, $quantidade, $cor, $prioridade, $observacoes);
            if ($stmt->execute()) {
                $msg = "OP criada com sucesso.";
            } else {
                $erro = "Erro ao criar OP.";
            }
        } else {
            $erro = "Selecione um modelo e informe a quantidade.";
        }
    }
    elseif ($_POST["acao"] === "editar_op") {
        $id_op = intval($_POST["id_op"] ?? 0);
        $id_modelo = intval($_POST["id_modelo"] ?? 0);
        $quantidade = intval($_POST["quantidade"] ?? 0);
        $cor = trim($_POST["cor"] ?? "");
        $prioridade = trim($_POST["prioridade"] ?? "MEDIA");
        $observacoes = trim($_POST["observacoes"] ?? "");
        if ($id_op > 0 && $id_modelo > 0 && $quantidade > 0) {
            $sql = "UPDATE OrdensProducao SET id_modelo=?, quantidade=?, cor=?, prioridade=?, observacoes=? WHERE id_op=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iisssi", $id_modelo, $quantidade, $cor, $prioridade, $observacoes, $id_op);
            if ($stmt->execute()) {
                $msg = "OP atualizada com sucesso.";
            } else {
                $erro = "Erro ao atualizar OP.";
            }
        }
    }
    elseif ($_POST["acao"] === "excluir_op") {
        $id_op = intval($_POST["id_op"] ?? 0);
        if ($id_op > 0) {
            $sql = "DELETE FROM OrdensProducao WHERE id_op=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id_op);
            if ($stmt->execute()) {
                $msg = "OP removida com sucesso.";
            } else {
                $erro = "Erro ao remover OP.";
            }
        }
    }
    elseif ($_POST["acao"] === "iniciar_op") {
        $id_op = intval($_POST["id_op"] ?? 0);
        if ($id_op > 0) {
            $sql = "UPDATE OrdensProducao SET status='INICIADA', data_inicio=NOW() WHERE id_op=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id_op);
            if ($stmt->execute()) {
                $msg = "OP iniciada com sucesso.";
            }
        }
    }
    elseif ($_POST["acao"] === "finalizar_op") {
        $id_op = intval($_POST["id_op"] ?? 0);
        if ($id_op > 0) {
            $sql = "UPDATE OrdensProducao SET status='FINALIZADA', data_fim=NOW() WHERE id_op=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id_op);
            if ($stmt->execute()) {
                $msg = "OP finalizada com sucesso.";
            }
        }
    }
}

$modelos = $conn->query("SELECT id_modelo, nome_modelo FROM Modelos ORDER BY nome_modelo ASC");
$modelosLista = $conn->query("SELECT id_modelo, nome_modelo, codigo_modelo FROM Modelos ORDER BY nome_modelo ASC");

$emProducao = $conn->query("
    SELECT o.id_op, m.nome_modelo, o.quantidade, o.cor, o.prioridade, o.status, o.data_criacao, o.data_inicio
    FROM OrdensProducao o
    JOIN Modelos m ON m.id_modelo = o.id_modelo
    WHERE o.status IN ('PENDENTE','INICIADA')
    ORDER BY o.data_criacao DESC
");

$finalizadas = $conn->query("
    SELECT o.id_op, m.nome_modelo, o.quantidade, o.cor, o.prioridade, o.data_inicio, o.data_fim
    FROM OrdensProducao o
    JOIN Modelos m ON m.id_modelo = o.id_modelo
    WHERE o.status='FINALIZADA'
    ORDER BY o.data_fim DESC
");

// Dados para edição
$edicao_modelo = null;
if (isset($_GET["editar_modelo"])) {
    $id = intval($_GET["editar_modelo"]);
    $sql = "SELECT * FROM Modelos WHERE id_modelo=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $edicao_modelo = $stmt->get_result()->fetch_assoc();
}

$edicao_op = null;
if (isset($_GET["editar_op"])) {
    $id = intval($_GET["editar_op"]);
    $sql = "SELECT * FROM OrdensProducao WHERE id_op=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $edicao_op = $stmt->get_result()->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Produção - Motor Genesis</title>
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
        <a href="producao.php" class="active"><i class="fas fa-industry"></i> Produção</a>
        <a href="logistica.php"><i class="fas fa-truck"></i> Logística</a>
        <a href="orcamentos.php"><i class="fas fa-file-invoice"></i> Orçamentos</a>
        <a href="relatorios.php"><i class="fas fa-chart-bar"></i> Relatórios</a>
        <a href="usuarios.php"><i class="fas fa-users"></i> Usuários</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a>
    </div>
</div>

<div class="main">

<div class="topbar">
    <div style="display: flex; gap: 10px;">
        <button onclick="window.location.href='producao.php?nova=modelo'" class="btn btn-primary" style="cursor: pointer;">
            <i class="fas fa-plus"></i> Novo Modelo
        </button>
        <button onclick="window.location.href='producao.php?nova=op'" class="btn btn-primary" style="cursor: pointer;">
            <i class="fas fa-plus"></i> Nova OP
        </button>
    </div>
    <div class="user">
        <i class="fas fa-user-circle"></i> <?php echo $_SESSION["login"]; ?> |
        <span class="status-online"><i class="fas fa-check-circle"></i> Online</span>
    </div>
</div>

<h1><i class="fas fa-industry"></i> Produção</h1>

<?php if ($msg !== "") { ?>
    <div class="success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($msg); ?></div>
<?php } ?>

<?php if ($erro !== "") { ?>
    <div class="error"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($erro); ?></div>
<?php } ?>

<div>
    <?php if (isset($_GET["nova"]) && $_GET["nova"] === "modelo") { ?>
        <h2><i class="fas fa-cube"></i> <?php echo $edicao_modelo ? "Editar Modelo" : "Cadastrar Modelo"; ?></h2>
        <form method="POST" style="background: #262626; padding: 20px; border-radius: 8px; margin: 20px 0;">
            <input type="hidden" name="acao" value="<?php echo $edicao_modelo ? 'editar_modelo' : 'cadastrar_modelo'; ?>">
            <?php if ($edicao_modelo): ?>
                <input type="hidden" name="id_modelo" value="<?php echo $edicao_modelo['id_modelo']; ?>">
            <?php endif; ?>
            <div class="form-group">
                <label><i class="fas fa-tag"></i> Nome do Modelo</label>
                <input type="text" name="nome_modelo" placeholder="Nome do modelo" value="<?php echo $edicao_modelo['nome_modelo'] ?? ''; ?>" required>
            </div>
            <div class="form-group">
                <label><i class="fas fa-barcode"></i> Código (Opcional)</label>
                <input type="text" name="codigo_modelo" placeholder="Código" value="<?php echo $edicao_modelo['codigo_modelo'] ?? ''; ?>">
            </div>
            <div style="display: flex; gap: 10px; margin-top: 15px;">
                <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> <?php echo $edicao_modelo ? 'Atualizar' : 'Salvar'; ?></button>
                <a href="producao.php" class="btn btn-secondary"><i class="fas fa-times"></i> Cancelar</a>
            </div>
        </form>
    <?php } ?>

    <?php if (isset($_GET["nova"]) && $_GET["nova"] === "op") { ?>
        <h2><i class="fas fa-list"></i> <?php echo $edicao_op ? "Editar OP" : "Nova OP"; ?></h2>
        <form method="POST" style="background: #262626; padding: 20px; border-radius: 8px; margin: 20px 0;">
            <input type="hidden" name="acao" value="<?php echo $edicao_op ? 'editar_op' : 'cadastrar_op'; ?>">
            <?php if ($edicao_op): ?>
                <input type="hidden" name="id_op" value="<?php echo $edicao_op['id_op']; ?>">
            <?php endif; ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <div>
                    <label><i class="fas fa-cube"></i> Modelo</label>
                    <select name="id_modelo" required>
                        <option value="">-- Selecione --</option>
                        <?php $modelos->data_seek(0); while($m = $modelos->fetch_assoc()){ ?>
                            <option value="<?php echo $m["id_modelo"]; ?>" <?php echo ($edicao_op && $edicao_op['id_modelo'] == $m['id_modelo']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($m["nome_modelo"]); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <div>
                    <label><i class="fas fa-boxes"></i> Quantidade</label>
                    <input type="number" name="quantidade" min="1" placeholder="0" value="<?php echo $edicao_op['quantidade'] ?? ''; ?>" required>
                </div>
                <div>
                    <label><i class="fas fa-palette"></i> Cor</label>
                    <input type="text" name="cor" placeholder="Cor da moto" value="<?php echo $edicao_op['cor'] ?? ''; ?>">
                </div>
                <div>
                    <label><i class="fas fa-flag"></i> Prioridade</label>
                    <select name="prioridade">
                        <option value="ALTA" <?php echo ($edicao_op && $edicao_op['prioridade'] == 'ALTA') ? 'selected' : ''; ?>>ALTA</option>
                        <option value="MEDIA" <?php echo (!$edicao_op || $edicao_op['prioridade'] == 'MEDIA') ? 'selected' : ''; ?>>MÉDIA</option>
                        <option value="BAIXA" <?php echo ($edicao_op && $edicao_op['prioridade'] == 'BAIXA') ? 'selected' : ''; ?>>BAIXA</option>
                    </select>
                </div>
            </div>
            <div class="form-group" style="margin-top: 15px;">
                <label><i class="fas fa-file-alt"></i> Observações</label>
                <textarea name="observacoes" placeholder="Observações adicionais"><?php echo $edicao_op['observacoes'] ?? ''; ?></textarea>
            </div>
            <div style="display: flex; gap: 10px; margin-top: 15px;">
                <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> <?php echo $edicao_op ? 'Atualizar' : 'Criar'; ?> OP</button>
                <a href="producao.php" class="btn btn-secondary"><i class="fas fa-times"></i> Cancelar</a>
            </div>
        </form>
    <?php } else { ?>
        <div style="display: flex; gap: 10px;">
            <button onclick="window.location.href='producao.php?nova=modelo'" class="btn btn-primary" style="cursor: pointer;">
                <i class="fas fa-plus"></i> Novo Modelo
            </button>
            <button onclick="window.location.href='producao.php?nova=op'" class="btn btn-primary" style="cursor: pointer;">
                <i class="fas fa-plus"></i> Nova OP
            </button>
        </div>
    <?php } ?>
</div>

<h2><i class="fas fa-list-ol"></i> Modelos cadastrados</h2>
<table>
    <thead>
    <tr>
        <th>ID</th>
        <th>Nome</th>
        <th>Código</th>
        <th>Ações</th>
    </tr>
    </thead>
    <tbody>
    <?php while($lm = $modelosLista->fetch_assoc()){ ?>
        <tr>
            <td><?php echo $lm["id_modelo"]; ?></td>
            <td><?php echo htmlspecialchars($lm["nome_modelo"]); ?></td>
            <td><?php echo htmlspecialchars($lm["codigo_modelo"]); ?></td>
            <td>
                <a href="producao.php?nova=modelo&editar_modelo=<?php echo $lm['id_modelo']; ?>" class="btn btn-primary" style="font-size: 0.85em; padding: 6px 10px;">
                    <i class="fas fa-edit"></i> Editar
                </a>
                <form method="POST" style="display: inline;" onsubmit="return confirm('Excluir este modelo?');">
                    <input type="hidden" name="acao" value="excluir_modelo">
                    <input type="hidden" name="id_modelo" value="<?php echo $lm["id_modelo"]; ?>">
                    <button type="submit" class="btn btn-danger" style="font-size: 0.85em; padding: 6px 10px;">
                        <i class="fas fa-trash"></i> Excluir
                    </button>
                </form>
            </td>
        </tr>
    <?php } ?>
    </tbody>
</table>

<h2><i class="fas fa-cogs"></i> Em Produção</h2>
<table>
    <thead>
    <tr>
        <th>ID</th>
        <th>Modelo</th>
        <th>Quantidade</th>
        <th>Cor</th>
        <th>Prioridade</th>
        <th>Status</th>
        <th>Data Criação</th>
        <th>Ações</th>
    </tr>
    </thead>
    <tbody>
    <?php while($op = $emProducao->fetch_assoc()){ ?>
        <tr>
            <td><?php echo $op["id_op"]; ?></td>
            <td><?php echo htmlspecialchars($op["nome_modelo"]); ?></td>
            <td><?php echo $op["quantidade"]; ?></td>
            <td><?php echo htmlspecialchars($op["cor"]); ?></td>
            <td><span class="status-badge"><?php echo htmlspecialchars($op["prioridade"]); ?></span></td>
            <td><span class="status-badge" style="background: #ff9800;"><?php echo htmlspecialchars($op["status"]); ?></span></td>
            <td><?php echo date('d/m/Y H:i', strtotime($op["data_criacao"])); ?></td>
            <td>
                <a href="producao.php?nova=op&editar_op=<?php echo $op['id_op']; ?>" class="btn btn-primary" style="font-size: 0.85em; padding: 6px 10px;">
                    <i class="fas fa-edit"></i>
                </a>
                <?php if ($op["status"] === "PENDENTE") { ?>
                    <form method="POST" style="display:inline">
                        <input type="hidden" name="acao" value="iniciar_op">
                        <input type="hidden" name="id_op" value="<?php echo $op["id_op"]; ?>">
                        <button type="submit" class="btn btn-success" style="font-size: 0.85em; padding: 6px 10px;"><i class="fas fa-play"></i></button>
                    </form>
                <?php } ?>
                <?php if ($op["status"] === "INICIADA") { ?>
                    <form method="POST" style="display:inline">
                        <input type="hidden" name="acao" value="finalizar_op">
                        <input type="hidden" name="id_op" value="<?php echo $op["id_op"]; ?>">
                        <button type="submit" class="btn btn-success" style="font-size: 0.85em; padding: 6px 10px;"><i class="fas fa-check"></i></button>
                    </form>
                <?php } ?>
                <form method="POST" style="display:inline" onsubmit="return confirm('Excluir esta OP?');">
                    <input type="hidden" name="acao" value="excluir_op">
                    <input type="hidden" name="id_op" value="<?php echo $op["id_op"]; ?>">
                    <button type="submit" class="btn btn-danger" style="font-size: 0.85em; padding: 6px 10px;"><i class="fas fa-trash"></i></button>
                </form>
            </td>
        </tr>
    <?php } ?>
    </tbody>
</table>

<h2><i class="fas fa-check-circle"></i> Finalizadas</h2>
<table>
    <thead>
    <tr>
        <th>ID</th>
        <th>Modelo</th>
        <th>Quantidade</th>
        <th>Cor</th>
        <th>Prioridade</th>
        <th>Início</th>
        <th>Finalização</th>
        <th>Ações</th>
    </tr>
    </thead>
    <tbody>
    <?php while($op = $finalizadas->fetch_assoc()){ ?>
        <tr>
            <td><?php echo $op["id_op"]; ?></td>
            <td><?php echo htmlspecialchars($op["nome_modelo"]); ?></td>
            <td><?php echo $op["quantidade"]; ?></td>
            <td><?php echo htmlspecialchars($op["cor"]); ?></td>
            <td><span class="status-badge"><?php echo htmlspecialchars($op["prioridade"]); ?></span></td>
            <td><?php echo date('d/m/Y H:i', strtotime($op["data_inicio"])); ?></td>
            <td><?php echo date('d/m/Y H:i', strtotime($op["data_fim"])); ?></td>
            <td>
                <form method="POST" style="display:inline" onsubmit="return confirm('Excluir esta OP?');">
                    <input type="hidden" name="acao" value="excluir_op">
                    <input type="hidden" name="id_op" value="<?php echo $op["id_op"]; ?>">
                    <button type="submit" class="btn btn-danger" style="font-size: 0.85em; padding: 6px 10px;"><i class="fas fa-trash"></i></button>
                </form>
            </td>
        </tr>
    <?php } ?>
    </tbody>
</table>
</div>

</body>
</html>
