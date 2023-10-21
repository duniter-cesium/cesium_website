<?php

textdomain('features');

$pageTitle = _('Fonctionnalités');
$pageDescription = _("Fonctionnalités de Cesium : gérer son comptes monnaie libre Ğ1, rechercher des membres dans l'annuaire, trouver des entreprises qui acceptent la Ğ1.");

include('head.php');

?>
<article id="features">
	
	<?php
	echo '<h1>'. _('Fonctionnalités') . '</h1>';
	
	
	// Documentation :
	// 
	// https://packagist.org/packages/geoip2/geoip2
	// 
	require_once 'vendor/autoload.php';
	use GeoIp2\Database\Reader;
	$reader = new Reader('vendor/GeoLite2-Country/GeoLite2-Country.mmdb');
	$record = $reader->country(getUserIpAddress());
	
	$path = './i18n/' . LANG_FOLDER . '/contents' . _('/fonctionnalites');
	$mapFileName = _('/carte-des-utilisateurs') . _('/pays') . sprintf('/%s.png', $record->country->isoCode);
	
	if (!file_exists($path . $mapFileName)) {
		
		/*
		## If country screenshot isn't found, fallback on continent :
		
		$record->continent->code returns :
		
		- AF for Africa
		- AN for Antarctica
		- AS for Asia
		- EU for Europe
		- NA for North america
		- OC for Oceania
		- SA for South america
		*/
		
		$mapFileName = _('/carte-des-utilisateurs') . _('/continents') . sprintf('/%s.png', $record->continent->code);
		
		if (!file_exists($path . $mapFileName))
		{
			$mapFileName = _('/carte-des-utilisateurs') . _('/monde.png');
		}
		
	}
	
		
	$features = 
		array(
			[
				'name' => _('Créer votre compte Ğ1'), 
				'desc' => [_('Choisissez un mot de passe ultra-sécurisé et créez votre compte en quelques minutes seulement.')], 
				'img' => _('/inscription.png')
			], 
			[
				'name' => _('Recevez des Ğ1'), 
				'desc' => [
				           _('Dès votre compte créé, vous pouvez recevoir vos premières Ğ1.'), 
						   _('Il suffit pour cela de partager votre clef publique à la personne qui souhaite vous en envoyer.')
				          ], 
				'img' => _('/recevoir.png')
			],
			[
				'name' => _('Recherche par nom'), 
				'desc' => [
				           _("Trouvez des membres de la Ğ1 d'après leur nom, prénom ou identifiant dans la blockchain.")
				          ], 
				'img' => _('/recherche-par-nom.png')
			], 
			[
				'name' => _('Envoyez des Ğ1'), 
				'desc' => [
				           _("Dans Cesium, il est facile de faire un virement en Ğ1 pour régler un achat ou faire un don.")
				          ], 
				'img' => _('/envoyer.png')
			], 
			[
				'name' => _('Envoyez des messages aux autres utilisateurs'), 
				'desc' => [
				           _("Gardez le contact avec les autres membres grâce à la messagerie intégrée dans Cesium.")
				          ], 
				'img' => _('/messagerie.png')
			], 
			[
				'name' => _("Soyez notifié dès qu'il y a de l'activité sur votre compte"), 
				'desc' => [
				           _("Cesium dispose d'un système de notification par mail."), 
				           _("Activez-le pour être informé dès qu'une transaction a lieu ou lorsque quelqu'un vous envoie un message."), 
				          ], 
				'img' => _('/notifications-par-mel.png')
			], 
			[
				'name' => _("Trouvez les entreprises qui acceptent la Ğ1"), 
				'desc' => [
				           _("Cesium intègre un annuaire qui vous permet de trouver facilement les entreprises qui acceptent la Ğ1 comme moyen de paiement."), 
				           _("Vous pouvez aussi y référencer gratuitement votre entreprise."), 
				          ], 
				'img' => _('/pages-jaunes.png')
			], 
			[
				'name' => _("Scanner de QR codes intégré"), 
				'desc' => [
				           _("L'application Cesium pour téléphone vous permet de scanner le QR code d'un portefeuille."), 
				           _("Cela vous évite de devoir rechercher le membre par son prénom."), 
				           _("Avec les QR codes, pas de risque de se tromper de destinataire !"), 
				          ], 
				'img' => _('/scanner-de-QR-codes.jpg')
			], 
			[
				'name' => _("Surveillez plusieurs portefeuilles"), 
				'desc' => [
				           _("Consultez facilement le solde de de vos autres portefeuilles (entreprise, association, financement participatif, etc.)")
				          ], 
				'img' => _('/suivre-d-autres-portefeuilles.png')
			], 
			/*
			[
				'name' => _('Trouvez des membres près de chez vous'),
				'desc' => [
				           _("Consultez la carte des membres afin de trouver les membres les plus proches de chez vous."),
				           _("Vous pouvez les contacter pour faire vos premiers échanges ou demander des certifications."),
				          ],
				'img' => $mapFileName
			],
			*/
		);
	
	foreach ($features as $f)
	{
		echo '
		<figure>
			<img src="'. $rootURL .'/i18n/' . LANG_FOLDER . '/contents' . _('/fonctionnalites') . $f['img']  .'" />

			<figcaption>
				<h2>'. $f['name'] .'</h2>';

				foreach ($f['desc'] as $d)
				{
					echo '<p>'. $d .'</p>';
				}

				echo '
			</figcaption>
		</figure>';
	}
	
	?>
</article>

<?php

include('foot.php');
