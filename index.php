<?php
session_start();

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

require 'app/config/conexao.php';

$erro = false;

if (isset($_POST['entrar'])) {
    $usuario = trim($_POST['usuario']);
    $senha = trim($_POST['senha']);

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = ? AND senha = ?");
    $stmt->execute([$usuario, $senha]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $_SESSION['usuario_logado'] = $user['usuario'];
        $_SESSION['perfil_logado'] = $user['perfil'];

        if ($user['perfil'] === 'admin') {
            header("Location: admin.php");
            exit;
        } else {
            header("Location: cidadao.php");
            exit;
        }
    } else {
        $erro = true;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ReportaAI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="public/assets/css/admin.css">
</head>
<body class="login-body d-flex justify-content-center align-items-center vh-100">
    <div class="card login-card p-5" style="width: 400px;">
        <div class="text-center mb-4">
            <h3 class="fw-bold text-dark">Reporta<span class="brand-highlight">AI</span></h3>
            <p class="text-muted small">Acesse com sua conta</p>
        </div>

        <?php if ($erro): ?>
            <div class="alert alert-danger py-2 small text-center border-0 mb-3">
                <i class="bi bi-exclamation-triangle-fill me-1"></i> Usuário ou senha inválidos.
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label small fw-medium">Usuário</label>
                <input type="text" name="usuario" class="form-control" placeholder="Ex: cidadao ou admin" required>
            </div>
            <div class="mb-4">
                <label class="form-label small fw-medium">Senha</label>
                <input type="password" name="senha" class="form-control" placeholder="Sua senha" required>
            </div>
            <button type="submit" name="entrar" class="btn btn-custom w-100 py-2">Entrar no Sistema</button>
        </form>

        <div class="text-center mt-4">
            <span class="text-muted small d-block">Contas de Teste:</span>
            <span class="text-muted small">Cidadão: <strong>cidadao</strong> / <strong>123</strong></span><br>
            <span class="text-muted small">Admin: <strong>admin</strong> / <strong>123</strong></span>
        </div>
    </div>
</body>
</html>