<?php
include("conexao.php");
$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $login = trim($_POST["login"]);
    $email = trim($_POST["email"]);
    $matricula = trim($_POST["matricula"]);
    $nivel = $_POST["nivel"];
    $senha = password_hash(trim($_POST["senha"]), PASSWORD_DEFAULT);

    $sql = "INSERT INTO Usuarios 
            (login, email, matricula, senha, nivel_acesso)
            VALUES (?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $login, $email, $matricula, $senha, $nivel);

    if ($stmt->execute()) {
        $msg = "<div class='success'>Usuário criado com sucesso!</div>";
    } else {
        $msg = "<div class='error'>Erro ao criar usuário.</div>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Cadastro - Montadora</title>
    <link rel="stylesheet" href="css/cadastro.css">
</head>
<body>
<div class="container">

    <div class="left"></div>

    <div class="right">
        <div class="form-box">
            <h2>Criar Usuário</h2>

            <?php echo $msg; ?>

            <form method="POST">

                <div class="form-group">
                    <label>Login</label>
                    <input type="text" name="login" required>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>

                <div class="form-group">
                    <label>Matrícula</label>
                    <input type="text" name="matricula" required>
                </div>

                <div class="form-group">
                    <label>Nível de Acesso</label>
                    <select name="nivel" required>
                        <option value="ADMIN">ADMIN</option>
                        <option value="OPERADOR">OPERADOR</option>
                        <option value="VISUALIZADOR">VISUALIZADOR</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Senha</label>
                    <input type="password" name="senha" required>
                </div>

                <button type="submit">Cadastrar</button>

                <div class="links">
                    <a href="login.php">Voltar para Login</a>
                </div>

            </form>
        </div>
    </div>

</div>
</body>
</html>