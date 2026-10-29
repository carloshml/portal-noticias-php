<?php
$erro = isset($erro) ? (int) $erro : 0;
?>
<?php if (!$usuarioLogado): ?>
	<!-- Modal de login -->
	<div class="modal fade login-modal" id="modal_login" tabindex="-1" aria-labelledby="loginModalTitle"
		aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered modal-lg">
			<div class="modal-content">
				<div class="row g-0">
					<div class="col-md-5 login-aside d-none d-md-flex flex-column p-4 p-lg-5">
						<span class="brand-mark brand-mark--light mb-4"><i class="fas fa-newspaper"></i></span>
						<h2 class="login-aside__title">Bem-vindo de volta</h2>
						<p class="login-aside__text mb-0">
							Entre para publicar notícias, editar suas publicações e acessar o painel de gestão.
						</p>
						<ul class="login-aside__list mt-auto pt-4">
							<li><i class="fas fa-check"></i> Publique quando quiser</li>
							<li><i class="fas fa-check"></i> Edite suas notícias</li>
							<li><i class="fas fa-check"></i> Acesso ao painel</li>
						</ul>
					</div>

					<div class="col-12 col-md-7">
						<div class="p-4 p-lg-5">
							<div class="d-flex justify-content-between align-items-start gap-3">
								<div>
									<h3 class="h5 fw-bold mb-1" id="loginModalTitle">Acessar sua conta</h3>
									<p class="text-muted small mb-0">Use seu login e senha para continuar.</p>
								</div>
								<button type="button" class="btn-close" data-bs-dismiss="modal"
									aria-label="Fechar"></button>
							</div>

							<?php if ($erro === 1): ?>
								<div class="login-alert login-alert--error mt-4" role="alert">
									<i class="fas fa-circle-exclamation"></i>
									<span>Usuário ou senha inválido(s). Confira os dados e tente novamente.</span>
								</div>
							<?php elseif ($erro === 2): ?>
								<div class="login-alert login-alert--warning mt-4" role="alert">
									<i class="fas fa-lock"></i>
									<span>É necessário entrar para acessar esta página.</span>
								</div>
							<?php endif; ?>

							<form method="post" action="services/valida_acesso.php" id="formLogin" class="mt-4">
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

								<button type="submit" class="btn btn-brand w-100">Entrar</button>
							</form>

							<div class="login-divider"><span>ou</span></div>

							<a class="btn btn-ghost w-100" href="views/usuario-inscrevase.php">
								<i class="fas fa-user-plus me-2"></i>Criar uma conta gratuita
							</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php endif; ?>
