<?php
session_start();
require_once('DAO/noticia.php');
require_once('views/partials/helpers.php');

$pageTitle = 'Notícias Atuais — Últimas notícias';
$pageDescription = 'Acompanhe as últimas notícias publicadas na plataforma Notícias Atuais e publique as suas.';
$pageStyles = [];
$homeUrl = 'index.php';
$urlNoticias = 'noticias';
$navUltimasHref = '#ultimas-noticias';
$paginaAtual = 'home';

$erro = (isset($_GET['erro']) && is_numeric($_GET['erro'])) ? (int) $_GET['erro'] : 0;
$usuarioLogado = isset($_SESSION['id_usuario']);
$nomeUsuario = $usuarioLogado ? (string) $_SESSION['nome_usuario'] : '';

$servicoNoticia = new NoticiaService();
$noticias = $servicoNoticia->get5Noticia();

$totalNoSite = (int) $servicoNoticia->numeroTotalNoticias();
$minhasNoticias = $usuarioLogado ? (int) $servicoNoticia->countByIdUsuario((int) $_SESSION['id_usuario']) : 0;

/* Normaliza as notícias vindas do banco em uma lista pronta para a view. */
$publicacoes = [];
if (is_array($noticias)) {
	foreach ($noticias as $row) {
		$fotoConteudo = $row['foto'];
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

		$dataCriacao = !empty($row['data_criacao'])
			? date('d/m/Y', strtotime($row['data_criacao']))
			: '';

		$publicacoes[] = [
			'id' => (int) $row['noticia_id'],
			'titulo' => (string) $row['titulo'],
			'texto' => (string) $row['texto'],
			'tags' => array_values(array_filter(
				[(string) $row['tag1'], (string) $row['tag2'], (string) $row['tag3']],
				fn($tag) => trim($tag) !== ''
			)),
			'autor' => (string) $row['nome_autor'],
			'data' => $dataCriacao,
			'imagem' => $base64,
			'mime' => $mime,
			'urlLeitura' => 'noticias?noticia=' . (int) $row['noticia_id'],
			'urlEdicao' => 'views/noticia-form.php?noticia_id=' . (int) $row['noticia_id'],
		];
	}
}

$destaque = $publicacoes[0] ?? null;
$secundarias = array_slice($publicacoes, 1);
$qtdSecundarias = count($secundarias);

