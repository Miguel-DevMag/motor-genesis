<?php 
session_start();

// se já estiver logado, encaminha direto para o painel
if (isset($_SESSION["id_usuario"])) {
    header("Location: dashboard.php");
    exit;
}
  
include("conexao.php");
$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $login = trim($_POST["login"]);
    $senha = trim($_POST["senha"]);

    if (empty($login) || empty($senha)) {
        $msg = "<div class='error'>Preencha todos os campos.</div>";
    } else {
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
                $msg = "<div class='error'>Senha incorreta!</div>";
            }

        } else {
            $msg = "<div class='error'>Usuário não encontrado!</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Motor Genesis</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
<div class="container">
    <div class="left">
        <div>
            <h1><i class="fas fa-motorcycle"></i> Motor Genesis</h1>
            <p>Sistema de Gerenciamento de Estoque</p>
        </div>
    </div>

    <div class="right">
        <div class="form-box">
            <h2><i class="fas fa-sign-in-alt"></i> Login</h2>
            <?php echo $msg; ?>
            <form method="POST">
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Email ou Matrícula</label>
                    <input type="text" name="login" placeholder="seu@email.com ou matrícula" required>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-lock"></i> Senha</label>
                    <input type="password" name="senha" placeholder="Digite sua senha" required>
                </div>
                <button type="submit"><i class="fas fa-arrow-right"></i> Entrar</button>
                <br><br>
                <div class="link-paginas">
                    <a href="recuperar.php"><i class="fas fa-key"></i> Esqueci minha senha</a>
                    <a href="cadastro.php"><i class="fas fa-user-plus"></i> Criar usuário</a>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>