<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
	header('Location: ../index.php?erro=2');
	exit;
}

require_once('../DAO/noticia.php');
require_once('../DAO/tag.php');

$nome_usuario = (string) $_SESSION['nome_usuario'];
$id_usuario = (int) $_SESSION['id_usuario'];

$noticiaId = isset($_GET['noticia_id']) && is_numeric($_GET['noticia_id']) ? (int) $_GET['noticia_id'] : 0;
$modoEdicao = $noticiaId > 0;
$noticia = null;

if ($modoEdicao) {
	$servicoNoticia = new NoticiaService();
	$encontradas = $servicoNoticia->findById($noticiaId);
	$noticia = $encontradas[0] ?? null;

	if (!$noticia) {
		header('Location: listar_noticias.php');
		exit;
	}
}

$erros = [];
if (!empty($_SESSION['noticia_form_erros'])) {
	$erros = (array) $_SESSION['noticia_form_erros'];
	unset($_SESSION['noticia_form_erros']);
}

$old = [];
if (!empty($_SESSION['noticia_form_old']) && is_array($_SESSION['noticia_form_old'])) {
	$old = $_SESSION['noticia_form_old'];
	unset($_SESSION['noticia_form_old']);
}

$temOld = isset($old['noticia_id']) && (int) $old['noticia_id'] === $noticiaId;
if ($old && !array_key_exists('noticia_id', $old)) {
	$temOld = true;
}

$valores = [
	'titulo' => $temOld ? (string) ($old['titulo'] ?? '') : (string) ($noticia['titulo'] ?? ''),
	'texto' => $temOld ? (string) ($old['texto'] ?? '') : (string) ($noticia['texto'] ?? ''),
	'tag1' => $temOld ? (string) ($old['tag1'] ?? '') : (string) ($noticia['tag1'] ?? ''),
	'tag2' => $temOld ? (string) ($old['tag2'] ?? '') : (string) ($noticia['tag2'] ?? ''),
	'tag3' => $temOld ? (string) ($old['tag3'] ?? '') : (string) ($noticia['tag3'] ?? ''),
];

$fotoBase64 = '';
$fotoMime = 'image/jpeg';
if ($noticia && !empty($noticia['foto'])) {
	$fotoConteudo = $noticia['foto'];
	if (is_resource($fotoConteudo)) {
		$fotoConteudo = stream_get_contents($fotoConteudo);
	}
	if ($fotoConteudo) {
		$fotoBase64 = base64_encode($fotoConteudo);
		$info = @getimagesizefromstring($fotoConteudo);
		if (is_array($info) && !empty($info['mime'])) {
			$fotoMime = $info['mime'];
		}
	}
}

$action = $modoEdicao ? '../services/noticia_update.php' : '../services/noticia_salvar.php';
$pageTitle = $modoEdicao ? 'Editar notícia — Notícias Atuais' : 'Nova notícia — Notícias Atuais';

