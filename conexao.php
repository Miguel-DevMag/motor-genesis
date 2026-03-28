<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "localhost"; 
$user = "root";
$pass = "root";
$db = "montadora";

try {
    $conn = new mysqli($host, $user, $pass, $db);
    
    if ($conn->connect_error) {
        error_log("Erro ao conectar no banco de dados: " . $conn->connect_error);
        die("<div style='color: red; font-family: Arial; padding: 20px;'><h2>Erro de Conexão</h2><p>Não foi possível conectar ao banco de dados.</p><p><strong>Detalhes:</strong> " . htmlspecialchars($conn->connect_error) . "</p><p style='color: gray; font-size: 12px;'>Se o erro é 'No password supplied', configure a senha do MySQL root no arquivo conexao.php</p></div>");
    }
    
    $conn->set_charset("utf8mb4");
    
} catch (Exception $e) {
    error_log("Exceção ao conectar: " . $e->getMessage());
    die("<div style='color: red; font-family: Arial; padding: 20px;'><h2>Erro Fatal</h2><p>Não foi possível inicializar a conexão com o banco de dados.</p></div>");
}
?>