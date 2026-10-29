<?php
$pageTitle = $pageTitle ?? 'Notícias Atuais';
$pageDescription = $pageDescription ?? 'Acompanhe as últimas notícias publicadas na plataforma Notícias Atuais.';
$pageStyles = $pageStyles ?? [];
?>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="<?= esc($pageDescription) ?>">
	<title><?= esc($pageTitle) ?></title>

	<!-- Fontes -->
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

	<!-- Bootstrap 5 CSS -->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
	<!-- Font Awesome -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
	<!-- Estilos da página -->
	<link rel="stylesheet" href="assets/css/home.css">
	<?php foreach ($pageStyles as $style): ?>
		<link rel="stylesheet" href="<?= esc($style) ?>">
	<?php endforeach; ?>
</head>
