<?php
$homeUrl = $homeUrl ?? 'index.php';
$urlNoticias = $urlNoticias ?? 'noticias';
$navUltimasHref = $navUltimasHref ?? $homeUrl . '#ultimas-noticias';
?>
<!-- Rodapé -->
<footer class="site-footer">
	<div class="container">
		<div class="row gy-4">
			<div class="col-lg-5">
				<a class="footer-brand" href="<?= esc($homeUrl) ?>">
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
					<li><a href="<?= esc($navUltimasHref) ?>">Últimas notícias</a></li>
					<li><a href="<?= esc($urlNoticias) ?>">Notícias</a></li>
					<?php if ($usuarioLogado): ?>
						<li><a href="views/home.php">Painel</a></li>
						<li><a href="views/escrever_noticia.php">Escrever notícia</a></li>
						<li><a href="views/listar_noticias.php">Gerenciar notícias</a></li>
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
