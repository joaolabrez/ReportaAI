<?php
session_start();
require '../config/conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoria = $_POST['categoria'] ?? '';
    $endereco = $_POST['endereco'] ?? '';
    $descricao = $_POST['descricao'] ?? '';
    $fotoCaminho = null;

    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $extensao = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        $novoNome = uniqid() . '.' . $extensao;
        $destino = '../../public/uploads/' . $novoNome;
        
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $destino)) {
            $fotoCaminho = 'public/uploads/' . $novoNome;
        }
    }

    $protocolo = date('Ymd') . rand(1000, 9999);

    $stmt = $pdo->prepare("INSERT INTO chamados (protocolo, categoria, descricao, endereco, foto) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$protocolo, $categoria, $descricao, $endereco, $fotoCaminho]);

    header("Location: ../../cidadao.php?sucesso=" . $protocolo);
    exit;
}
?>