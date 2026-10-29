<?php
session_start();
require_once('DAO/noticia.php');
require_once('DAO/tag.php');
require_once('views/partials/helpers.php');

$basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
$urlNoticias = ($basePath === '' || $basePath === '.') ? 'noticias' : $basePath . '/noticias';
$homeUrl = ($basePath === '' || $basePath === '.') ? 'index.php' : $basePath . '/index.php';
$navUltimasHref = $homeUrl . '#ultimas-noticias';
$paginaAtual = 'noticias';

$erro = 0;
$usuarioLogado = isset($_SESSION['id_usuario']);
$nomeUsuario = $usuarioLogado ? (string) $_SESSION['nome_usuario'] : '';

$tagService = new TagService();
$tagSolicitada = isset($_GET['tag']) ? trim((string) $_GET['tag']) : '';
$tagSelecionada = null;
$tagInvalida = false;

if ($tagSolicitada !== '') {
	$tagSelecionada = $tagService->buscarPorNome($tagSolicitada);
	if (!$tagSelecionada) {
		$tagInvalida = true;
	}
}

$servicoNoticia = new NoticiaService();
$porPagina = 4;

if ($tagSelecionada) {
	$totalNoticias = $servicoNoticia->numeroTotalNoticiasPorTag((int) $tagSelecionada['id']);
} elseif ($tagInvalida) {
	$totalNoticias = 0;
} else {
	$totalNoticias = (int) $servicoNoticia->numeroTotalNoticias();
}

$totalPaginas = max(1, (int) ceil($totalNoticias / $porPagina));

$pagina = isset($_GET['pagina']) && is_numeric($_GET['pagina']) ? (int) $_GET['pagina'] : 1;
$pagina = max(1, $pagina);

$noticiaId = isset($_GET['noticia']) && is_numeric($_GET['noticia']) ? (int) $_GET['noticia'] : 0;
if ($tagInvalida) {
	$noticiaId = 0;
}
$temNoticiaSelecionada = false;

$urlComTag = fn(string $nome): string => $urlNoticias . '?tag=' . urlencode($nome);

if ($noticiaId > 0) {
	$encontradas = $servicoNoticia->findById($noticiaId);
	if (empty($encontradas)) {
		$destino = $tagSelecionada ? $urlComTag($tagSelecionada['nome']) : $urlNoticias;
		header('Location: ' . $destino);
		exit;
	}

	if ($tagSelecionada) {
		$tagsDoArtigo = array_map(
			static fn($tag) => mb_strtolower((string) $tag, 'UTF-8'),
			$encontradas[0]['tags'] ?? []
		);
		if (!in_array(mb_strtolower($tagSelecionada['nome'], 'UTF-8'), $tagsDoArtigo, true)) {
			header('Location: ' . $urlComTag($tagSelecionada['nome']));
			exit;
		}

		$pagina = intdiv(
			$servicoNoticia->contarMaisRecentesQuePorTag($noticiaId, (int) $tagSelecionada['id']),
			$porPagina
		) + 1;
	} else {
		$pagina = intdiv($servicoNoticia->contarMaisRecentesQue($noticiaId), $porPagina) + 1;
	}

	$temNoticiaSelecionada = true;
}

$pagina = min($pagina, $totalPaginas);
$offset = ($pagina - 1) * $porPagina;

if ($tagSelecionada) {
	$registros = $servicoNoticia->getNoticiasPaginadasPorTag((int) $tagSelecionada['id'], $porPagina, $offset);
} elseif ($tagInvalida) {
	$registros = [];
} else {
	$registros = $servicoNoticia->getNoticiasPaginadas($porPagina, $offset);
}

/* Coloca a notícia selecionada em destaque e mantém as outras da mesma página. */
if ($temNoticiaSelecionada) {
	$linhaPrincipal = null;
	$outrasLinhas = [];

	foreach ($registros as $registro) {
		if ($linhaPrincipal === null && (int) $registro['noticia_id'] === $noticiaId) {
			$linhaPrincipal = $registro;
		} else {
			$outrasLinhas[] = $registro;
		}
	}

	if ($linhaPrincipal !== null) {
		$registros = array_merge([$linhaPrincipal], array_slice($outrasLinhas, 0, $porPagina - 1));
	}
}

