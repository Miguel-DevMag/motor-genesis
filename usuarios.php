<?php
include("seguranca.php");
include("conexao.php");

$msg = "";
$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $acao = $_POST["acao"] ?? "";
    
    if ($acao === "adicionar") {
        $nome = trim($_POST["nome"] ?? "");
        $email = trim($_POST["email"] ?? "");
        $login = trim($_POST["login"] ?? "");
        $senha = trim($_POST["senha"] ?? "");
        $matricula = trim($_POST["matricula"] ?? "");
        $perfil = trim($_POST["perfil"] ?? "usuario");
        
        if ($nome && $email && $login && $senha && $matricula) {
            // Verificar se login já existe (usando prepared statement para evitar SQL injection)
            $check_sql = "SELECT id_usuario FROM Usuarios WHERE login=?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("s", $login);
            $check_stmt->execute();
            $check = $check_stmt->get_result();
            $check_stmt->close();
            if ($check->num_rows > 0) {
                $erro = "Login já existe!";
            } else {
                $senha_hash = password_hash($senha, PASSWORD_BCRYPT);
                $sql = "INSERT INTO Usuarios (nome, email, login, senha, matricula, perfil) 
                        VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                if ($stmt) {
                    $stmt->bind_param("ssssss", $nome, $email, $login, $senha_hash, $matricula, $perfil);
                    if ($stmt->execute()) {
                        $msg = "Usuário adicionado com sucesso!";
                    } else {
                        $erro = "Erro ao adicionar: " . $stmt->error;
                    }
                    $stmt->close();
                }
            }
        } else {
            $erro = "Preencha todos os campos obrigatórios!";
        }
    }
    
    elseif ($acao === "editar") {
        $id = intval($_POST["id_usuario"] ?? 0);
        $nome = trim($_POST["nome"] ?? "");
        $email = trim($_POST["email"] ?? "");
        $login = trim($_POST["login"] ?? "");
        $matricula = trim($_POST["matricula"] ?? "");
        $perfil = trim($_POST["perfil"] ?? "usuario");
        $senha = trim($_POST["senha"] ?? "");
        
        if ($id > 0 && $nome && $email && $login && $matricula) {
            if ($senha) {
                // Atualizar incluindo senha
                $senha_hash = password_hash($senha, PASSWORD_BCRYPT);
                $sql = "UPDATE Usuarios SET nome=?, email=?, login=?, matricula=?, perfil=?, senha=? 
                        WHERE id_usuario=?";
                $stmt = $conn->prepare($sql);
                if ($stmt) {
                    $stmt->bind_param("ssssssi", $nome, $email, $login, $matricula, $perfil, $senha_hash, $id);
                    if ($stmt->execute()) {
                        $msg = "Usuário atualizado com sucesso!";
                    } else {
                        $erro = "Erro ao atualizar: " . $stmt->error;
                    }
                    $stmt->close();
                }
            } else {
                // Atualizar sem alterar senha
                $sql = "UPDATE Usuarios SET nome=?, email=?, login=?, matricula=?, perfil=? 
                        WHERE id_usuario=?";
                $stmt = $conn->prepare($sql);
                if ($stmt) {
                    $stmt->bind_param("sssssi", $nome, $email, $login, $matricula, $perfil, $id);
                    if ($stmt->execute()) {
                        $msg = "Usuário atualizado com sucesso!";
                    } else {
                        $erro = "Erro ao atualizar: " . $stmt->error;
                    }
                    $stmt->close();
                }
            }
        }
    }
    
    elseif ($acao === "excluir") {
        $id = intval($_POST["id_usuario"] ?? 0);
        if ($id > 0) {
            // Impedir exclusão de si mesmo
            $sql_check = "SELECT id_usuario FROM Usuarios WHERE login=?";
            $stmt = $conn->prepare($sql_check);
            $stmt->bind_param("s", $_SESSION["login"]);
            $stmt->execute();
            $current_user = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            
            if (!$current_user) {
                $erro = "Usuário atual não encontrado!";
            } elseif ($current_user['id_usuario'] == $id) {
                $erro = "Você não pode deletar sua própria conta!";
            } else {
                $sql = "DELETE FROM Usuarios WHERE id_usuario=?";
                $stmt = $conn->prepare($sql);
                if ($stmt) {
                    $stmt->bind_param("i", $id);
                    if ($stmt->execute()) {
                        $msg = "Usuário removido com sucesso!";
                    } else {
                        $erro = "Erro ao remover: " . $stmt->error;
                    }
                    $stmt->close();
                }
            }
        }
    }
}

