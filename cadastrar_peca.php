<?php

include("seguranca.php");
include("conexao.php");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: estoque.php");
    exit;
}

$codigo = trim($_POST['codigo'] ?? '');
$nome = trim($_POST['nome'] ?? '');
$categoria = trim($_POST['categoria'] ?? '');
$lote = trim($_POST['lote'] ?? '');
$quantidade = intval($_POST['quantidade'] ?? 0);
$estoque_minimo = intval($_POST['estoque_minimo'] ?? 0);
$unidade = trim($_POST['unidade'] ?? '');
$preco = floatval($_POST['preco'] ?? 0);
$fornecedor = trim($_POST['fornecedor'] ?? '');
$localizacao = trim($_POST['localizacao'] ?? '');
$descricao = trim($_POST['descricao'] ?? '');

if (empty($codigo) || empty($nome)) {
    echo "Código e nome são obrigatórios.";
    exit;
}

$sql = "INSERT INTO Pecas 
(codigo_peca, nome_peca, categoria, lote, quantidade_estoque, estoque_minimo, unidade, custo_unitario, fornecedor, localizacao, descricao)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssiiissss", $codigo, $nome, $categoria, $lote, $quantidade, $estoque_minimo, $unidade, $preco, $fornecedor, $localizacao, $descricao);

if ($stmt->execute()) {
    header("Location: estoque.php");
} else {
    echo "Erro: " . $conn->error;
}

?>