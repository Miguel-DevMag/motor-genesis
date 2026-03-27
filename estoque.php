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
<title>Estoque</title>
<link rel="stylesheet" href="css/estoque.css">
</head>

<body>

<div class="sidebar">
    <div class="logo">
        <img src="img/logo.png" alt="logo">
    </div>

    <div class="menu">
        <a href="dashboard.php">Dashboard</a>
        <a href="#">Estoque</a>
        <a href="producao.php">Produção</a>
        <a href="logistica.php">Logística</a>
        <a href="#">Orçamentos</a>
        <a href="#">Relatórios</a>
        <a href="#">Usuários</a>
        <a href="logout.php">Sair</a>
    </div>
</div>

<div class="main">

<div class="topbar">
<input type="text" class="search" placeholder="Buscar peças...">

<div class="user">
<?php echo $_SESSION["login"]; ?> |
<span class="status-online">● Sistema Online</span>
</div>
</div>

<h1>Gestão de Estoque</h1>
<h4>Controle inteligente de peças</h4>

<!-- BOTÃO NOVA PEÇA -->
<form method="GET">
<button type="submit" name="nova">+ Nova Peça</button>
</form>

<?php
if(isset($_GET['nova'])){
?>

<!-- FORMULÁRIO (SÓ APARECE DEPOIS DE CLICAR) -->
<form action="cadastrar_peca.php" method="POST">

<input type="text" name="codigo" placeholder="Código" required>

<input type="text" name="nome" placeholder="Nome" required>

<input type="text" name="categoria" placeholder="Categoria">

<input type="text" name="lote" placeholder="Lote">

<input type="number" name="quantidade">

<input type="number" name="estoque_minimo">

<input type="text" name="unidade">

<input type="number" step="0.01" name="preco">

<input type="text" name="fornecedor">

<input type="text" name="localizacao">

<textarea name="descricao"></textarea>

<br><br>

<button type="submit">Cadastrar</button>

</form>

<?php
}
?>

<h2>Peças cadastradas</h2>

<table border="1">

<tr>
<th>Código</th>
<th>Nome</th>
<th>Categoria</th>
<th>Quantidade</th>
<th>Preço</th>
</tr>

<?php
while($linha = $resultado->fetch_assoc()){
?>

<tr>
<td><?php echo $linha['codigo_peca']; ?></td>
<td><?php echo $linha['nome_peca']; ?></td>
<td><?php echo $linha['categoria']; ?></td>
<td><?php echo $linha['quantidade_estoque']; ?></td>
<td><?php echo $linha['custo_unitario']; ?></td>
</tr>

<?php
}
?>

</table>

</div>

</body>
</html>