// Buscar usuários
$busca = trim($_GET["busca"] ?? "");
if ($busca) {
    $termo = "%$busca%";
    $sql = "SELECT id_usuario, nome, email, login, matricula, perfil FROM Usuarios 
            WHERE nome LIKE ? OR email LIKE ? OR login LIKE ? ORDER BY id_usuario DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $termo, $termo, $termo);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $stmt->close();
} else {
    $sql = "SELECT id_usuario, nome, email, login, matricula, perfil FROM Usuarios ORDER BY id_usuario DESC";
    $resultado = $conn->query($sql);
}

// Dados para edição
$edicao = null;
if (isset($_GET["editar"])) {
    $id = intval($_GET["editar"]);
    $sql = "SELECT id_usuario, nome, email, login, matricula, perfil FROM Usuarios WHERE id_usuario=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $edicao = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Usuários - Motor Genesis</title>
<link rel="stylesheet" href="css/tema.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<div class="sidebar">
    <div class="logo">
        <img src="img/logo.png" alt="Motor Genesis">
        <h3>Motor Genesis</h3>
    </div>
    <div class="menu">
        <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="estoque.php"><i class="fas fa-boxes"></i> Estoque</a>
        <a href="producao.php"><i class="fas fa-industry"></i> Produção</a>
        <a href="logistica.php"><i class="fas fa-truck"></i> Logística</a>
        <a href="orcamentos.php"><i class="fas fa-file-invoice"></i> Orçamentos</a>
        <a href="relatorios.php"><i class="fas fa-chart-bar"></i> Relatórios</a>
        <a href="usuarios.php" class="active"><i class="fas fa-users"></i> Usuários</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a>
    </div>
</div>

<div class="main">
    <div class="topbar">
        <form method="GET" style="display: flex; gap: 10px; flex: 1;">
            <input type="text" name="busca" placeholder="Buscar usuários por nome, email ou login..." value="<?php echo htmlspecialchars($busca); ?>">
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Buscar</button>
            <?php if ($busca): ?>
                <a href="usuarios.php" class="btn btn-secondary"><i class="fas fa-times"></i> Limpar</a>
            <?php endif; ?>
        </form>
        <div class="user">
            <i class="fas fa-user-circle"></i> <?php echo $_SESSION["login"]; ?> |
            <span class="status-online"><i class="fas fa-check-circle"></i> Online</span>
        </div>
    </div>

    <h1><i class="fas fa-users"></i> Gerenciamento de Usuários</h1>

    <?php if ($msg): ?>
        <div class="success"><i class="fas fa-check-circle"></i> <?php echo $msg; ?></div>
    <?php endif; ?>
    
    <?php if ($erro): ?>
        <div class="error"><i class="fas fa-exclamation-circle"></i> <?php echo $erro; ?></div>
    <?php endif; ?>

    <form method="POST" style="margin-bottom: 30px;">
        <input type="hidden" name="acao" value="<?php echo $edicao ? 'editar' : 'adicionar'; ?>">
        <?php if ($edicao): ?>
            <input type="hidden" name="id_usuario" value="<?php echo $edicao['id_usuario']; ?>">
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
            <div>
                <label><i class="fas fa-user"></i> Nome *</label>
                <input type="text" name="nome" value="<?php echo htmlspecialchars($edicao['nome'] ?? ''); ?>" required>
            </div>
            <div>
                <label><i class="fas fa-envelope"></i> Email *</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($edicao['email'] ?? ''); ?>" required>
            </div>
            <div>
                <label><i class="fas fa-user-circle"></i> Login *</label>
                <input type="text" name="login" value="<?php echo htmlspecialchars($edicao['login'] ?? ''); ?>" required>
            </div>
            <div>
                <label><i class="fas fa-id-card"></i> Matrícula *</label>
                <input type="text" name="matricula" value="<?php echo htmlspecialchars($edicao['matricula'] ?? ''); ?>" required>
            </div>
            <?php if (!$edicao): ?>
            <div>
                <label><i class="fas fa-lock"></i> Senha *</label>
                <input type="password" name="senha" placeholder="Mínimo 6 caracteres" required>
            </div>
            <?php else: ?>
            <div>
                <label><i class="fas fa-lock"></i> Nova Senha (deixe em branco para não alterar)</label>
                <input type="password" name="senha" placeholder="Deixe em branco para manter a senha atual">
            </div>
            <?php endif; ?>
            <div>
                <label><i class="fas fa-shield-alt"></i> Perfil</label>
                <select name="perfil">
                    <option value="usuario" <?php echo (!$edicao || $edicao['perfil'] == 'usuario') ? 'selected' : ''; ?>>Usuário</option>
                    <option value="gerente" <?php echo ($edicao && $edicao['perfil'] == 'gerente') ? 'selected' : ''; ?>>Gerente</option>
                    <option value="admin" <?php echo ($edicao && $edicao['perfil'] == 'admin') ? 'selected' : ''; ?>>Administrador</option>
                </select>
            </div>
        </div>

        <div style="margin-top: 15px; display: flex; gap: 10px;">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-<?php echo $edicao ? 'edit' : 'plus'; ?>"></i>
                <?php echo $edicao ? 'Atualizar' : 'Adicionar'; ?> Usuário
            </button>
            <?php if ($edicao): ?>
                <a href="usuarios.php" class="btn btn-secondary"><i class="fas fa-times"></i> Cancelar</a>
            <?php endif; ?>
        </div>
    </form>

    <h2><i class="fas fa-list"></i> Usuários Cadastrados</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Email</th>
                <th>Login</th>
                <th>Matrícula</th>
                <th>Perfil</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($resultado && $resultado->num_rows > 0): ?>
                <?php while ($user = $resultado->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $user['id_usuario']; ?></td>
                    <td><?php echo htmlspecialchars($user['nome']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo htmlspecialchars($user['login']); ?></td>
                    <td><?php echo htmlspecialchars($user['matricula']); ?></td>
                    <td>
                        <span class="status-badge" style="background: <?php 
                            if ($user['perfil'] == 'admin') echo '#dc143c';
                            elseif ($user['perfil'] == 'gerente') echo '#ff9800';
                            else echo '#2196F3';
                        ?>;">
                            <?php echo ucfirst($user['perfil']); ?>
                        </span>
                    </td>
                    <td>
                        <a href="usuarios.php?editar=<?php echo $user['id_usuario']; ?>" class="btn btn-primary" style="font-size: 0.85em; padding: 6px 10px;">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form method="POST" style="display: inline;" onsubmit="return confirm('Excluir este usuário?');">
                            <input type="hidden" name="acao" value="excluir">
                            <input type="hidden" name="id_usuario" value="<?php echo $user['id_usuario']; ?>">
                            <button type="submit" class="btn btn-danger" style="font-size: 0.85em; padding: 6px 10px;">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" style="text-align: center; padding: 30px; color: #999;">
                        <i class="fas fa-inbox" style="font-size: 2em; display: block; margin-bottom: 10px;"></i>
                        Nenhum usuário cadastrado
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
