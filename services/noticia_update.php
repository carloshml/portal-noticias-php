<?php
session_start();
require_once __DIR__ . '/../config/banco.php';
require_once __DIR__ . '/../DAO/tag.php';

if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../index.php?erro=2');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/listar_noticias.php');
    exit;
}

$id_autor = (int) $_SESSION['id_usuario'];
$noticia_id = isset($_POST['noticia_id']) ? (int) $_POST['noticia_id'] : 0;
$titulo = trim((string) ($_POST['titulo'] ?? ''));
$texto = trim((string) ($_POST['texto'] ?? ''));
$tag1 = trim((string) ($_POST['tag1'] ?? ''));
$tag2 = trim((string) ($_POST['tag2'] ?? ''));
$tag3 = trim((string) ($_POST['tag3'] ?? ''));

$erros = [];
if ($noticia_id <= 0) {
    $erros[] = 'Notícia inválida.';
}
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
    'noticia_id' => $noticia_id,
    'titulo' => $titulo,
    'texto' => $texto,
    'tag1' => $tag1,
    'tag2' => $tag2,
    'tag3' => $tag3,
];

$volta = '../views/noticia-form.php' . ($noticia_id > 0 ? '?noticia_id=' . $noticia_id : '');

if ($erros) {
    $_SESSION['noticia_form_erros'] = $erros;
    $_SESSION['noticia_form_old'] = $old;
    header('Location: ' . $volta);
    exit;
}

$imagem = $_FILES['imagem'] ?? null;
$temNovaImagem = $imagem
    && ($imagem['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK
    && is_uploaded_file($imagem['tmp_name']);

$tags = array_values(array_filter(
    [$tag1, $tag2, $tag3],
    static fn($tag) => $tag !== ''
));

try {
    $pdo = Banco::conectar();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->beginTransaction();

    $sql = "UPDATE noticia
               SET titulo = :titulo,
                   texto = :texto,
                   id_autor = :id_autor" .
        ($temNovaImagem ? ", foto = :foto" : "") .
        " WHERE id = :noticia_id";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':titulo', $titulo);
    $stmt->bindValue(':texto', $texto);
    $stmt->bindValue(':id_autor', $id_autor, PDO::PARAM_INT);
    $stmt->bindValue(':noticia_id', $noticia_id, PDO::PARAM_INT);

    if ($temNovaImagem) {
        $stmt->bindValue(':foto', file_get_contents($imagem['tmp_name']), PDO::PARAM_LOB);
    }

    $stmt->execute();

    $tagService = new TagService();
    $tagService->sincronizarNoticia($noticia_id, $tags);

    $pdo->commit();
} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $_SESSION['noticia_form_erros'] = ['Não foi possível atualizar a notícia. Tente novamente.'];
    $_SESSION['noticia_form_old'] = $old;
    header('Location: ' . $volta);
    exit;
}

header('Location: ../views/listar_noticias.php?atualizado=1');
exit;
