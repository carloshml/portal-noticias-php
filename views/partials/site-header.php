<?php
$homeUrl = $homeUrl ?? 'index.php';
$urlNoticias = $urlNoticias ?? 'noticias';
$navUltimasHref = $navUltimasHref ?? $homeUrl . '#ultimas-noticias';
$paginaAtual = $paginaAtual ?? 'home';
?>
<!-- Barra de navegação -->
<header class="site-nav">
	<nav class="navbar navbar-expand-lg navbar-light">
		<div class="container">
			<a class="navbar-brand" href="<?= esc($homeUrl) ?>">
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
						<a class="nav-link nav-link-soft" href="<?= esc($navUltimasHref) ?>">Últimas notícias</a>
					</li>
					<li class="nav-item">
						<a class="nav-link nav-link-soft" href="<?= esc($urlNoticias) ?>"
							<?= $paginaAtual === 'noticias' ? 'aria-current="page"' : '' ?>>Notícias</a>
					</li>
					<?php if ($usuarioLogado): ?>
						<li class="nav-item">
							<a class="nav-link nav-link-soft" href="views/listar_noticias.php">Gerenciar notícias</a>
						</li>
						<li class="nav-item">
							<a class="nav-link nav-link-soft" href="views/noticia-form.php">Escrever notícia</a>
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
								<li><a class="dropdown-item" href="views/noticia-form.php"><i
											class="fas fa-pen-to-square"></i> Escrever notícia</a></li>
								<li><a class="dropdown-item" href="views/listar_noticias.php"><i
											class="fas fa-list"></i> Gerenciar notícias</a></li>
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
