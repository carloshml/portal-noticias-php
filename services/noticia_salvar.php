<?php
session_start();
require_once __DIR__ . '/../config/banco.php';

if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../index.php?erro=2');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/noticia-form.php');
    exit;
}

$id_autor = (int) $_SESSION['id_usuario'];
$titulo = trim((string) ($_POST['titulo'] ?? ''));
$texto = trim((string) ($_POST['texto'] ?? ''));
$tag1 = trim((string) ($_POST['tag1'] ?? ''));
$tag2 = trim((string) ($_POST['tag2'] ?? ''));
$tag3 = trim((string) ($_POST['tag3'] ?? ''));

$erros = [];
if ($titulo === '') {
    $erros[] = 'O título é obrigatório.';
} elseif (mb_strlen($titulo) > 50) {
    $erros[] = 'O título deve ter no máximo 50 caracteres.';
}
if ($texto === '') {
    $erros[] = 'O texto é obrigatório.';
} elseif (mb_strlen($texto) < 60) {
    $erros[] = 'O texto deve conter pelo menos 60 caracteres.';
}
if ($tag1 === '' && $tag2 === '' && $tag3 === '') {
    $erros[] = 'Preencha pelo menos uma tag.';
}
foreach ([$tag1, $tag2, $tag3] as $tag) {
    if ($tag !== '' && mb_strlen($tag) > 10) {
        $erros[] = 'Cada tag deve ter no máximo 10 caracteres.';
        break;
    }
}

$old = [
    'noticia_id' => 0,
    'titulo' => $titulo,
    'texto' => $texto,
    'tag1' => $tag1,
    'tag2' => $tag2,
    'tag3' => $tag3,
];

if ($erros) {
    $_SESSION['noticia_form_erros'] = $erros;
    $_SESSION['noticia_form_old'] = $old;
    header('Location: ../views/noticia-form.php');
    exit;
}

$imagem = $_FILES['imagem'] ?? null;
$temImagem = $imagem
    && ($imagem['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK
    && is_uploaded_file($imagem['tmp_name']);

try {
    $pdo = Banco::conectar();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "INSERT INTO noticia (titulo, texto, tag1, tag2, tag3, id_autor, data_criacao, foto)
            VALUES (:titulo, :texto, :tag1, :tag2, :tag3, :id_autor, NOW(), :foto)";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':titulo', $titulo);
    $stmt->bindValue(':texto', $texto);
    $stmt->bindValue(':tag1', $tag1);
    $stmt->bindValue(':tag2', $tag2);
    $stmt->bindValue(':tag3', $tag3);
    $stmt->bindValue(':id_autor', $id_autor, PDO::PARAM_INT);

    if ($temImagem) {
        $stmt->bindValue(':foto', file_get_contents($imagem['tmp_name']), PDO::PARAM_LOB);
    } else {
        $stmt->bindValue(':foto', null, PDO::PARAM_NULL);
    }

    $stmt->execute();
} catch (PDOException $e) {
    $_SESSION['noticia_form_erros'] = ['Não foi possível publicar a notícia. Tente novamente.'];
    $_SESSION['noticia_form_old'] = $old;
    header('Location: ../views/noticia-form.php');
    exit;
}

header('Location: ../views/listar_noticias.php?sucesso=1');
exit;
