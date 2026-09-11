<?php
session_start();
require 'app/config/conexao.php';

if (isset($_POST['login']) && $_POST['senha'] === 'admin123') {
    $_SESSION['admin_logado'] = true;
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin.php");
    exit;
}

if (!isset($_SESSION['admin_logado'])) {
    ?>
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login - ReportaAI</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="public/assets/css/admin.css">
    </head>
    <body class="login-body d-flex justify-content-center align-items-center vh-100">
        <div class="card login-card p-5" style="width: 380px;">
            <div class="text-center mb-4">
                <h3 class="fw-bold text-dark">Reporta<span class="brand-highlight">AI</span></h3>
                <p class="text-muted">Acesso Restrito</p>
            </div>
            <form method="post">
                <input type="password" name="senha" class="form-control mb-4" placeholder="Senha Administrativa" required>
                <button type="submit" name="login" class="btn btn-custom w-100">Autenticar</button>
            </form>
            <div class="text-center mt-3">
                <a href="index.php" class="text-muted small text-decoration-none"><i class="bi bi-arrow-left me-1"></i>Voltar para o Portal do Cidadão</a>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

if (isset($_POST['atualizar_status'])) {
    $stmt = $pdo->prepare("UPDATE chamados SET status = ? WHERE id = ?");
    $stmt->execute([$_POST['status'], $_POST['id']]);
    header("Location: admin.php");
    exit;
}

$filtro = $_GET['filtro_categoria'] ?? '';
$sql = "SELECT * FROM chamados";
$params = [];

if ($filtro) {
    $sql .= " WHERE categoria = ?";
    $params[] = $filtro;
}
$sql .= " ORDER BY data_criacao DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$chamados = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo - ReportaAI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="public/assets/css/admin.css">
</head>
<body>
    <nav class="navbar navbar-dark mb-4 py-3">
        <div class="container-fluid px-4 d-flex justify-content-between align-items-center">
            <span class="navbar-brand mb-0 h1"><i class="bi bi-shield-lock-fill brand-highlight me-2"></i>Reporta<span class="brand-highlight">AI</span></span>
            <div>
                <a href="index.php" class="btn btn-outline-light btn-sm px-3 rounded-pill me-2"><i class="bi bi-house-door me-1"></i>Portal do Cidadão</a>
                <a href="?logout=1" class="btn btn-outline-danger btn-sm px-3 rounded-pill">Encerrar Sessão</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-4 pb-5">
        <div class="row mb-4">
            <div class="col-12">
                <div class="card p-3">
                    <form action="admin.php" method="GET" class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label fw-medium text-muted mb-1">Filtrar Categoria</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-funnel"></i></span>
                                <select name="filtro_categoria" class="form-select border-start-0">
                                    <option value="">Exibir Todos os Relatos</option>
                                    <option value="Buraco na Via" <?= $filtro == 'Buraco na Via' ? 'selected' : '' ?>>Buraco na Via</option>
                                    <option value="Falta de Iluminação" <?= $filtro == 'Falta de Iluminação' ? 'selected' : '' ?>>Falta de Iluminação</option>
                                    <option value="Descarte Irregular de Lixo" <?= $filtro == 'Descarte Irregular de Lixo' ? 'selected' : '' ?>>Descarte Irregular de Lixo</option>
                                    <option value="Sinalização Danificada" <?= $filtro == 'Sinalização Danificada' ? 'selected' : '' ?>>Sinalização Danificada</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-dark w-100 btn-action"><i class="bi bi-search me-2"></i>Aplicar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Protocolo</th>
                            <th>Registro</th>
                            <th>Categoria</th>
                            <th>Localização</th>
                            <th>Evidência</th>
                            <th>Status Atual</th>
                            <th>Gestão</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($chamados as $c): ?>
                        <tr>
                            <td><span class="font-monospace fw-bold"><?= htmlspecialchars($c['protocolo']) ?></span></td>
                            <td class="text-muted"><i class="bi bi-clock me-1"></i><?= date('d/m/y H:i', strtotime($c['data_criacao'])) ?></td>
                            <td><span class="fw-medium"><?= htmlspecialchars($c['categoria']) ?></span></td>
                            <td><i class="bi bi-geo-alt me-1 text-muted"></i><?= htmlspecialchars($c['endereco']) ?></td>
                            <td>
                                <?php if ($c['foto']): ?>
                                    <button type="button" class="btn btn-sm btn-outline-dark btn-action" data-bs-toggle="modal" data-bs-target="#imageModal" data-bs-img="<?= htmlspecialchars($c['foto']) ?>">
                                        <i class="bi bi-image me-1"></i>Visualizar
                                    </button>
                                <?php else: ?>
                                    <span class="text-muted small"><i class="bi bi-camera-video-off me-1"></i>N/A</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                    $bg = 'bg-danger';
                                    if($c['status'] == 'Resolvido') $bg = 'bg-success';
                                    if($c['status'] == 'Em Andamento') $bg = 'bg-warning text-dark';
                                ?>
                                <span class="badge <?= $bg ?> badge-custom"><?= htmlspecialchars($c['status']) ?></span>
                            </td>
                            <td>
                                <form action="admin.php" method="POST" class="d-flex align-items-center">
                                    <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                    <select name="status" class="form-select form-select-sm me-2 w-auto" required>
                                        <option value="Pendente" <?= $c['status'] == 'Pendente' ? 'selected' : '' ?>>Pendente</option>
                                        <option value="Em Andamento" <?= $c['status'] == 'Em Andamento' ? 'selected' : '' ?>>Em Andamento</option>
                                        <option value="Resolvido" <?= $c['status'] == 'Resolvido' ? 'selected' : '' ?>>Resolvido</option>
                                    </select>
                                    <button type="submit" name="atualizar_status" class="btn btn-sm btn-dark btn-action"><i class="bi bi-arrow-repeat"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($chamados)): ?>
                        <tr><td colspan="7" class="text-center py-5 text-muted"><i class="bi bi-inbox fs-2 d-block mb-2"></i>Nenhum registro encontrado no sistema.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal de Visualização de Imagem -->
    <div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content bg-transparent border-0">
                <div class="modal-header border-0 pb-0 d-flex justify-content-end p-2">
                    <button type="button" class="btn btn-dark rounded-circle shadow-sm" data-bs-dismiss="modal" aria-label="Fechar" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border: 2px solid #ffd21f;">
                        <i class="bi bi-x-lg text-white"></i>
                    </button>
                </div>
                <div class="modal-body text-center pt-0">
                    <img src="" id="modalImageDisplay" class="img-fluid rounded shadow-lg" alt="Evidência do Chamado" style="max-height: 80vh; border: 1px solid rgba(255,255,255,0.1);">
                </div>
            </div>
        </div>
    </div>

    <!-- Script necessário para o Modal funcionar -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const imageModal = document.getElementById('imageModal');
        if (imageModal) {
            imageModal.addEventListener('show.bs.modal', event => {
                const button = event.relatedTarget;
                const imgSrc = button.getAttribute('data-bs-img');
                document.getElementById('modalImageDisplay').src = imgSrc;
            });
        }
    </script>
</body>
</html>