$tagService = new TagService();
$tagsRecentes = $tagService->getRecentes(5);
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
	<link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
	<nav class="navbar navbar-expand-lg navbar-light py-3">
		<div class="container">
			<a class="navbar-brand fw-bold" href="home.php">
				<i class="fas fa-newspaper me-2"></i>Notícias Atuais - Editor
			</a>
			<div class="d-flex align-items-center">
				<span class="me-3 d-none d-sm-inline"><?= htmlspecialchars($nome_usuario, ENT_QUOTES, 'UTF-8') ?></span>
				<a class="btn btn-outline-danger" href="../services/logout.php">
					<i class="fas fa-sign-out-alt"></i> Sair
				</a>
			</div>
		</div>
	</nav>

	<section class="container mt-5">
		<div class="card shadow-sm border-0">
			<div class="card-header text-white d-flex flex-wrap justify-content-between align-items-center gap-2"
				style="background-color: var(--primary-color);">
				<div>
					<h4 class="mb-0"><?= $modoEdicao ? 'Editar notícia' : 'Nova notícia' ?></h4>
					<small class="d-block opacity-75">
						<?= $modoEdicao
							? 'Altere os campos e salve para atualizar a publicação.'
							: 'Preencha os campos e publique uma nova notícia.' ?>
					</small>
				</div>
				<a href="listar_noticias.php" class="btn btn-sm btn-light" aria-label="Voltar para a lista de notícias">
					<i class="fas fa-arrow-left me-2"></i>Voltar
				</a>
			</div>

			<div class="card-body p-4">
				<?php if ($erros): ?>
					<div class="alert alert-danger" role="alert">
						<strong>Não foi possível salvar:</strong>
						<ul class="mb-0 mt-2">
							<?php foreach ($erros as $erro): ?>
								<li><?= htmlspecialchars((string) $erro, ENT_QUOTES, 'UTF-8') ?></li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>

				<form method="post" enctype="multipart/form-data" action="<?= $action ?>" id="formNoticia">
					<input type="hidden" name="noticia_id" id="noticia_id" value="<?= $noticiaId ?>">

					<div class="mb-3 form-floating">
						<input type="text" class="form-control" id="titulo" name="titulo" placeholder="Título"
							maxlength="50" value="<?= htmlspecialchars($valores['titulo'], ENT_QUOTES, 'UTF-8') ?>"
							required>
						<label for="titulo">Título</label>
					</div>

					<div class="mb-3">
						<label for="texto" class="form-label">Texto</label>
						<textarea name="texto" id="texto" class="form-control" rows="10"
							required><?= htmlspecialchars($valores['texto'], ENT_QUOTES, 'UTF-8') ?></textarea>
					</div>

					<div class="mb-4">
						<label for="imagem_file" class="form-label">Imagem</label>
						<div class="row align-items-center g-3">
							<div class="col-md-8">
								<input type="file" id="imagem_file" name="imagem" class="form-control" accept="image/*">
								<?php if ($fotoBase64): ?>
									<small class="text-muted d-block mt-2">
										Envie uma nova imagem apenas se quiser substituir a atual.
									</small>
								<?php endif; ?>
							</div>
							<div class="col-md-4">
								<div id="imagePreviewContainer" class="border rounded p-2 text-center bg-light"
									style="display: <?= $fotoBase64 ? 'block' : 'none' ?>;">
									<img id="imagePreview"
										src="<?= $fotoBase64 ? 'data:' . $fotoMime . ';base64,' . $fotoBase64 : '#' ?>"
										alt="Pré-visualização da imagem" class="img-fluid rounded"
										style="max-height: 180px;">
									<small id="imagePreviewLabel" class="d-block mt-2 text-muted">
										<?= $fotoBase64 ? 'Imagem atual' : 'Pré-visualização' ?>
									</small>
								</div>
							</div>
						</div>
					</div>

					<div class="row g-3 mb-2">
						<div class="col-md">
							<input type="text" name="tag1" id="tag1" class="form-control tag-input" placeholder="Tag 1"
								maxlength="10" value="<?= htmlspecialchars($valores['tag1'], ENT_QUOTES, 'UTF-8') ?>">
						</div>
						<div class="col-md">
							<input type="text" name="tag2" id="tag2" class="form-control tag-input" placeholder="Tag 2"
								maxlength="10" value="<?= htmlspecialchars($valores['tag2'], ENT_QUOTES, 'UTF-8') ?>">
						</div>
						<div class="col-md">
							<input type="text" name="tag3" id="tag3" class="form-control tag-input" placeholder="Tag 3"
								maxlength="10" value="<?= htmlspecialchars($valores['tag3'], ENT_QUOTES, 'UTF-8') ?>">
						</div>
					</div>

					<?php if ($tagsRecentes): ?>
						<div class="mb-4">
							<span class="form-label d-block mb-2">Usadas recentemente</span>
							<div class="d-flex flex-wrap gap-2">
								<?php foreach ($tagsRecentes as $tagRecente): ?>
									<button type="button" class="btn btn-sm btn-outline-primary rounded-pill tag-suggestion"
										data-tag="<?= htmlspecialchars($tagRecente['nome'], ENT_QUOTES, 'UTF-8') ?>">
										<i class="fas fa-plus me-1"></i><?= htmlspecialchars($tagRecente['nome'], ENT_QUOTES, 'UTF-8') ?>
									</button>
								<?php endforeach; ?>
							</div>
							<small id="tagSuggestionHelp" class="text-muted d-block mt-2">
								Clique para preencher a primeira tag vazia.
							</small>
						</div>
					<?php endif; ?>

					<div class="d-flex justify-content-between align-items-center">
						<a href="listar_noticias.php" class="btn btn-outline-secondary">Cancelar</a>
						<button type="submit" class="btn btn-success">
							<i class="fas fa-check me-2"></i><?= $modoEdicao ? 'Salvar alterações' : 'Publicar notícia' ?>
						</button>
					</div>
				</form>
			</div>
		</div>
	</section>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
	<script src="noticia-form.js"></script>
</body>

</html>
