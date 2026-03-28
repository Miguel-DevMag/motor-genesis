<?php
include("conexao.php");

echo "<h1>Teste de Conexão e Estrutura do Banco</h1>";

// Verificar conexão
if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}
echo "<p style='color: #00c853;'><strong>✓ Conexão bem-sucedida</strong></p>";

// Verificar tabelas
$tables = array('Usuarios', 'Pecas', 'Modelos', 'OrdensProducao', 'Transportadoras', 'Envios', 'Orcamentos');

echo "<h2>Estrutura das Tabelas</h2>";
foreach ($tables as $table) {
    $result = $conn->query("DESCRIBE $table");
    if ($result) {
        echo "<h3>Tabela: $table</h3>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "</tr>";
        }
        echo "</table><br>";
    } else {
        echo "<p style='color: #ff6b6b;'><strong>✗ Tabela $table não encontrada: " . $conn->error . "</strong></p>";
    }
}

// Verificar dados de exemplo
echo "<h2>Dados de Exemplo</h2>";

$pecas = $conn->query("SELECT COUNT(*) as total FROM Pecas");
$pecas_data = $pecas->fetch_assoc();
echo "<p>Total de Peças: <strong>" . $pecas_data['total'] . "</strong></p>";

$usuarios = $conn->query("SELECT COUNT(*) as total FROM Usuarios");
$usuarios_data = $usuarios->fetch_assoc();
echo "<p>Total de Usuários: <strong>" . $usuarios_data['total'] . "</strong></p>";

$ordensproducao = $conn->query("SELECT COUNT(*) as total FROM OrdensProducao");
$ordensproducao_data = $ordensproducao->fetch_assoc();
echo "<p>Total de Ordens de Produção: <strong>" . $ordensproducao_data['total'] . "</strong></p>";

// Verificar páginas criadas
echo "<h2>Páginas Criadas</h2>";
$files = array('orcamentos.php', 'relatorios.php', 'usuarios.php', 'tema.css');
$path = __DIR__;
foreach ($files as $file) {
    if (file_exists("$path/$file") || file_exists("$path/css/$file")) {
        echo "<p style='color: #00c853;'><strong>✓</strong> $file</p>";
    } else {
        echo "<p style='color: #ff6b6b;'><strong>✗</strong> $file não encontrado</p>";
    }
}

echo "<h2>CSS Integrado</h2>";
$css_files = array('index.php', 'dashboard.php', 'estoque.php', 'producao.php', 'logistica.php', 'orcamentos.php', 'relatorios.php', 'usuarios.php', 'cadastro.php', 'recuperar.php');
foreach ($css_files as $file) {
    if (file_exists("$path/$file")) {
        $content = file_get_contents("$path/$file");
        if (strpos($content, 'css/tema.css') !== false) {
            echo "<p style='color: #00c853;'><strong>✓</strong> $file usa tema.css</p>";
        } else {
            echo "<p style='color: #ff9800;'><strong>⚠</strong> $file não usa tema.css</p>";
        }
    }
}

?>
<style>
    body {
        background: #1a1a1a;
        color: white;
        font-family: Segoe UI, sans-serif;
        padding: 20px;
    }
    h1, h2 { color: #dc143c; }
    table { background: #262626; border-color: #404040; }
    td, th { border-color: #404040; }
</style>
