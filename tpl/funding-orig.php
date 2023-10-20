<?php

$pageTitle = _("Encouragez-nous !");
$pageDescription = _("");

include('cesiumDownloads.php');
// require_once('vendor/crowdfunding2/Crowdfunding.class.php');
// require_once('vendor/crowdfunding2/Chart.class.php');
// require_once('vendor/crowdfunding2/Graph.class.php');


include('head.php');

?>


<article id="funding">
	<?php
	if (!empty($subpage)) {
		
		echo '
		<p>
			'. _('Votre téléchargement de Cesium doit être en cours.') . ' 
			'. sprintf(_('Sinon, <a href="%s">cliquez ici</a>.'), $cesiumDownloads[substr($subpage, 1)]['url']) .'
		</p>';
	}
	?>
	
	<h1><?php echo _("Merci aux donateurs !"); ?></h1>
	
	<p>Les développeurs remercient chaleureusement toutes les personnes qui, le mois dernier, ont financé en Ğ1 le projet Duniter&nbsp;:</p>
	
	<?php
	
	/*
	$today = new DateTime();
	
	$lastMonthSameDay = (clone $today)->sub(new DateInterval('P1M'));
	$lastMonthStart = new DateTime($lastMonthSameDay->format('Y-m-') . '01');
	$lastMonthEnd = new DateTime((clone $lastMonthSameDay)->format('Y-m-t'));
	
	$lastMonthCF = new Crowdfunding(FUNDING_PUBKEY, 'relative', $lastMonthStart->format('Y-m-d'), $lastMonthEnd->format('Y-m-d'));

	$donors = $lastMonthCF->getDonors();

	if (empty($donors)) {

		echo _('Pas encore de donateurs');

	} else {
		
		echo '<ul class="donorsList">';

		foreach ($donors as $donor) {
			
			$donorProfile = $lastMonthCF->getDonorCesiumPlusProfile($donor);
			
			echo '

			<li>';
				echo '
				<a href="https://demo.cesium.app/#/app/wot/'. $donor .'/">';
					
					$avatar = $donorProfile->getAvatar();
					
					if (!empty($avatar)) {
						
						echo '<img src="data:'. $avatar->getContentType(). ';base64, '. $avatar->getContent() .'" />';
					
					} else {
						
						echo '<img src="'. DEFAULT_AVATAR .'" />';
					}
					
					
					echo '
					<span class="name">
						<span>
							'. $donorProfile->getName() .'
						</span>
					</span>
				</a>
				
			</li>';
		}

		echo '</ul>';
	}
	*/
	?>

	
	<h2>Soutenir Duniter</h2>
	
	<p>
		Si vous aussi vous souhaitez soutenir le projet Duniter, c'est simple&nbsp;: 
	</p>

	<div id="pubkey-and-copy-button">
		<p class="pubkey">
			Copiez la clef suivante dans votre presse-papier&nbsp;: 

			<input id="pubkey" type="text" value="<?php echo FUNDING_PUBKEY; ?>" />
		</p>

		<p class="CTA-button">
			<button id="copyButton">
				Copier la clef
			</button>
		</p>

		<div id="successMsg">
			<p>Et maintenant collez-la dans l'annuaire Cesium afin de faire votre don 😉</p>
			<p style="text-align: center;">Merci pour votre générosité ❤️</p>
		</div>
	</div>
	
	
	<!--
	<h2>Progression du crowdfunding du mois en cours</h2>
	
	<figure id="chart"></figure>
	-->
	
	
	
	
	<!--
	<h2>Progression du crowdfunding du mois en cours</h2>
	<section class="text-box">
		<p>
			L'adoption de la Ğ1 est lente.
		</p>
		
		<p>
			Cela tient principalement à deux choses&nbsp;:
		</p>

		<ol>
			<li>il semble difficile de convertir des gens à la monnaie libre, pour diverses raisons (la monnaie, c'est compliqué, la confiance n'est pas forcément là a prior, etc.)</li>
			<li>les logiciels de l'écosystème (Cesium, Duniter, etc.) doivent encore être améliorés pour garantir l'expansion de la Ğ1 (par exemple en permettant de valider les transactions plus sûrement et plus rapidement)</li>
		</ol>

		<h2>
			Comment vous pouvez contribuer au développement de la Ğ1
		</h2>
		
		<p>
			La situation actuelle est la suivante&nbsp;:
		</p>

		<p>
			Certains développeurs de la Ğ1 (par exemple Eloïs, qui développe Duniter, le moteur 
			de la blockchain) ont fait le choix de démissionner de leur emploi pour se concentrer 
			sur le développement de l'écosystème logiciel de la Ğ1. Les autres développeurs, eux, 
			ont choisi de sacrifier leur temps libre, souvent leurs soirs, voire leurs week-ends.
		</p>

		<p>
			Mais cette situation ne permet pas un développement très rapide des logiciels 
			car, comme tout un chacun, un développeur n'a que 24&nbsp;h dans une journée.
		</p>

		<p>
			Cependant, il y a deux moyens par lesquels vous pouvez contribuer à 
			accélérer le processus&nbsp;:
		</p>
		
		<h3>
			Moyen n°1&nbsp;: Vendre, en Ğ1, des biens et services
		</h3>
		
		<p>
			La valeur d'une monnaie tient aux biens et services qui s'échangent dans cette monnaie.
		</p>

		<p>
			Plus il y aura de biens et services en vente en Ğ1, plus la monnaie libre sera intéressante.
		</p>
		
		<p>
			En vendant un bien ou un service, vous permettez aux développeurs de continuer à travailler
			à l'amélioration de la Ğ1, de deux manières&nbsp;:
		</p>
		
		<dl>
			<dt>
				directement
			</dt>
				<dd>
					...si vous vendez un bien ou service à un développeur. 
					À titre d'exemple, Éloïs, qui en 2020 est le développeur principal de Duniter 
					(logiciel crucial car c'est le moteur de la blockchain)
					<a href="https://www.gchange.fr/#/app/market/view/AXIVawVxUm73BnXnuMPx/cherche-logement-a-louer-toute-lanne-en-1-en-occitanie">cherche un logement à louer en 
					Occitanie</a>. Faites passer le mot ;-)
				</dd>

			<dt>
				indirectement (mais c'est tout aussi utile&nbsp;!)
			</dt>
				<dd>
					...si vous vendez à quelqu'un qui ne développe pas la Ğ1. 
					En effet, un utilisateur de la Ğ1, s'il voit qu'il peut acheter en Ğ1 
					des biens et services qui l'intéresse, sera à son tour plus 
					motivé à mettre des choses en vente, et cela peut, de fil en aiguille, conduire à 
					la mise en vente par certains junistes de biens ou services que les développeurs recherchent.
				</dd>
		</dl>

		<h3>
			Moyen n°2&nbsp;: En finançant, en Ğ1, les développeurs
		</h3>

		<p>
			Il existe des caisses de côtisations qui permettent à la communauté Ğ1 de valoriser, en Ğ1, 
			les contributions que les développeurs apportent à l'écosystème logiciel de la Ğ1.
		</p>
		
		<p>
			Chaque mois, une vingtaine de contributeurs se voient gratifiés d'au moins 15 DU<sub>Ğ1</sub> 
			pour leurs travaux sur Cesium, Silkaj, Sakia, Duniter, etc... Il est donné davantage à certains 
			contributeurs, sur divers critères, comme l'impact espéré de leur contribution, ou encore la quantité 
			de travail nécessaire à la production du code.
		</p>
		
		<p>
			Ces rémunérations sont faites en toute transparence ; et vous pouvez les retrouver 
			sur le site et le forum de Duniter, ainsi que dans la blockchain.
		</p>
		
		<p>
			Nous aimerions augmenter progressivement la rémunération des développeurs 
			jusqu'à atteindre des montants qui permettent à ceux qui ont quitté leur 
			ancien travail (celui qui était rémunéré en monnaie non-libre) de continuer 
			à travailler à l'amélioration de la Ğ1 sur le long terme.
		</p>
		
		<p>
			Ce mois-ci, nous aimerions donc atteindre la somme de <?php echo FUNDING_TARGET; ?> DU<sub>Ğ1</sub>. 
			Voilà où nous en sommes par rapport à cet objectif&nbsp;:
		</p>
		
		

	
	</section>
	
	-->
</article>

<?php 

/*
$currentCF = new Crowdfunding(FUNDING_PUBKEY, 'relative');
$currentCF->setTarget(FUNDING_TARGET);
$chart = new Chart($currentCF);

$targetGraph = new Graph($chart->getTargetLinePoints(), _('Objectif'));
$targetGraph->setStyle('type', 'line');
$targetGraph->setStyle('borderColor', 'hsl(348.8, 89.2%, 52.9%)');
$targetGraph->setStyle('borderDash', [5, 5]);
$targetGraph->setStyle('radius', 0);
$targetGraph->setStyle('fill', false);
$chart->addGraph($targetGraph);

$amountCumulativeGraph = new Graph($chart->getAmountCollectedByDayCumulativePoints(), _('Montant total récolté'));
$amountCumulativeGraph->setStyle('type', 'line');
$amountCumulativeGraph->setStyle('borderColor', '#301873');
$amountCumulativeGraph->setStyle('backgroundColor', '#301873');
$amountCumulativeGraph->setStyle('lineTension', 0);
$amountCumulativeGraph->setStyle('pointRadius', 1);
$amountCumulativeGraph->setStyle('borderWidth', 2);
$amountCumulativeGraph->setStyle('steppedLine', false);
$chart->addGraph($amountCumulativeGraph);


echo $chart->getScripts(LANG, '#chart', $rootURL . '/vendor/crowdfunding2/');
*/

?>

<script src="<?php echo $rootURL; ?>/lib/js/jquery-3.4.1.min.js"></script>
<script src="<?php echo $rootURL; ?>/lib/js/counter.js"></script>
<script>
$(document).ready(function(){	

	$('.progress-bar').animate({

		width: '<?php //echo $currentCF->getPercentage(); ?>%'

	}, 1300, '');
});
</script>

<script>
function copy() {
	
	var copyText = document.querySelector("#pubkey");
	copyText.select();
	document.execCommand("copy");

	var successMsg = document.querySelector("#successMsg");
	successMsg.style.opacity = "1";
	/*successMsg.style.height = "3em";*/

	var copyButton = document.querySelector("#copyButton");
	copyButton.style.animation = "none";
	
}

function support() {
	
	var pubkeyAndCopyButton = document.querySelector("#pubkey-and-copy-button");
	var supportButtonContainer = document.querySelector("#supportButtonContainer");
	supportButtonContainer.style.opacity = "0";
	supportButtonContainer.style.height = "0";
	pubkeyAndCopyButton.style.height = "100%";
	pubkeyAndCopyButton.style.opacity = "1";
	
	var supportButton = document.querySelector("#supportButton");
	$(this).style.animation = "none";
}

document.querySelector("#copyButton").addEventListener("click", copy);
document.querySelector("#supportButton").addEventListener("click", support);
</script>
