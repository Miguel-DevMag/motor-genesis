<?php

include("conexao.php");

$codigo = $_POST['codigo'];
$nome = $_POST['nome'];
$categoria = $_POST['categoria'];
$lote = $_POST['lote'];
$quantidade = $_POST['quantidade'];
$estoque_minimo = $_POST['estoque_minimo'];
$unidade = $_POST['unidade'];
$preco = $_POST['preco'];
$fornecedor = $_POST['fornecedor'];
$localizacao = $_POST['localizacao'];
$descricao = $_POST['descricao'];

$sql = "INSERT INTO Pecas 
(codigo_peca, nome_peca, categoria, lote, quantidade_estoque, estoque_minimo, unidade, custo_unitario, fornecedor, localizacao, descricao)
VALUES
('$codigo','$nome','$categoria','$lote','$quantidade','$estoque_minimo','$unidade','$preco','$fornecedor','$localizacao','$descricao')";

if($conn->query($sql)){
header("Location: estoque.php");
}else{
echo "Erro: ".$conn->error;
}

?>