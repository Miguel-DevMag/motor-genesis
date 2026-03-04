<?php
session_start();

// se já estiver logado, encaminha direto para o painel
if (isset($_SESSION["id_usuario"])) {
    header("Location: dashboard.php");
    exit;
}
 
include("conexao.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $login = trim($_POST["login"]);
    $senha = trim($_POST["senha"]);

    $sql = "SELECT * FROM Usuarios 
            WHERE (email=? OR matricula=?) 
            AND ativo=TRUE";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $login, $login);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {

        $user = $result->fetch_assoc();

        if (password_verify($senha, $user["senha"])) {

            $_SESSION["id_usuario"] = $user["id_usuario"];
            $_SESSION["login"] = $user["login"];
            $_SESSION["nivel"] = $user["nivel_acesso"];

            $update = $conn->prepare("UPDATE Usuarios SET ultimo_login=NOW() WHERE id_usuario=?");
            $update->bind_param("i", $user["id_usuario"]);
            $update->execute();

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
            <h2 class="titulo">Login</h2>
            <form method="POST">
                <input type="text" name="login" placeholder="Email ou Matrícula" required>
                <input type="password" name="senha" placeholder="Senha" required>
                <button type="submit">Entrar</button>
                <br><br>
                <div class="link-paginas">

                    <a href="recuperar.php">Esqueci minha senha</a>
             
                <div>
                    <a href="cadastro.php">Criar usuário</a>
                </div>
                   </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>