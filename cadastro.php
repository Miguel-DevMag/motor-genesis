<?php
session_start();
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
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - Motor Genesis</title>
    <link rel="stylesheet" href="css/cadastro.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
<div class="container">

    <div class="left">
        <div>
            <h1><i class="fas fa-motorcycle"></i> Motor Genesis</h1>
            <p>Novo Usuário</p>
        </div>
    </div>

    <div class="right">
        <div class="form-box">
            <h2><i class="fas fa-user-plus"></i> Criar Usuário</h2>

            <?php echo $msg; ?>

            <form method="POST">

                <div class="form-group">
                    <label><i class="fas fa-user"></i> Login</label>
                    <input type="text" name="login" placeholder="Nome de usuário" required>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-envelope"></i> Email</label>
                    <input type="email" name="email" placeholder="seu@email.com" required>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-id-card"></i> Matrícula</label>
                    <input type="text" name="matricula" placeholder="Matrícula" required>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-shield-alt"></i> Nível de Acesso</label>
                    <select name="nivel" required>
                        <option value="">-- Selecione --</option>
                        <option value="ADMIN">ADMIN</option>
                        <option value="OPERADOR">OPERADOR</option>
                        <option value="VISUALIZADOR">VISUALIZADOR</option>
                    </select>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-lock"></i> Senha</label>
                    <input type="password" name="senha" placeholder="Crie uma senha forte" required>
                </div>

                <button type="submit"><i class="fas fa-check"></i> Cadastrar</button>

                <div class="links">
                    <a href="index.php"><i class="fas fa-arrow-left"></i> Voltar para Login</a>
                </div>

            </form>
        </div>
    </div>

</div>
</body>
</html>