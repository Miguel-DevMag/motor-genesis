<?php
session_start();
include("conexao.php");
$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST["email"]);

    // Verificar se email existe
    $sql_check = "SELECT id_usuario FROM Usuarios WHERE email=?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("s", $email);
    $stmt_check->execute();
    $result = $stmt_check->get_result();

    if ($result->num_rows == 1) {
        // Gerar nova senha aleatória
        $novaSenha = bin2hex(random_bytes(4)); // 8 caracteres hex
        $hash = password_hash($novaSenha, PASSWORD_DEFAULT);

        $sql = "UPDATE Usuarios SET senha=? WHERE email=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $hash, $email);

        if ($stmt->execute()) {
            $msg = "<div class='success'>Nova senha gerada: <strong>$novaSenha</strong><br>Altere após login.</div>";
        } else {
            $msg = "<div class='error'>Erro ao redefinir senha.</div>";
        }
    } else {
        $msg = "<div class='error'>Email não encontrado.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Senha - Motor Genesis</title>
    <link rel="stylesheet" href="css/senha.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<div class="form-box">
    <h2><i class="fas fa-key"></i> Recuperar Senha</h2>

    <?php echo $msg; ?>

    <form method="POST">

        <div class="form-group">
            <label><i class="fas fa-envelope"></i> Email Cadastrado</label>
            <input type="email" name="email" placeholder="seu@email.com" required>
        </div>

        <button type="submit"><i class="fas fa-redo"></i> Redefinir Senha</button>

        <div class="links">
            <a href="index.php"><i class="fas fa-arrow-left"></i> Voltar ao Login</a>
        </div>

    </form>
</div>

</body>
</html>