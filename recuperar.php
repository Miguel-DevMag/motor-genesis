<?php
session_start();
include("conexao.php");
$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST["email"]);

    $novaSenha = password_hash("123456", PASSWORD_DEFAULT);

    $sql = "UPDATE Usuarios SET senha=? WHERE email=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $novaSenha, $email);

    if ($stmt->execute()) {
        $msg = "<div class='success'>Senha redefinida para 123456. Altere após login.</div>";
    } else {
        $msg = "<div class='error'>Erro ao redefinir senha.</div>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Recuperar Senha - Montadora</title>
    <link rel="stylesheet" href="css/senha.css">
</head>
<body>

<div class="container">

    <div class="left"></div>

    <div class="right">
        <div class="form-box">
            <h2>Recuperar Senha</h2>

            <?php echo $msg; ?>

            <form method="POST">

                <div class="form-group">
                    <label>Email Cadastrado</label>
                    <input type="email" name="email" required>
                </div>

                <button type="submit">Redefinir Senha</button>

                <div class="links">
                    <a href="index.php">Voltar ao Login</a>
                </div>

            </form>
        </div>
    </div>

</div>

</body>
</html>