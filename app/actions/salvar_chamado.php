<?php
require '../config/conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoria = $_POST['categoria'];
    $descricao = $_POST['descricao'];
    $endereco = $_POST['endereco'];
    $protocolo = date('YmdHis') . rand(100, 999);
    $dbPath = null;

    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $fotoNome = $protocolo . '.' . $ext;
        $physicalPath = '../../public/uploads/' . $fotoNome;
        $dbPath = 'public/uploads/' . $fotoNome;
        
        if (!is_dir('../../public/uploads')) {
            mkdir('../../public/uploads', 0777, true);
        }
        
        move_uploaded_file($_FILES['foto']['tmp_name'], $physicalPath);
    }

    $stmt = $pdo->prepare("INSERT INTO chamados (protocolo, categoria, descricao, endereco, foto) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$protocolo, $categoria, $descricao, $endereco, $dbPath]);

    header("Location: ../../index.php?sucesso=" . $protocolo);
    exit;
}
?>