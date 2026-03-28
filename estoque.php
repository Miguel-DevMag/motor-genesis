<?php
include("seguranca.php");
include("conexao.php");

$sql = "SELECT * FROM Pecas ORDER BY id_peca DESC";
$resultado = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gestão de Estoque - Motor Genesis</title>
<link rel="stylesheet" href="css/estoque.css">
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
        <a href="#"><i class="fas fa-file-invoice"></i> Orçamentos</a>
        <a href="#"><i class="fas fa-chart-bar"></i> Relatórios</a>
        <a href="#"><i class="fas fa-users"></i> Usuários</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a>
    </div>
</div>

<div class="main">

<div class="topbar">
<input type="text" class="search" placeholder="Buscar peças...">

<div class="user">
<i class="fas fa-user-circle"></i> <?php echo $_SESSION["login"]; ?> |
<span class="status-online"><i class="fas fa-check-circle"></i> Sistema Online</span>
</div>
</div>

<h1><i class="fas fa-boxes"></i> Gestão de Estoque</h1>
<h4>Controle inteligente de peças</h4>

<!-- BOTÃO NOVA PEÇA -->
<button onclick="toggleForm()"><i class="fas fa-plus"></i> Nova Peça</button>

<!-- FORMULÁRIO (SÓ APARECE DEPOIS DE CLICAR) -->
<form id="formPeca" action="cadastrar_peca.php" method="POST">

<div class="form-group">
    <label><i class="fas fa-barcode"></i> Código</label>
    <input type="text" name="codigo" placeholder="Código da peça" required>
</div>

<div class="form-group">
    <label><i class="fas fa-tag"></i> Nome</label>
    <input type="text" name="nome" placeholder="Nome da peça" required>
</div>

<div class="form-group">
    <label><i class="fas fa-list"></i> Categoria</label>
    <input type="text" name="categoria" placeholder="Ex: Pneus, Correntes, Motor...">
</div>

<div class="form-group">
    <label><i class="fas fa-cube"></i> Lote</label>
    <input type="text" name="lote" placeholder="Número do lote">
</div>

<div class="form-group">
    <label><i class="fas fa-boxes"></i> Quantidade</label>
    <input type="number" name="quantidade" placeholder="0">
</div>

<div class="form-group">
    <label><i class="fas fa-bell"></i> Estoque Mínimo</label>
    <input type="number" name="estoque_minimo" placeholder="0">
</div>

<div class="form-group">
    <label><i class="fas fa-ruler"></i> Unidade</label>
    <input type="text" name="unidade" placeholder="Un, Kg, m...">
</div>

<div class="form-group">
    <label><i class="fas fa-money-bill"></i> Preço Unitário</label>
    <input type="number" step="0.01" name="preco" placeholder="0.00">
</div>

<div class="form-group">
    <label><i class="fas fa-building"></i> Fornecedor</label>
    <input type="text" name="fornecedor" placeholder="Nome do fornecedor">
</div>

<div class="form-group">
    <label><i class="fas fa-map-pin"></i> Localização</label>
    <input type="text" name="localizacao" placeholder="Prateleira, Galpão...">
</div>

<div class="form-group">
    <label><i class="fas fa-file-alt"></i> Descrição</label>
    <textarea name="descricao" placeholder="Descrição da peça"></textarea>
</div>

<button type="submit"><i class="fas fa-check"></i> Cadastrar</button>
<button type="button" onclick="toggleForm()"><i class="fas fa-times"></i> Cancelar</button>

</form>

<h2><i class="fas fa-list-ol"></i> Peças cadastradas</h2>

<table>

<thead>
<tr>
<th>Código</th>
<th>Nome</th>
<th>Categoria</th>
<th>Quantidade</th>
<th>Preço Unit.</th>
<th>Valor Total</th>
<th>Fornecedor</th>
<th>Ações</th>
</tr>
</thead>

<tbody>
<?php
while($linha = $resultado->fetch_assoc()){
    $valor_total = $linha['quantidade_estoque'] * $linha['custo_unitario'];
?>

<tr>
<td><?php echo $linha['codigo_peca']; ?></td>
<td><?php echo $linha['nome_peca']; ?></td>
<td><?php echo $linha['categoria']; ?></td>
<td><?php echo $linha['quantidade_estoque']; ?></td>
<td>R$ <?php echo number_format($linha['custo_unitario'], 2, ",", "."); ?></td>
<td>R$ <?php echo number_format($valor_total, 2, ",", "."); ?></td>
<td><?php echo $linha['fornecedor']; ?></td>
<td>
    <button class="btn-small"><i class="fas fa-edit"></i> Editar</button>
    <button class="btn-small btn-danger"><i class="fas fa-trash"></i> Excluir</button>
</td>
</tr>

<?php
}
?>
</tbody>

</table>

</div>

<script>
function toggleForm() {
    const form = document.getElementById('formPeca');
    form.classList.toggle('show');
}
</script>

</body>
</html>