$classeColuna = 'col-sm-6 col-lg-3';
if ($qtdSecundarias === 1) {
	$classeColuna = 'col-lg-8';
} elseif ($qtdSecundarias === 2) {
	$classeColuna = 'col-sm-6 col-lg-6';
} elseif ($qtdSecundarias === 3) {
	$classeColuna = 'col-sm-6 col-lg-4';
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<?php include 'views/partials/head.php'; ?>

<body class="home">
	<a class="skip-link" href="#ultimas-noticias">Pular para as notícias</a>

	<?php include 'views/partials/site-header.php'; ?>

	<main>
		<!-- Hero -->
		<section class="hero">
			<div class="container">
				<div class="row align-items-center g-5">
					<div class="col-lg-8">
						<?php if ($usuarioLogado): ?>
							<span class="hero-eyebrow"><i class="fas fa-pen-nib"></i> Área do autor</span>
							<h1 class="hero-title mt-3">Olá, <?= esc($nomeUsuario) ?>.</h1>
							<p class="hero-lead mt-3">
								Que bom ter você por aqui. Escreva uma nova notícia ou gerencie o que já foi publicado
								na plataforma.
							</p>
							<div class="d-flex flex-wrap gap-2 mt-4">
								<a class="btn btn-hero-light" href="views/noticia-form.php">
									<i class="fas fa-pen-to-square me-2"></i>Escrever notícia
								</a>
								<a class="btn btn-hero-outline" href="views/listar_noticias.php">
									<i class="fas fa-list me-2"></i>Ver todas as notícias
								</a>
							</div>
						<?php else: ?>
							<span class="hero-eyebrow"><i class="fas fa-bolt"></i> Notícias Atuais</span>
							<h1 class="hero-title mt-3">
								As notícias que importam,<br class="d-none d-md-block"> em um só lugar.
							</h1>
							<p class="hero-lead mt-3">
								Acompanhe as publicações mais recentes da comunidade, descubra novos assuntos e
								compartilhe o que acontece ao seu redor.
							</p>
							<div class="d-flex flex-wrap gap-2 mt-4">
								<a class="btn btn-hero-light" href="#ultimas-noticias">
									<i class="fas fa-book-open me-2"></i>Ler últimas notícias
								</a>
								<a class="btn btn-hero-outline" href="views/usuario-inscrevase.php">
									<i class="fas fa-user-plus me-2"></i>Criar conta grátis
								</a>
							</div>
							<p class="hero-note mt-3">
								<i class="fas fa-lock-open"></i>Leitura gratuita — não é preciso criar conta para
								acompanhar as notícias.
							</p>
						<?php endif; ?>
					</div>

					<div class="col-lg-4 d-none d-lg-block">
						<aside class="hero-panel">
							<?php if ($usuarioLogado): ?>
								<p class="hero-panel__label">Sua atividade</p>
								<p class="hero-panel__value"><?= $minhasNoticias ?></p>
								<p class="hero-panel__caption">
									<?= $minhasNoticias === 1 ? 'notícia publicada por você' : 'notícias publicadas por você' ?>
								</p>
								<hr>
								<ul class="hero-panel__list">
									<li><i class="fas fa-pen-nib"></i> Escreva novas notícias quando quiser</li>
									<li><i class="fas fa-pen-to-square"></i> Edite suas publicações a qualquer momento
									</li>
								</ul>
							<?php else: ?>
								<p class="hero-panel__label">No ar agora</p>
								<p class="hero-panel__value"><?= $totalNoSite ?></p>
								<p class="hero-panel__caption">
									<?= $totalNoSite === 1 ? 'notícia publicada pela comunidade' : 'notícias publicadas pela comunidade' ?>
								</p>
								<hr>
								<ul class="hero-panel__list">
									<li><i class="fas fa-lock-open"></i> Leitura gratuita, sem cadastro</li>
									<li><i class="fas fa-pen-nib"></i> Publique suas próprias notícias</li>
								</ul>
							<?php endif; ?>
						</aside>
					</div>
				</div>
			</div>
		</section>

		<!-- Últimas notícias -->
		<section id="ultimas-noticias" class="news-section">
			<div class="container">
				<div class="section-heading">
					<div>
						<span class="section-kicker">Últimas atualizações</span>
						<h2 class="section-title">Últimas notícias</h2>
						<p class="section-subtitle">Acompanhe o que foi publicado mais recentemente.</p>
					</div>
					<div class="d-flex flex-wrap align-items-center gap-2">
						<span class="count-pill">
							<i class="fas fa-newspaper"></i>
							<?= pluralNoticias(count($publicacoes)) ?>
						</span>
						<a class="btn btn-brand btn-sm" href="<?= esc($urlNoticias) ?>">
							<i class="fas fa-book-open me-2"></i>Ler todas as notícias
						</a>
					</div>
				</div>

				<?php if (!$destaque): ?>
					<!-- Nenhuma notícia publicada -->
					<div class="empty-state">
						<div class="empty-state__icon"><i class="fas fa-newspaper"></i></div>
						<h3 class="mb-2">Nenhuma notícia publicada ainda</h3>
						<p class="mb-0">
							Assim que a primeira publicação sair, ela aparece aqui em destaque. Enquanto isso, você pode
							criar sua conta para publicar.
						</p>
						<div class="d-flex flex-wrap justify-content-center gap-2 mt-4">
							<?php if ($usuarioLogado): ?>
								<a class="btn btn-brand" href="views/noticia-form.php">
									<i class="fas fa-pen-to-square me-2"></i>Escrever a primeira notícia
								</a>
							<?php else: ?>
								<button class="btn btn-brand" type="button" data-bs-toggle="modal"
									data-bs-target="#modal_login">
									<i class="fas fa-right-to-bracket me-2"></i>Entrar
								</button>
								<a class="btn btn-ghost" href="views/usuario-inscrevase.php">
									<i class="fas fa-user-plus me-2"></i>Criar conta
								</a>
							<?php endif; ?>
						</div>
					</div>
				<?php else: ?>
					<!-- Notícia em destaque -->
					<article class="featured-card mb-4 mb-lg-5">
						<div class="row g-0 h-100">
							<?php if ($destaque['imagem']): ?>
								<div class="col-lg-6 featured-media">
									<img src="data:<?= esc($destaque['mime']) ?>;base64,<?= $destaque['imagem'] ?>"
										alt="<?= esc($destaque['titulo']) ?>" fetchpriority="high">
								</div>
							<?php endif; ?>

							<div class="<?= $destaque['imagem'] ? 'col-lg-6' : 'col-12' ?>">
								<div class="featured-body">
									<span class="chip-featured"><i class="fas fa-star"></i>Destaque</span>

									<h3 class="featured-title">
										<a href="<?= esc($destaque['urlLeitura']) ?>">
											<?= esc($destaque['titulo']) ?>
										</a>
									</h3>

									<p class="featured-excerpt">
										<?= esc(resumir($destaque['texto'], 260)) ?>
									</p>

									<?php if ($destaque['tags']): ?>
										<div class="tag-list">
											<?php foreach ($destaque['tags'] as $tag): ?>
												<a class="tag" href="noticias?tag=<?= urlencode($tag) ?>" title="Filtrar notícias por <?= esc($tag) ?>"><?= esc($tag) ?></a>
											<?php endforeach; ?>
										</div>
									<?php endif; ?>

									<div class="meta-row mt-auto pt-3">
										<span class="avatar"><?= esc(inicial($destaque['autor'])) ?></span>
										<span class="meta-author"><?= esc($destaque['autor']) ?></span>
										<span class="meta-divider" aria-hidden="true"></span>
										<span><?= esc($destaque['data']) ?></span>

										<span class="ms-auto d-flex align-items-center gap-2">
											<?php if ($usuarioLogado): ?>
												<a class="icon-action" href="<?= esc($destaque['urlEdicao']) ?>"
													title="Editar notícia" aria-label="Editar notícia">
													<i class="fas fa-pen"></i>
												</a>
											<?php endif; ?>
											<a class="btn btn-brand btn-sm" href="<?= esc($destaque['urlLeitura']) ?>">
												Ler notícia <i class="fas fa-arrow-right ms-1"></i>
											</a>
										</span>
									</div>
								</div>
							</div>
						</div>
					</article>

					<?php if ($secundarias): ?>
						<div class="row g-4">
							<?php foreach ($secundarias as $noticia): ?>
								<div class="<?= $classeColuna ?>">
									<article class="news-card">
										<?php if ($noticia['imagem']): ?>
											<a class="news-card__media" href="<?= esc($noticia['urlLeitura']) ?>"
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
											<div class="d-flex align-items-center justify-content-between gap-2">
												<span class="news-card__date"><?= esc($noticia['data']) ?></span>
												<?php if ($usuarioLogado): ?>
													<a class="icon-action" href="<?= esc($noticia['urlEdicao']) ?>"
														title="Editar notícia" aria-label="Editar notícia">
														<i class="fas fa-pen"></i>
													</a>
												<?php endif; ?>
											</div>

											<h3 class="news-card__title clamp-2">
												<a href="<?= esc($noticia['urlLeitura']) ?>">
													<?= esc($noticia['titulo']) ?>
												</a>
											</h3>

											<p class="news-card__excerpt clamp-3">
												<?= esc(resumir($noticia['texto'], 150)) ?>
											</p>

											<?php if ($noticia['tags']): ?>
												<div class="tag-list mb-0">
													<?php foreach ($noticia['tags'] as $tag): ?>
														<a class="tag" href="noticias?tag=<?= urlencode($tag) ?>" title="Filtrar notícias por <?= esc($tag) ?>"><?= esc($tag) ?></a>
													<?php endforeach; ?>
												</div>
											<?php endif; ?>

											<div class="news-card__footer">
												<div class="meta-row">
													<span class="avatar avatar-sm"><?= esc(inicial($noticia['autor'])) ?></span>
													<span class="small"><?= esc($noticia['autor']) ?></span>
												</div>
												<a class="btn btn-ghost btn-sm" href="<?= esc($noticia['urlLeitura']) ?>"
													aria-label="Ler notícia: <?= esc($noticia['titulo']) ?>">
													Ler <i class="fas fa-arrow-right ms-1"></i>
												</a>
											</div>
										</div>
									</article>
								</div>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				<?php endif; ?>
			</div>
		</section>

		<?php if (!$usuarioLogado && $destaque): ?>
			<!-- Convite para participar -->
			<section class="pb-5">
				<div class="container">
					<div class="join-band">
						<div class="join-inner">
							<div class="row align-items-center g-4">
								<div class="col-lg-8">
									<span class="join-kicker"><i class="fas fa-bullhorn"></i> Participe</span>
									<h2 class="join-title">Tem uma notícia para contar?</h2>
									<p class="join-text mb-0">
										Crie sua conta gratuita para publicar, editar e gerenciar suas notícias na
										plataforma. Leva menos de um minuto.
									</p>
								</div>
								<div
									class="col-lg-4 d-flex flex-column flex-sm-row flex-lg-column justify-content-lg-center gap-2">
									<button class="btn btn-hero-light" type="button" data-bs-toggle="modal"
										data-bs-target="#modal_login">
										<i class="fas fa-right-to-bracket me-2"></i>Entrar
									</button>
									<a class="btn btn-hero-outline" href="views/usuario-inscrevase.php">
										<i class="fas fa-user-plus me-2"></i>Criar conta grátis
									</a>
								</div>
							</div>
						</div>
					</div>
				</div>
			</section>
		<?php endif; ?>
	</main>

	<?php include 'views/partials/site-footer.php'; ?>

	<?php include 'views/partials/login-modal.php'; ?>

	<?php include 'views/partials/page-scripts.php'; ?>
</body>

</html>
