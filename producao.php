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
<title>Produção</title>
<link rel="stylesheet" href="css/producao.css">
</head>
<body>

<div class="sidebar">
    <div class="logo">
        <img src="img/logo.png" alt="logo">
    </div>

    <div class="menu">
        <a href="dashboard.php">Dashboard</a>
        <a href="estoque.php">Estoque</a>
        <a href="#">Produção</a>
        <a href="logistica.php">Logística</a>
        <a href="#">Orçamentos</a>
        <a href="#">Relatórios</a>
        <a href="#">Usuários</a>
        <a href="logout.php">Sair</a>
    </div>
</div>

<div class="main">

<h1>Produção</h1>

<?php if ($msg !== "") { ?>
    <div><?php echo htmlspecialchars($msg); ?></div>
<?php } ?>

<form method="GET" style="display:inline">
    <button type="submit" name="nova" value="modelo">+ Cadastrar Modelo</button>
    <button type="submit" name="nova" value="op">+ Nova OP</button>
    <?php if (isset($_GET["nova"])) { ?>
        <a href="producao.php">Ocultar</a>
    <?php } ?>
    </form>

<?php if (isset($_GET["nova"]) && $_GET["nova"] === "modelo") { ?>
    <h2>Cadastrar Modelo</h2>
    <form method="POST">
        <input type="hidden" name="acao" value="cadastrar_modelo">
        <input type="text" name="nome_modelo" placeholder="Nome do Modelo" required>
        <input type="text" name="codigo_modelo" placeholder="Código (opcional)">
        <button type="submit">Salvar Modelo</button>
    </form>
<?php } ?>

<?php if (isset($_GET["nova"]) && $_GET["nova"] === "op") { ?>
    <h2>Nova OP</h2>
    <form method="POST">
        <input type="hidden" name="acao" value="cadastrar_op">
        <label>Modelo</label>
        <select name="id_modelo" required>
            <option value="">Selecione</option>
            <?php while($m = $modelos->fetch_assoc()){ ?>
                <option value="<?php echo $m["id_modelo"]; ?>">
                    <?php echo htmlspecialchars($m["nome_modelo"]); ?>
                </option>
            <?php } ?>
        </select>
        <label>Quantidade</label>
        <input type="number" name="quantidade" min="1" required>
        <label>Cor</label>
        <input type="text" name="cor" placeholder="Cor">
        <label>Prioridade</label>
        <select name="prioridade">
            <option value="ALTA">ALTA</option>
            <option value="MEDIA" selected>MEDIA</option>
            <option value="BAIXA">BAIXA</option>
        </select>
        <label>Observações</label>
        <textarea name="observacoes" rows="3"></textarea>
        <button type="submit">Criar OP</button>
    </form>
<?php } ?>

<h2>Modelos cadastrados</h2>
<table border="1" cellpadding="6" cellspacing="0">
    <tr>
        <th>ID</th>
        <th>Nome</th>
        <th>Código</th>
        <th>Ativo</th>
        <th>Criado em</th>
    </tr>
    <?php while($lm = $modelosLista->fetch_assoc()){ ?>
        <tr>
            <td><?php echo $lm["id_modelo"]; ?></td>
            <td><?php echo htmlspecialchars($lm["nome_modelo"]); ?></td>
            <td><?php echo htmlspecialchars($lm["codigo_modelo"]); ?></td>
            <td><?php echo ($lm["ativo"] ? "Sim" : "Não"); ?></td>
            <td><?php echo $lm["criado_em"]; ?></td>
        </tr>
    <?php } ?>
</table>

<h2>Em Produção</h2>
<table border="1" cellpadding="6" cellspacing="0">
    <tr>
        <th>ID</th>
        <th>Modelo</th>
        <th>Qtd</th>
        <th>Cor</th>
        <th>Prioridade</th>
        <th>Status</th>
        <th>Ações</th>
    </tr>
    <?php while($op = $emProducao->fetch_assoc()){ ?>
        <tr>
            <td><?php echo $op["id_op"]; ?></td>
            <td><?php echo htmlspecialchars($op["nome_modelo"]); ?></td>
            <td><?php echo $op["quantidade"]; ?></td>
            <td><?php echo htmlspecialchars($op["cor"]); ?></td>
            <td><?php echo htmlspecialchars($op["prioridade"]); ?></td>
            <td><?php echo htmlspecialchars($op["status"]); ?></td>
            <td>
                <?php if ($op["status"] === "PENDENTE") { ?>
                    <form method="POST" style="display:inline">
                        <input type="hidden" name="acao" value="iniciar_op">
                        <input type="hidden" name="id_op" value="<?php echo $op["id_op"]; ?>">
                        <button type="submit">Iniciar</button>
                    </form>
                <?php } ?>
                <?php if ($op["status"] === "INICIADA") { ?>
                    <form method="POST" style="display:inline">
                        <input type="hidden" name="acao" value="finalizar_op">
                        <input type="hidden" name="id_op" value="<?php echo $op["id_op"]; ?>">
                        <button type="submit">Finalizar</button>
                    </form>
                <?php } ?>
            </td>
        </tr>
    <?php } ?>
</table>

<h2>Finalizadas</h2>
<table border="1" cellpadding="6" cellspacing="0">
    <tr>
        <th>ID</th>
        <th>Modelo</th>
        <th>Qtd</th>
        <th>Cor</th>
        <th>Prioridade</th>
        <th>Início</th>
        <th>Fim</th>
    </tr>
    <?php while($op = $finalizadas->fetch_assoc()){ ?>
        <tr>
            <td><?php echo $op["id_op"]; ?></td>
            <td><?php echo htmlspecialchars($op["nome_modelo"]); ?></td>
            <td><?php echo $op["quantidade"]; ?></td>
            <td><?php echo htmlspecialchars($op["cor"]); ?></td>
            <td><?php echo htmlspecialchars($op["prioridade"]); ?></td>
            <td><?php echo $op["data_inicio"]; ?></td>
            <td><?php echo $op["data_fim"]; ?></td>
        </tr>
    <?php } ?>
</table>
</div>
</body>
</html>
