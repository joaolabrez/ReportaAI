<?php
require 'app/config/conexao.php';
$chamadoEncontrado = null;
$buscaErro = false;

if (isset($_GET['busca']) && !empty(trim($_GET['busca']))) {
    $termo = trim($_GET['busca']);
    $stmt = $pdo->prepare("SELECT * FROM chamados WHERE protocolo = ? OR endereco LIKE ?");
    $stmt->execute([$termo, "%$termo%"]);
    $chamadoEncontrado = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$chamadoEncontrado) {
        $buscaErro = true;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ReportaAI - Montenegro/RS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="public/assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-dark mb-5 py-3">
        <div class="container d-flex justify-content-between align-items-center">
            <span class="navbar-brand mb-0 h1"><i class="bi bi-geo-alt-fill brand-highlight me-2"></i>Reporta<span class="brand-highlight">AI</span></span>
            <a href="admin.php" class="btn btn-outline-light btn-sm px-3 rounded-pill" style="border-color: #ffd21f; color: #ffd21f;">
                <i class="bi bi-shield-lock me-1"></i> Acesso Prefeitura
            </a>
        </div>
    </nav>

    <div class="container pb-5">
        <div class="row g-4">
            <div class="col-lg-7">
                <div class="card p-4">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-megaphone me-2" style="color: #ffd21f;"></i>Relatar Novo Problema</h5>
                        <form action="app/actions/salvar_chamado.php" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                            <div class="mb-4">
                                <label class="form-label fw-medium">Categoria</label>
                                <select name="categoria" class="form-select" required>
                                    <option value="" selected disabled>Selecione a natureza do problema...</option>
                                    <option value="Buraco na Via">Buraco na Via</option>
                                    <option value="Falta de Iluminação">Falta de Iluminação</option>
                                    <option value="Descarte Irregular de Lixo">Descarte Irregular de Lixo</option>
                                    <option value="Sinalização Danificada">Sinalização Danificada</option>
                                </select>
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-medium">Endereço Completo</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-map"></i></span>
                                    <input type="text" name="endereco" id="enderecoInput" class="form-control border-start-0" placeholder="Rua, Número, Bairro" required>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-medium">Selecione o Local no Mapa <span class="text-muted fw-normal">(Clique para marcar)</span></label>
                                <div id="map" style="height: 250px; border-radius: 10px; border: 1px solid #e0e0e0;"></div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-medium">Descrição Detalhada</label>
                                <textarea name="descricao" class="form-control" rows="3" placeholder="Descreva o problema..." required></textarea>
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-medium">Foto do Local <span class="text-muted fw-normal">(Opcional)</span></label>
                                <input type="file" name="foto" id="fotoInput" class="form-control" accept="image/*">
                                <img id="imagePreview" src="#" alt="Preview">
                            </div>
                            <button type="submit" class="btn btn-primary-custom w-100 fs-5"><i class="bi bi-send-fill me-2"></i>Enviar Relato</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <?php if (isset($_GET['sucesso'])): ?>
                    <div class="alert alert-custom p-4 mb-4">
                        <h5 class="text-success mb-3"><i class="bi bi-check-circle-fill me-2"></i>Relato Enviado!</h5>
                        <p class="mb-1 text-muted">Guarde seu número de protocolo:</p>
                        <div class="bg-light p-3 rounded text-center border">
                            <span class="fs-4 fw-bold text-dark"><?= htmlspecialchars($_GET['sucesso']) ?></span>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="card p-4">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-search me-2" style="color: #ffd21f;"></i>Consultar Protocolo</h5>
                        <form action="index.php" method="GET">
                            <div class="input-group mb-4">
                                <input type="text" name="busca" class="form-control" placeholder="Digite seu protocolo" value="<?= htmlspecialchars($_GET['busca'] ?? '') ?>" required>
                                <button class="btn btn-secondary-custom" type="submit">Buscar</button>
                            </div>
                        </form>
                        
                        <?php if ($chamadoEncontrado): ?>
                            <div class="p-4 rounded" style="background-color: #f8f9fa; border: 1px solid #e0e0e0;">
                                <p class="text-muted mb-3 text-center">Protocolo: <strong><?= htmlspecialchars($chamadoEncontrado['protocolo']) ?></strong></p>
                                
                                <?php 
                                    $status = $chamadoEncontrado['status'];
                                    $step1 = ($status == 'Pendente' || $status == 'Em Andamento' || $status == 'Resolvido') ? 'active text-success fw-bold' : 'text-muted';
                                    $step2 = ($status == 'Em Andamento' || $status == 'Resolvido') ? 'active text-success fw-bold' : 'text-muted';
                                    $step3 = ($status == 'Resolvido') ? 'active text-success fw-bold' : 'text-muted';
                                ?>
                                <div class="timeline-container px-2">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="<?= $step1 ?>"><i class="bi bi-1-circle-fill"></i> Pendente</span>
                                        <span class="<?= $step2 ?>"><i class="bi bi-2-circle-fill"></i> Em Andamento</span>
                                        <span class="<?= $step3 ?>"><i class="bi bi-3-circle-fill"></i> Resolvido</span>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <?php 
                                            $width = '15%';
                                            if($status == 'Em Andamento') $width = '55%';
                                            if($status == 'Resolvido') $width = '100%';
                                        ?>
                                        <div class="progress-bar bg-success" role="progressbar" style="width: <?= $width ?>;"></div>
                                    </div>
                                </div>
                                <div class="mt-3 text-center">
                                    <span class="badge bg-dark fs-6 px-3 py-2">Status: <?= htmlspecialchars($status) ?></span>
                                </div>
                            </div>
                        <?php elseif ($buscaErro): ?>
                            <div class="alert alert-danger border-0 rounded-3">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>Protocolo não encontrado.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="public/assets/js/main.js"></script>
</body>
</html>