<?php
if (!$pageIsHome) {

	$pageTitle .= " - "  . "Cesium Ğ1";

}

$bodyIds = !isset($bodyIds) ? '' : $bodyIds;

?>
<!DOCTYPE html>
<html lang="fr-FR">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
		<meta charset="utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
		<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />

		<title><?php echo $pageTitle; ?></title>
		<link rel="shortcut icon" type="image/png" href="<?php echo $rootURL; ?>/img/logo.small.cs.dune.200x200.png" />
		<meta name="generator" content="BorisPaing" />


		<meta name="description" content="<?php echo $pageDescription; ?>" />

		<meta property="og:title" content="<?php echo $pageTitle; ?>"/>
		<meta property="og:description" content="<?php echo $pageDescription; ?>" />
		<meta property="og:url" content="http://cesium.app/" />
		<meta property="og:site_name" content="Cesium Ğ1" />

		<meta property="og:image" content="<?php echo $rootURL . '/i18n/' . LANG_FOLDER . '/contents' . dgettext('menu', '/accueil') . dgettext('home', '/Cesium-G1-maquette.png'); ?>" />
		<meta property="og:type" content="website" />

		<meta name="twitter:description" content="<?php echo $pageDescription; ?>" />
		<meta name="twitter:title" content="<?php echo $pageTitle; ?>" />

		<meta name="twitter:image" content="<?php echo $rootURL . '/i18n/' . LANG_FOLDER . '/contents' . dgettext('menu', '/accueil') . dgettext('home', '/Cesium-G1-maquette.png'); ?>" />
		<meta name="twitter:card" content="summary_large_image" />

		<script type="text/javascript" src="<?php echo $rootURL; ?>/lib/lazyImg.min.js"></script>

		<?php
		foreach ($availableLanguages as $isoCode => $v)
		{
			echo '<link rel="alternate" hreflang="'. $isoCode .'" href="'. $rootURL .'/'. $isoCode .'/" />';
		}
		?>

		<?php

		if ($_SERVER['SERVER_NAME'] == 'localhost') {

			echo '
			<link rel="stylesheet/less" type="text/css" href="' . $rootURL . '/lib/style.less" />
			<script src="' . $rootURL . '/lib/less-3.9.0.min.js" ></script>
			';

		} else {

			echo '<link rel="stylesheet" type="text/css" media="screen" href="' . $rootURL . '/lib/style.css" />';

		}

		?>

		<?php if (FUNDING_ALT) echo '<link type="text/css" rel="stylesheet" media="screen" href="'. $rootURL .'/lib/style-funding-alt-1.css" />'; ?>

	</head>
	<body id="<?php echo $bodyIds; ?>">
			<?php
				$menu =
					array(
						[
							'uri' => dgettext('menu', '/'),
							'label' => dgettext('menu', 'Accueil')
						],
						[
							'uri' => dgettext('menu', '/fonctionnalites'),
							'label' => dgettext('menu', 'Fonctionnalités')
						],
						[
							'uri' => dgettext('menu', '/telechargement'),
							'label' => dgettext('menu', 'Télécharger')
						],
                        [
                                'url' => 'https://demo.cesium.app',
                                'label' => dgettext('menu', 'Demo')
                        ],
						[
							'uri' => dgettext('menu', '/tutoriel-cesium') . '/',
							'label' => dgettext('menu', 'Tutoriel')
						],
						[
							'uri' => dgettext('menu', '/merci'),
							'label' => '❤ ' . dgettext('menu', 'Encouragez-nous !'),
							'class' => 'support-us'
							// ❤
						]
					);
			?>

			<header>
				<?php

				$element = $pageIsHome ? 'h1' : 'h2';

				echo '
				<'. $element .' class="site-title">
					<a href="'. parseURI("/") .'">
						Cesium Ğ1
					</a>
				</'. $element .'>';
				?>

				<p class="site-logo">
					<a href="<?php echo parseURI("/"); ?>">
						<img src="<?php echo $rootURL; ?>/lib/logo.small.cs.dune.svg" alt="logo Cesium Ğ1" />
					</a>
				</p>

				<nav>
					<button><span>Afficher le menu</span></button>

					<ul>

						<?php

						$itemsNb = count($menu);

						for ($i = 0; $i < $itemsNb; ++$i)
						{
							$classes = '';


							$classes .= isset($menu[$i]['class']) ? ' '. $menu[$i]['class'] . '"' : '';

							if (isset($menu[$i]['uri'])) {

								$classes .= ($_SERVER['REQUEST_URI'] == parseURI($menu[$i]['uri'])) ? ' active' : '';
								$href = parseURI($menu[$i]['uri']);

							} else {

								$href = $menu[$i]['url'];
							}

							$classes = !empty($classes) ? ' class="'. $classes .'"' : '';

							echo '
							<li'. $classes . '>
								<a role="menuitem" href="'. $href . '">
									<span>'. $menu[$i]['label'] .'</span>
								</a>
							</li>';
						}
						?>

					</ul>
				</nav>
			</header>

			<main>
