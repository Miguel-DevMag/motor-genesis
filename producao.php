<?php
include("seguranca.php");
include("conexao.php");

$msg = "";

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
                $msg = "Erro ao cadastrar modelo.";
            }
        } else {
            $msg = "Informe o nome do modelo.";
        }
    }
    if ($_POST["acao"] === "cadastrar_op") {
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
                $msg = "Erro ao criar OP.";
            }
        } else {
            $msg = "Selecione um modelo e informe a quantidade.";
        }
    }
    if ($_POST["acao"] === "iniciar_op") {
        $id_op = intval($_POST["id_op"] ?? 0);
        if ($id_op > 0) {
            $sql = "UPDATE OrdensProducao SET status='INICIADA', data_inicio=NOW() WHERE id_op=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id_op);
            $stmt->execute();
        }
    }
    if ($_POST["acao"] === "finalizar_op") {
        $id_op = intval($_POST["id_op"] ?? 0);
        if ($id_op > 0) {
            $sql = "UPDATE OrdensProducao SET status='FINALIZADA', data_fim=NOW() WHERE id_op=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id_op);
            $stmt->execute();
        }
    }
}

$modelos = $conn->query("SELECT id_modelo, nome_modelo FROM Modelos WHERE ativo=TRUE ORDER BY nome_modelo ASC");
$modelosLista = $conn->query("SELECT id_modelo, nome_modelo, codigo_modelo, ativo, criado_em FROM Modelos ORDER BY nome_modelo ASC");

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
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Produção - Motor Genesis</title>
<link rel="stylesheet" href="css/producao.css">
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
        <a href="#"><i class="fas fa-file-invoice"></i> Orçamentos</a>
        <a href="#"><i class="fas fa-chart-bar"></i> Relatórios</a>
        <a href="#"><i class="fas fa-users"></i> Usuários</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a>
    </div>
</div>

<div class="main">

<h1><i class="fas fa-industry"></i> Produção</h1>

<?php if ($msg !== "") { ?>
    <div class="success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($msg); ?></div>
<?php } ?>

<div>
    <button onclick="toggleForm('modelo')"><i class="fas fa-plus"></i> Cadastrar Modelo</button>
    <button onclick="toggleForm('op')"><i class="fas fa-plus"></i> Nova OP</button>
</div>

<?php if (isset($_GET["nova"]) && $_GET["nova"] === "modelo") { ?>
    <h2><i class="fas fa-cube"></i> Cadastrar Modelo</h2>
    <form method="POST" class="show">
        <input type="hidden" name="acao" value="cadastrar_modelo">
        <div class="form-group">
            <label><i class="fas fa-tag"></i> Nome do Modelo</label>
            <input type="text" name="nome_modelo" placeholder="Nome do modelo" required>
        </div>
        <div class="form-group">
            <label><i class="fas fa-barcode"></i> Código (Opcional)</label>
            <input type="text" name="codigo_modelo" placeholder="Código">
        </div>
        <button type="submit"><i class="fas fa-check"></i> Salvar Modelo</button>
        <a href="producao.php"><i class="fas fa-times"></i> Cancelar</a>
    </form>
<?php } ?>

<?php if (isset($_GET["nova"]) && $_GET["nova"] === "op") { ?>
    <h2><i class="fas fa-list"></i> Nova OP</h2>
    <form method="POST" class="show">
        <input type="hidden" name="acao" value="cadastrar_op">
        <div class="form-group">
            <label><i class="fas fa-cube"></i> Modelo</label>
            <select name="id_modelo" required>
                <option value="">-- Selecione --</option>
                <?php $modelos->data_seek(0); while($m = $modelos->fetch_assoc()){ ?>
                    <option value="<?php echo $m["id_modelo"]; ?>">
                        <?php echo htmlspecialchars($m["nome_modelo"]); ?>
                    </option>
                <?php } ?>
            </select>
        </div>
        <div class="form-group">
            <label><i class="fas fa-boxes"></i> Quantidade</label>
            <input type="number" name="quantidade" min="1" placeholder="0" required>
        </div>
        <div class="form-group">
            <label><i class="fas fa-palette"></i> Cor</label>
            <input type="text" name="cor" placeholder="Cor da moto">
        </div>
        <div class="form-group">
            <label><i class="fas fa-flag"></i> Prioridade</label>
            <select name="prioridade">
                <option value="ALTA">ALTA</option>
                <option value="MEDIA" selected>MÉDIA</option>
                <option value="BAIXA">BAIXA</option>
            </select>
        </div>
        <div class="form-group">
            <label><i class="fas fa-file-alt"></i> Observações</label>
            <textarea name="observacoes" placeholder="Observações adicionais"></textarea>
        </div>
        <button type="submit"><i class="fas fa-check"></i> Criar OP</button>
        <a href="producao.php"><i class="fas fa-times"></i> Cancelar</a>
    </form>
<?php } ?>

<h2><i class="fas fa-list-ol"></i> Modelos cadastrados</h2>
<table>
    <thead>
    <tr>
        <th>ID</th>
        <th>Nome</th>
        <th>Código</th>
        <th>Status</th>
        <th>Data de Criação</th>
    </tr>
    </thead>
    <tbody>
    <?php while($lm = $modelosLista->fetch_assoc()){ ?>
        <tr>
            <td><?php echo $lm["id_modelo"]; ?></td>
            <td><?php echo htmlspecialchars($lm["nome_modelo"]); ?></td>
            <td><?php echo htmlspecialchars($lm["codigo_modelo"]); ?></td>
            <td><span class="status-badge <?php echo $lm["ativo"] ? 'status-active' : 'status-inactive'; ?>"><?php echo ($lm["ativo"] ? "Ativo" : "Inativo"); ?></span></td>
            <td><?php echo date('d/m/Y', strtotime($lm["criado_em"])); ?></td>
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
            <td><span class="status-badge status-pending"><?php echo htmlspecialchars($op["status"]); ?></span></td>
            <td><?php echo date('d/m/Y', strtotime($op["data_criacao"])); ?></td>
            <td>
                <?php if ($op["status"] === "PENDENTE") { ?>
                    <form method="POST" style="display:inline">
                        <input type="hidden" name="acao" value="iniciar_op">
                        <input type="hidden" name="id_op" value="<?php echo $op["id_op"]; ?>">
                        <button type="submit" class="btn-success"><i class="fas fa-play"></i> Iniciar</button>
                    </form>
                <?php } ?>
                <?php if ($op["status"] === "INICIADA") { ?>
                    <form method="POST" style="display:inline">
                        <input type="hidden" name="acao" value="finalizar_op">
                        <input type="hidden" name="id_op" value="<?php echo $op["id_op"]; ?>">
                        <button type="submit" class="btn-success"><i class="fas fa-check"></i> Finalizar</button>
                    </form>
                <?php } ?>
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
        </tr>
    <?php } ?>
    </tbody>
</table>
</div>

<script>
function toggleForm(type) {
    window.location.href = 'producao.php?nova=' + type;
}
</script>

</body>
</html>