function prepararNoticia(array $row): array
{
	$fotoConteudo = $row['foto'] ?? null;
	if (is_resource($fotoConteudo)) {
		$fotoConteudo = stream_get_contents($fotoConteudo);
	}

	$base64 = $fotoConteudo ? base64_encode($fotoConteudo) : '';
	$mime = 'image/jpeg';
	if ($fotoConteudo) {
		$info = @getimagesizefromstring($fotoConteudo);
		if (is_array($info) && !empty($info['mime'])) {
			$mime = $info['mime'];
		}
	}

	$dataCriacao = !empty($row['data_criacao']) ? date('d/m/Y', strtotime($row['data_criacao'])) : '';
	$tags = array_values(array_filter(
		[(string) ($row['tag1'] ?? ''), (string) ($row['tag2'] ?? ''), (string) ($row['tag3'] ?? '')],
		fn($tag) => trim($tag) !== ''
	));

	return [
		'id' => (int) $row['noticia_id'],
		'titulo' => (string) $row['titulo'],
		'texto' => (string) $row['texto'],
		'tags' => $tags,
		'autor' => (string) $row['nome_autor'],
		'data' => $dataCriacao,
		'imagem' => $base64,
		'mime' => $mime,
	];
}

$noticiasPagina = array_map('prepararNoticia', $registros);
$principal = $noticiasPagina[0] ?? null;
$secundarias = array_slice($noticiasPagina, 1, 3);

$paginasVisiveis = [];
if ($totalPaginas <= 7) {
	$paginasVisiveis = range(1, $totalPaginas);
} else {
	$paginasVisiveis = [1];
	if ($pagina > 3) {
		$paginasVisiveis[] = '...';
	}
	for ($p = max(2, $pagina - 1); $p <= min($totalPaginas - 1, $pagina + 1); $p++) {
		$paginasVisiveis[] = $p;
	}
	if ($pagina < $totalPaginas - 2) {
		$paginasVisiveis[] = '...';
	}
	$paginasVisiveis[] = $totalPaginas;
}

$urlPagina = function (int $numero) use ($urlNoticias, $tagSelecionada): string {
	$params = ['pagina' => $numero];
	if ($tagSelecionada) {
		$params['tag'] = $tagSelecionada['nome'];
	}
	return $urlNoticias . '?' . http_build_query($params);
};

$urlDaNoticia = function (int $id) use ($urlNoticias, $tagSelecionada): string {
	$params = ['noticia' => $id];
	if ($tagSelecionada) {
		$params['tag'] = $tagSelecionada['nome'];
	}
	return $urlNoticias . '?' . http_build_query($params);
};

$urlTag = fn(string $nome): string => $urlNoticias . '?tag=' . urlencode($nome);

$pageTitle = $tagSelecionada
	? 'Notícias sobre "' . $tagSelecionada['nome'] . '" — Notícias Atuais'
	: 'Notícias — Notícias Atuais';
$pageDescription = 'Leia a notícia completa e acompanhe as últimas publicações da plataforma Notícias Atuais.';
$pageStyles = ['assets/css/noticias.css'];
?>
<!DOCTYPE html>
<html lang="pt-br">
<?php include 'views/partials/head.php'; ?>

