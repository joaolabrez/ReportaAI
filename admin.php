<?php
session_start();

if (!isset($_SESSION['usuario_logado']) || $_SESSION['perfil_logado'] !== 'admin') {
    header("Location: index.php");
    exit;
}

require 'app/config/conexao.php';

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

if (isset($_POST['atualizar_status'])) {
    $stmt = $pdo->prepare("UPDATE chamados SET status = ? WHERE id = ?");
    $stmt->execute([$_POST['status'], $_POST['id']]);
    header("Location: admin.php");
    exit;
}

$filtro = $_GET['filtro_categoria'] ?? '';
$termoBusca = $_GET['termo'] ?? '';

$sql = "SELECT * FROM chamados WHERE 1=1";
$params = [];

if ($filtro) {
    $sql .= " AND categoria = ?";
    $params[] = $filtro;
}

if ($termoBusca) {
    $sql .= " AND (protocolo LIKE ? OR endereco LIKE ?)";
    $params[] = "%$termoBusca%";
    $params[] = "%$termoBusca%";
}

$sql .= " ORDER BY data_criacao DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$chamados = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalPendentes = $pdo->query("SELECT COUNT(*) FROM chamados WHERE status = 'Pendente'")->fetchColumn();
$totalAndamento = $pdo->query("SELECT COUNT(*) FROM chamados WHERE status = 'Em Andamento'")->fetchColumn();
$totalResolvidos = $pdo->query("SELECT COUNT(*) FROM chamados WHERE status = 'Resolvido'")->fetchColumn();

$statsCategorias = $pdo->query("SELECT categoria, COUNT(*) as total FROM chamados GROUP BY categoria")->fetchAll(PDO::FETCH_ASSOC);
$catLabels = [];
$catData = [];
foreach($statsCategorias as $sc) {
    $catLabels[] = $sc['categoria'];
    $catData[] = $sc['total'];
}
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
            <span class="navbar-brand mb-0 h1"><i class="bi bi-shield-lock-fill brand-highlight me-2"></i>Reporta<span class="brand-highlight">AI</span> Workspace</span>
            <div>
                <span class="text-white small me-3">Admin: <strong><?= htmlspecialchars($_SESSION['usuario_logado']) ?></strong></span>
                <a href="?logout=1" class="btn btn-outline-danger btn-sm px-3 rounded-pill">Sair</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-4 pb-5">
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card p-3 bg-white border-start border-danger border-4">
                    <span class="text-muted small">Pendentes</span>
                    <h3 class="fw-bold mb-0 text-danger"><?= $totalPendentes ?></h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-3 bg-white border-start border-warning border-4">
                    <span class="text-muted small">Em Andamento</span>
                    <h3 class="fw-bold mb-0 text-warning"><?= $totalAndamento ?></h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-3 bg-white border-start border-success border-4">
                    <span class="text-muted small">Resolvidos</span>
                    <h3 class="fw-bold mb-0 text-success"><?= $totalResolvidos ?></h3>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-lg-8 mb-3 mb-lg-0">
                <div class="card p-3">
                    <form action="admin.php" method="GET" class="row g-3 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label fw-medium text-muted mb-1">Filtrar Categoria</label>
                            <select name="filtro_categoria" class="form-select">
                                <option value="">Todas as Categorias</option>
                                <option value="Buraco na Via" <?= $filtro == 'Buraco na Via' ? 'selected' : '' ?>>Buraco na Via</option>
                                <option value="Falta de Iluminação" <?= $filtro == 'Falta de Iluminação' ? 'selected' : '' ?>>Falta de Iluminação</option>
                                <option value="Descarte Irregular de Lixo" <?= $filtro == 'Descarte Irregular de Lixo' ? 'selected' : '' ?>>Descarte Irregular de Lixo</option>
                                <option value="Sinalização Danificada" <?= $filtro == 'Sinalização Danificada' ? 'selected' : '' ?>>Sinalização Danificada</option>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-medium text-muted mb-1">Buscar Protocolo / Endereço</label>
                            <input type="text" name="termo" class="form-control" placeholder="Digite para buscar..." value="<?= htmlspecialchars($termoBusca) ?>">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-dark w-100 btn-action">Filtrar</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card p-3 text-center">
                    <h6 class="text-muted small mb-2">Incidência por Categoria</h6>
                    <div style="height: 120px;"><canvas id="categoriaChart"></canvas></div>
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

    <div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content bg-transparent border-0">
                <div class="modal-header border-0 pb-0 d-flex justify-content-end p-2">
                    <button type="button" class="btn btn-dark rounded-circle shadow-sm" data-bs-dismiss="modal" aria-label="Fechar" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border: 2px solid #ffd21f;">
                        <i class="bi bi-x-lg text-white"></i>
                    </button>
                </div>
                <div class="modal-body text-center pt-0">
                    <img src="" id="modalImageDisplay" class="img-fluid rounded shadow-lg" alt="Evidência" style="max-height: 80vh; border: 1px solid rgba(255,255,255,0.1);">
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const imageModal = document.getElementById('imageModal');
        if (imageModal) {
            imageModal.addEventListener('show.bs.modal', event => {
                const button = event.relatedTarget;
                const imgSrc = button.getAttribute('data-bs-img');
                document.getElementById('modalImageDisplay').src = imgSrc;
            });
        }

        const ctx = document.getElementById('categoriaChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($catLabels) ?>,
                datasets: [{
                    data: <?= json_encode($catData) ?>,
                    backgroundColor: ['#ffd21f', '#1a1a1a', '#dc3545', '#198754']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                }
            }
        });
    </script>
</body>
</html>