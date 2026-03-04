<?php
session_start();
include("conexao.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $login = trim($_POST["login"]);
    $senha = trim($_POST["senha"]);

    $sql = "SELECT * FROM usuarios WHERE email=? OR matricula=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $login, $login);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        if (password_verify($senha, $user["senha"])) {
            $_SESSION["usuario"] = $user["nome"];
            header("Location: dashboard.php");
            exit;
        } else {
            echo "Senha incorreta!";
        }
    } else {
        echo "Usuário não encontrado!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - Montadora</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container">
    <div class="left"></div>

    <div class="right">
        <div class="form-box">
            <h2>Login</h2>
            <form method="POST">
                <input type="text" name="login" placeholder="Email ou Matrícula" required>
                <input type="password" name="senha" placeholder="Senha" required>
                <button type="submit">Entrar</button>
                <br><br>
                <a href="recuperar.php">Esqueci minha senha</a><br>
                <a href="cadastro.php">Criar conta</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>