<body class="home noticias-page">
	<a class="skip-link" href="#conteudo">Pular para o conteúdo</a>

	<?php include 'views/partials/site-header.php'; ?>

	<main id="conteudo">
		<section class="noticias-hero">
			<div class="container">
				<span class="section-kicker">
					<?= $tagSelecionada ? 'Filtro por tag' : ($tagInvalida ? 'Filtro' : 'Leitura') ?>
				</span>
				<h1 class="section-title">
					<?php if ($tagSelecionada): ?>
						Notícias sobre “<?= esc($tagSelecionada['nome']) ?>”
					<?php elseif ($tagInvalida): ?>
						Tag não encontrada
					<?php else: ?>
						Notícias
					<?php endif; ?>
				</h1>
				<p class="section-subtitle">
					<?php if ($tagSelecionada): ?>
						<?= pluralNoticias($totalNoticias, 'notícia encontrada', 'notícias encontradas') ?> com esta tag.
					<?php elseif ($tagInvalida): ?>
						A tag “<?= esc($tagSolicitada) ?>” não existe.
					<?php else: ?>
						A notícia mais recente em destaque e as últimas publicações para continuar lendo.
					<?php endif; ?>
				</p>
				<?php if ($tagSelecionada || $tagInvalida): ?>
					<a class="btn btn-ghost btn-sm mt-2" href="<?= esc($urlNoticias) ?>">
						<i class="fas fa-times me-1"></i>Limpar filtro
					</a>
				<?php endif; ?>
			</div>
		</section>

		<div class="container noticias-content">
			<?php if (!$principal): ?>
				<div class="empty-state">
					<div class="empty-state__icon"><i class="fas fa-newspaper"></i></div>
					<h3 class="mb-2">
						<?= $tagInvalida
							? 'Tag não encontrada'
							: ($tagSelecionada ? 'Nenhuma notícia com esta tag' : 'Nenhuma notícia publicada ainda') ?>
					</h3>
					<p class="mb-0">
						<?php if ($tagSelecionada || $tagInvalida): ?>
							Não encontramos publicações para este filtro. Tente outra tag ou veja todas as notícias.
						<?php else: ?>
							Assim que a primeira publicação sair, ela aparece aqui para leitura.
						<?php endif; ?>
					</p>
					<?php if ($tagSelecionada || $tagInvalida): ?>
						<a class="btn btn-brand mt-4" href="<?= esc($urlNoticias) ?>">
							<i class="fas fa-newspaper me-2"></i>Ver todas as notícias
						</a>
					<?php else: ?>
						<a class="btn btn-brand mt-4" href="<?= esc($homeUrl) ?>">
							<i class="fas fa-arrow-left me-2"></i>Voltar para a home
						</a>
					<?php endif; ?>
				</div>
			<?php else: ?>
				<article class="reader-card">
					<?php if ($principal['imagem']): ?>
						<div class="reader-media">
							<img src="data:<?= esc($principal['mime']) ?>;base64,<?= $principal['imagem'] ?>"
								alt="<?= esc($principal['titulo']) ?>">
						</div>
					<?php endif; ?>

					<div class="reader-body">
						<div class="reader-topline">
							<span class="chip-featured"><i class="fas fa-book-open"></i> Leitura</span>

							<?php if ($temNoticiaSelecionada): ?>
								<a class="reader-back" href="<?= esc($urlPagina($pagina)) ?>">
									<i class="fas fa-arrow-left"></i> Voltar para a lista
								</a>
							<?php endif; ?>
						</div>

						<h2 class="reader-title"><?= esc($principal['titulo']) ?></h2>

						<div class="meta-row reader-meta">
							<span class="avatar"><?= esc(inicial($principal['autor'])) ?></span>
							<span class="meta-author"><?= esc($principal['autor']) ?></span>
							<span class="meta-divider" aria-hidden="true"></span>
							<span><?= esc($principal['data']) ?></span>
						</div>

						<?php if ($principal['tags']): ?>
							<div class="tag-list reader-tags">
								<?php foreach ($principal['tags'] as $tag): ?>
									<a class="tag" href="<?= esc($urlTag($tag)) ?>"
										title="Filtrar notícias por <?= esc($tag) ?>"><?= esc($tag) ?></a>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>

						<div class="reader-text">
							<?php foreach (preg_split('/\R{2,}/', trim($principal['texto'])) as $paragrafo): ?>
								<?php if (trim($paragrafo) === '') {
									continue;
								} ?>
								<p><?= nl2br(esc($paragrafo)) ?></p>
							<?php endforeach; ?>
						</div>
					</div>
				</article>

				<?php if ($secundarias): ?>
					<section class="reader-more">
						<h2 class="reader-more__title">Mais notícias</h2>

						<div class="row g-4">
							<?php foreach ($secundarias as $noticia): ?>
								<div class="col-md-4">
									<article class="news-card">
										<?php if ($noticia['imagem']): ?>
											<a class="news-card__media" href="<?= esc($urlDaNoticia($noticia['id'])) ?>"
												tabindex="-1" aria-hidden="true">
												<img src="data:<?= esc($noticia['mime']) ?>;base64,<?= $noticia['imagem'] ?>"
													alt="" loading="lazy">
											</a>
										<?php else: ?>
											<div class="news-card__media news-card__media--empty" aria-hidden="true">
												<i class="fas fa-newspaper"></i>
											</div>
										<?php endif; ?>

										<div class="news-card__body">
											<span class="news-card__date"><?= esc($noticia['data']) ?></span>

											<h3 class="news-card__title clamp-2">
												<a href="<?= esc($urlDaNoticia($noticia['id'])) ?>">
													<?= esc($noticia['titulo']) ?>
												</a>
											</h3>

											<p class="news-card__excerpt clamp-3">
												<?= esc(resumir($noticia['texto'], 120)) ?>
											</p>

											<?php if ($noticia['tags']): ?>
												<div class="tag-list mb-0">
													<?php foreach ($noticia['tags'] as $tag): ?>
														<a class="tag" href="<?= esc($urlTag($tag)) ?>"
										title="Filtrar notícias por <?= esc($tag) ?>"><?= esc($tag) ?></a>
													<?php endforeach; ?>
												</div>
											<?php endif; ?>

											<div class="news-card__footer">
												<div class="meta-row">
													<span class="avatar avatar-sm"><?= esc(inicial($noticia['autor'])) ?></span>
													<span class="small"><?= esc($noticia['autor']) ?></span>
												</div>
												<a class="btn btn-ghost btn-sm" href="<?= esc($urlDaNoticia($noticia['id'])) ?>"
													aria-label="Ler notícia: <?= esc($noticia['titulo']) ?>">
													Ler <i class="fas fa-arrow-right ms-1"></i>
												</a>
											</div>
										</div>
									</article>
								</div>
							<?php endforeach; ?>
						</div>
					</section>
				<?php endif; ?>

				<?php if ($totalPaginas > 1): ?>
					<nav class="reader-pagination" aria-label="Paginação de notícias">
						<ul class="pagination justify-content-center flex-wrap mb-0">
							<li class="page-item <?= $pagina <= 1 ? 'disabled' : '' ?>">
								<?php if ($pagina > 1): ?>
									<a class="page-link" href="<?= esc($urlPagina($pagina - 1)) ?>" rel="prev">
										<i class="fas fa-chevron-left me-1"></i>Anterior
									</a>
								<?php else: ?>
									<span class="page-link"><i class="fas fa-chevron-left me-1"></i>Anterior</span>
								<?php endif; ?>
							</li>

							<?php foreach ($paginasVisiveis as $numeroPagina): ?>
								<?php if ($numeroPagina === '...'): ?>
									<li class="page-item disabled"><span class="page-link">…</span></li>
								<?php else: ?>
									<li class="page-item <?= (int) $numeroPagina === $pagina ? 'active' : '' ?>">
										<a class="page-link" href="<?= esc($urlPagina($numeroPagina)) ?>"
											<?= (int) $numeroPagina === $pagina ? 'aria-current="page"' : '' ?>>
											<?= (int) $numeroPagina ?>
										</a>
									</li>
								<?php endif; ?>
							<?php endforeach; ?>

							<li class="page-item <?= $pagina >= $totalPaginas ? 'disabled' : '' ?>">
								<?php if ($pagina < $totalPaginas): ?>
									<a class="page-link" href="<?= esc($urlPagina($pagina + 1)) ?>" rel="next">
										Próxima<i class="fas fa-chevron-right ms-1"></i>
									</a>
								<?php else: ?>
									<span class="page-link">Próxima<i class="fas fa-chevron-right ms-1"></i></span>
								<?php endif; ?>
							</li>
						</ul>
					</nav>
				<?php endif; ?>
			<?php endif; ?>
		</div>
	</main>

	<?php include 'views/partials/site-footer.php'; ?>
	<?php include 'views/partials/login-modal.php'; ?>
	<?php include 'views/partials/page-scripts.php'; ?>
</body>

</html>
