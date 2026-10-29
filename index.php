<?php
session_start();
require_once('DAO/noticia.php');

/** Escapa texto para saída segura no HTML. */
function esc($valor): string
{
	return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

/** Limita o texto a um resumo de uma linha. */
function resumir($texto, int $limite = 200): string
{
	$limpo = trim((string) preg_replace('/\s+/u', ' ', (string) $texto));
	if (mb_strlen($limpo) <= $limite) {
		return $limpo;
	}
	return rtrim(mb_substr($limpo, 0, $limite)) . '…';
}

/** Primeira letra do nome, usada no avatar. */
function inicial($nome): string
{
	$nome = trim((string) $nome);
	return $nome === '' ? '?' : mb_strtoupper(mb_substr($nome, 0, 1, 'UTF-8'), 'UTF-8');
}

/** "1 notícia" / "5 notícias". */
function pluralNoticias(int $total, string $singular = 'notícia', string $plural = 'notícias'): string
{
	return $total . ' ' . ($total === 1 ? $singular : $plural);
}

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

		$payload = [
			'noticia_id' => $row['noticia_id'],
			'titulo' => $row['titulo'],
			'texto' => $row['texto'],
			'tag1' => $row['tag1'],
			'tag2' => $row['tag2'],
			'tag3' => $row['tag3'],
			'nomeAutor' => $row['nome_autor'],
			'dataCriacao' => $dataCriacao,
		];

		$parametros = 'noticia_id=' . (int) $row['noticia_id']
			. '&erro=' . urlencode('Edite Sua Noticia')
			. '&noticia=' . urlencode(json_encode($payload, JSON_INVALID_UTF8_SUBSTITUTE));

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
			'urlLeitura' => 'views/noticia-read.php?' . $parametros,
			'urlEdicao' => 'views/escrever_noticia.php?' . $parametros,
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

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description"
		content="Acompanhe as últimas notícias publicadas na plataforma Notícias Atuais e publique as suas.">
	<title>Notícias Atuais — Últimas notícias</title>

	<!-- Fontes -->
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

	<!-- Bootstrap 5 CSS -->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
	<!-- Font Awesome -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
	<!-- Estilos da página inicial -->
	<link rel="stylesheet" href="assets/css/home.css">
</head>

<body class="home">
	<a class="skip-link" href="#ultimas-noticias">Pular para as notícias</a>

	<!-- Barra de navegação -->
	<header class="site-nav">
		<nav class="navbar navbar-expand-lg navbar-light">
			<div class="container">
				<a class="navbar-brand" href="index.php">
					<span class="brand-mark"><i class="fas fa-newspaper"></i></span>
					<span>Notícias <span class="brand-sub">Atuais</span></span>
				</a>

				<button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse"
					data-bs-target="#navHome" aria-controls="navHome" aria-expanded="false"
					aria-label="Abrir menu de navegação">
					<span class="navbar-toggler-icon"></span>
				</button>

				<div class="collapse navbar-collapse" id="navHome">
					<ul class="navbar-nav me-auto mb-0 ms-lg-3">
						<li class="nav-item">
							<a class="nav-link nav-link-soft" href="#ultimas-noticias">Últimas notícias</a>
						</li>
						<?php if ($usuarioLogado): ?>
							<li class="nav-item">
								<a class="nav-link nav-link-soft" href="views/listar_noticias.php">Todas as notícias</a>
							</li>
							<li class="nav-item">
								<a class="nav-link nav-link-soft" href="views/escrever_noticia.php">Escrever notícia</a>
							</li>
						<?php endif; ?>
					</ul>

					<div class="nav-actions">
						<?php if ($usuarioLogado): ?>
							<a class="btn btn-ghost btn-sm" href="views/home.php">
								<i class="fas fa-gauge-high me-2"></i>Painel
							</a>

							<div class="dropdown">
								<button class="btn btn-user dropdown-toggle" type="button" data-bs-toggle="dropdown"
									aria-expanded="false">
									<span class="avatar avatar-sm"><?= esc(inicial($nomeUsuario)) ?></span>
									<span class="text-truncate d-none d-sm-inline"><?= esc($nomeUsuario) ?></span>
									<span class="text-truncate d-sm-none"><?= esc(explode(' ', $nomeUsuario)[0]) ?></span>
								</button>
								<ul class="dropdown-menu dropdown-menu-end">
									<li>
										<h6 class="dropdown-header text-uppercase small text-muted mb-1">Sessão iniciada
										</h6>
									</li>
									<li><a class="dropdown-item" href="views/home.php"><i class="fas fa-gauge-high"></i>
											Painel</a></li>
									<li><a class="dropdown-item" href="views/escrever_noticia.php"><i
												class="fas fa-pen-to-square"></i> Escrever notícia</a></li>
									<li><a class="dropdown-item" href="views/listar_noticias.php"><i
												class="fas fa-list"></i> Todas as notícias</a></li>
									<li>
										<hr class="dropdown-divider">
									</li>
									<li>
										<form method="post" action="services/logout.php">
											<button class="dropdown-item text-danger" type="submit">
												<i class="fas fa-arrow-right-from-bracket"></i> Sair
											</button>
										</form>
									</li>
								</ul>
							</div>
						<?php else: ?>
							<button class="btn btn-ghost btn-sm" type="button" data-bs-toggle="modal"
								data-bs-target="#modal_login" id="btn_abrir_login">
								<i class="fas fa-right-to-bracket me-2"></i>Entrar
							</button>
							<a class="btn btn-brand btn-sm" href="views/usuario-inscrevase.php">
								<i class="fas fa-user-plus me-2"></i>Criar conta
							</a>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</nav>
	</header>

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
								<a class="btn btn-hero-light" href="views/escrever_noticia.php">
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
					<div class="d-flex align-items-center gap-2">
						<span class="count-pill">
							<i class="fas fa-newspaper"></i>
							<?= pluralNoticias(count($publicacoes)) ?>
						</span>
						<?php if ($usuarioLogado): ?>
							<a class="btn btn-ghost btn-sm" href="views/listar_noticias.php">Ver todas</a>
						<?php endif; ?>
					</div>
				</div>

				<?php if ($destaque): ?>
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
												<span class="tag"><?= esc($tag) ?></span>
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
														<span class="tag"><?= esc($tag) ?></span>
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

	</main>

	<!-- Rodapé -->
	<footer class="site-footer">
		<div class="container">
			<div class="row gy-4">
				<div class="col-lg-5">
					<a class="footer-brand" href="index.php">
						<span class="brand-mark brand-mark--sm"><i class="fas fa-newspaper"></i></span>
						Notícias Atuais
					</a>
					<p class="footer-text mt-3 mb-0">
						Um espaço para acompanhar e publicar as notícias que importam, com leitura gratuita para
						todos.
					</p>
				</div>

				<div class="col-6 col-lg-3">
					<h2 class="footer-title">Navegar</h2>
					<ul class="footer-links">
						<li><a href="#ultimas-noticias">Últimas notícias</a></li>
						<?php if ($usuarioLogado): ?>
							<li><a href="views/home.php">Painel</a></li>
							<li><a href="views/escrever_noticia.php">Escrever notícia</a></li>
							<li><a href="views/listar_noticias.php">Todas as notícias</a></li>
						<?php else: ?>
							<li><a href="views/usuario-inscrevase.php">Criar conta</a></li>
							<li><a href="#" data-bs-toggle="modal" data-bs-target="#modal_login">Entrar</a></li>
						<?php endif; ?>
					</ul>
				</div>

				<div class="col-6 col-lg-4">
					<h2 class="footer-title">Sobre</h2>
					<p class="footer-text mb-0">
						Conteúdo produzido pela comunidade. Tem dúvidas ou sugestões? Entre em contato com a equipe
						pelo painel do sistema.
					</p>
				</div>
			</div>

			<hr class="footer-divider">

			<div class="d-flex flex-column flex-sm-row justify-content-between gap-2 footer-bottom">
				<span>© <?= date('Y') ?> Notícias Atuais. Todos os direitos reservados.</span>
				<span>Feito com <i class="fas fa-heart"></i> para quem gosta de estar bem informado.</span>
			</div>
		</div>
	</footer>

	<?php if (!$usuarioLogado): ?>
		<!-- Modal de login -->
		<div class="modal fade" id="modal_login" tabindex="-1" aria-labelledby="loginModalTitle"
			aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content border-0 rounded-4 shadow-lg">
					<div class="modal-header border-0 pb-0">
						<h2 class="modal-title h5 fw-bold" id="loginModalTitle">Entrar</h2>
						<button type="button" class="btn-close" data-bs-dismiss="modal"
							aria-label="Fechar"></button>
					</div>
					<div class="modal-body p-4">
						<?php if ($erro === 1): ?>
							<div class="alert alert-danger py-2 small" role="alert">
								Usuário ou senha inválido(s). Confira os dados e tente novamente.
							</div>
						<?php elseif ($erro === 2): ?>
							<div class="alert alert-warning py-2 small" role="alert">
								É necessário entrar para acessar esta página.
							</div>
						<?php endif; ?>

						<form method="post" action="services/valida_acesso.php" id="formLogin">
							<div class="form-floating mb-3">
								<input type="text" class="form-control" id="campo_usuario" name="login"
									placeholder="Usuário" autocomplete="username" required>
								<label for="campo_usuario">Usuário</label>
							</div>
							<div class="form-floating mb-3">
								<input type="password" class="form-control" id="campo_senha" name="senha"
									placeholder="Senha" autocomplete="current-password" required>
								<label for="campo_senha">Senha</label>
							</div>
							<button type="submit" class="btn btn-primary w-100">Entrar</button>
						</form>

						<div class="text-center text-muted small my-3">ou</div>

						<a class="btn btn-outline-success w-100" href="views/usuario-inscrevase.php">
							<i class="fas fa-user-plus me-2"></i>Criar uma conta
						</a>
					</div>
				</div>
			</div>
		</div>
	<?php endif; ?>

	<!-- Bootstrap 5 JS Bundle with Popper -->
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
