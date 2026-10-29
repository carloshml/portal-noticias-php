<!-- Bootstrap 5 JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
	(function () {
		var modalEl = document.getElementById('modal_login');
		if (!modalEl) {
			return;
		}

		var erro = <?= isset($erro) ? (int) $erro : 0 ?>;
		if (erro === 1 || erro === 2) {
			bootstrap.Modal.getOrCreateInstance(modalEl).show();
		}

		modalEl.addEventListener('shown.bs.modal', function () {
			var campo = document.getElementById('campo_usuario');
			if (campo) {
				campo.focus();
			}
		});
	})();
</script>
