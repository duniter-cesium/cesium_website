<?php

/*

-----

Ce script est basé sur la "barre de financement intégrable" développée par Pierre-Jean Chancellier.

Son travail original se trouve ici :

https://git.duniter.org/paidge/barre-de-financement-int-grable

-----

This script is based on Pierre-Jean Chancellier's "barre de financement intégrable".

See original work at :

https://git.duniter.org/paidge/barre-de-financement-int-grable

*/

require_once('conf.php');
require_once('lib/phpqrcode/qrlib.php');
require_once('Donation.class.php');
require_once('Donor.class.php');

date_default_timezone_set('Europe/Paris');

class Crowdfunding {

	/**********************
	 * Constants
	 **********************/

	const PUBKEY_FORMAT = '#^[a-zA-Z1-9]{43,44}$#';

	const DATE_FORMAT = 'Y-m-d';

	private $units = ['quantitative','relative'];

	private $truePossibleValues = ['true','1', 'yes'];

	private $qrCodesFolder = __DIR__ . '/img/qrcodes';

	private $qrCodePath = NULL;

	private $logosFolder = __DIR__ . '/img/logos';

	private $logo = NULL;

	private $logoPath = NULL;

	private $validDisplayTypes = ['img', 'svg', 'html'];

	private $cacheDir = __DIR__ . '/cache-cache/';

	private $isActivatedCache = true;

	private $cacheLongevity = 43200; // in seconds

	/**********************
	 * General parameters
	 **********************/

	private $pubkey;

	private $target = NULL;

	private $startDate = NULL;

	private $endDate = NULL;

	private $nodes = [

		// Fast ones
		'duniter971.dns1.us',
		'duniter.g1.pfouque.xyz',
		'duniter.pini.fr',
		'g1.asycn.io',
		'g1.brussels.ovh',
		'g1.cgeek.fr',
		'g1.computhings.be'

		/*
		'duniter.g1.1000i100.fr',
		'duniter-g1.p2p.legal',
		'duniter.normandie-libre.fr',
		'g1.mithril.re',
		'g1.presles.fr',
		'duniter.vincentux.fr',
		'g1.le-sou.org',
		'g1.donnadieu.fr',
		*/

		/*
		// Node that timeout
		'g1.duniter.org',
		'g1.librelois.fr',
		'g1.cgeek.fr',
		'remuniter.cgeek.fr',
		'g1.monnaielibreoccitanie.org',

		// Nodes with other issues
		'g1.duniter.inso.ovh',
		*/
	];

	private $cesiumPlusNodes = [

		'g1.data.e-is.pro',
		'g1.data.presles.fr',
		'g1.data.adn.life',
		'g1.data.duniter.fr',
		'g1.data.le-sou.org'
	];

	private $preferredNode = NULL;
	private $preferredCesiumPlusNode = NULL;

	/*
	private $answeringNode = NULL;
	private $answeringCesiumPlusNode = NULL;
	*/

	// in seconds
	private $nodeTimeout = 2;
	private $nodeTimeoutIncrement = 2;
	private $cesiumPlusNodeTimeout = 15;
	private $cesiumPlusNodeTimeoutIncrement = 10;

	private $node = NULL;

	private $apiNode = 'g1.duniter.fr'; // Where the web payment gateway is located

	private $unit = 'quantitative';

	private $title = 'Financement participatif en monnaie libre';

	/**********************
	 * Display parameters
	 **********************/

	private $mustDisplayButton = false;

	private $mustDisplayPubkey = false;

	private $mustDisplayGraph = false;

	private $mustHideTitle = false;

	private $mustDisplayQRCode = false;

	private $displayType = 'html';

	private $filterMinDonation = 0;

	private $filterMinCommentLength = 0;

	private $validButtonTypes = ['api', 'copy', '2-steps-copy'];

	private $buttonType = '2-steps-copy';

	private $defaultMonthsToConsider = 3;

	/**********************
	 * Computed
	 **********************/

	private $isEvergreen = false; // monthly | forever

	private $hasStartedYet = NULL;

	private $isOver = NULL;

	private $daysLeft = NULL;



	private $monthlyAmountCollectedMean = NULL;

	private $periodDonorsNb = NULL;

	private $percentage = NULL;

	private $totalDonationPerDonor = NULL;

	private $monthsToConsider = NULL;


	private $donationsList = NULL;

	private $donorsNb = NULL;

	private $amountCollected = NULL;

	private $donorsList = NULL;

	private $donorsProfiles = NULL;


	private $meanDonation = NULL;

	private $maxDonation = NULL;

	private $minDonation = NULL;

	private $periodDonorsList = array();

	/*
	 * UD amount in quantitive, for divisions
	 */
	private $latestUdAmount = NULL;

	private $startDateUdAmount = NULL;




	/**********************
	 * Methods
	 **********************/

	/**
	 *
	 * @param $unit = 'quantitative' | 'relative'
	 * @param $displayType = NULL | 'img' | 'svg' | 'iframe'
	 */

	public function __construct ($pubkey, $unit = NULL, $startDate = NULL, $endDate = NULL, $displayType = NULL) {

		$this->setDisplayType($displayType);

		$this->setPubkey($pubkey);

		$this->setUnit($unit);

		$this->now = new DateTime();
		$this->today = new DateTime();
		$this->today->setTime(0, 0, 0);

		$this->handleDates($startDate, $endDate);
	}

	public function setFilterMinDonation ($min) {

		$this->filterMinDonation = (float) $min;
	}


	public function getFilterMinDonation () {

		return $this->filterMinDonation;
	}

	public function setFilterMinCommentLength ($length) {

		$this->filterMinCommentLength = (int) $length;
	}


	public function getFilterMinCommentLength () {

		return $this->filterMinCommentLength;
	}

	public function getEndDate() {

		return (clone $this->endDate);

	}

	private function setUnit ($unit) {

		if (!empty($unit)) {

			if (!in_array($unit, $this->units)) {

				$out = [];
				$out[] = _('L\'unité renseignée n\'existe pas.');
				$out[] = _('Vérifiez votre synthaxe.');

				$this->decease($out);

			} else {

				$this->unit = $unit;
			}
		}
	}

	private function setDisplayType ($displayType) {

		if (!empty($displayType)) {

			if (in_array($displayType, $this->validDisplayTypes)) {

				$this->displayType = $displayType;

			} else {

				$this->decease(_('Ce type d\'affichage n\'existe pas.'));
			}

		}
	}

	public function decease ($errorMsgs) {

		if (!is_array($errorMsgs)) {

			$errorMsgs = explode("\n", $errorMsgs);
		}


		if ($this->displayType == 'img') {

			$source = imagecreatetruecolor(500, 200);

			$bgColor = imagecolorallocate($source,
										  255, 255, 255);

			imagefill($source,
					  0, 0,
					  $bgColor);

			$txtColor = imagecolorallocate($source,
										   0, 0, 0);

			$errorMsgFontSize = 3;
			$x = 5;
			$y = 5;

			foreach ($errorMsgs as $msg) {

				imagestring($source, $errorMsgFontSize, $x, $y, utf8_decode($msg), $txtColor);

				$y += $errorMsgFontSize + 20;
			}


			imagepng($source);
			imagedestroy($source);

		} else if ($this->displayType == 'svg') {

			echo '<?xml version="1.0" encoding="utf-8"?>
			<svg width="580"
				 height="224"
				 style="fill:black;"
				 version="1.1"
				 xmlns="http://www.w3.org/2000/svg"
				 xmlns:xlink="http://www.w3.org/1999/xlink">

				<g style="font-family:sans-serif;">';

				$x = 25;
				$y = 25;

				foreach ($errorMsgs as $msg) {

					echo '
					<text
						style="font-size:.8rem;"
						x="'. $x .'"
						y="'. $y . '"
						dominant-baseline="hanging">
							'. $msg . '
					</text>';

					$y += 25;
				}

				echo '
				</g>
			</svg>';

		} else {

			ob_get_clean(); // to prevent error message to display inside an HTML container (case of error generated by get method calls)

			echo '<!DOCTYPE html>
			<html>
				<head>
					<meta charset="utf-8" />
					<title>'. $this->getTitle() . '</title>

					<style>

						div {

							overflow: auto;
							word-wrap: break-word;
							background-color: hsl(0, 100%, 69%);
							color: hsl(0, 100%, 19%);
							margin: 1em;
							padding: 1em;
							border-radius: 1em;
							position: fixed;
							top: 0;
							left: 0;
							width: calc(100% - 4em);
							max-height: calc(100vh - 4em);
						}
					</style>
				</head>

				<body>
					<div>';


						foreach ($errorMsgs as $msg) {

							echo '<p>' . $msg . '</p>';
						}

					echo '
					</div>
				</body>
			</html>';
		}

		exit;
	}

	public function setTarget ($target) {

		if (empty($target)) {

			$out = [];
			$out[] = _('Il manque le montant à atteindre. Vérifiez votre syntaxe.');
			$out[] = _('Vérifiez votre syntaxe.');
			$this->decease($out);

		} else {

			$target = (int)$target;

			if (!is_int($target)){

				$out = [];
				$out[] = _('Le montant n\'est pas un nombre entier.');
				$out[] = _('Vérifiez votre syntaxe.');
				$this->decease($out);

			} else if ($target == 0) {

				$out = [];
				$out[] = _('Le montant cible est nul.');
				$out[] = _('Vérifiez votre syntaxe.');
				$this->decease($out);

			} else if ($target < 0) {

				$out = [];
				$out[] = _('La montant cible inférieur à 0.');
				$out[] = _('Vérifiez votre syntaxe.');
				$this->decease($out);

			} else {

				$this->target = $target;

			}
		}
	}

	public function hasTarget() {

		return isset($this->target);
	}

	public function getTarget () {

		if (!$this->hasTarget()) {

			$out = [];
			$out[] = _('Il manque le montant à atteindre.');
			$out[] = _('Vérifiez votre syntaxe.');
			$this->decease($out);

		} else {

			return $this->target;
		}

	}

	public function setMustDisplayGraph ($displayGraphOrNot) {

		if (in_array($displayGraphOrNot, $this->truePossibleValues)) {

			$this->mustDisplayGraph = true;

		} else {

			$this->mustDisplayGraph = false;
		}
	}

	public function getMustDisplayGraph () {

		return $this->mustDisplayGraph;
	}

	public function setMustDisplayQRCode ($mustDisplayQRCode) {

		$this->mustDisplayQRCode = $mustDisplayQRCode;

		if ($mustDisplayQRCode) {

			$qrCodePath = $this->qrCodesFolder . '/' . $this->pubkey . '.png';

			if (file_exists($qrCodePath)) {

				$this->qrCodePath = $qrCodePath;

			} else {

				QRcode::png($this->pubkey, $qrCodePath);

				$this->qrCodePath = $qrCodePath;
			}
		}
	}

	public function getQRCodePath () {

		if (!file_exists($this->qrCodePath)) {

			return false;

		} else {

			return $this->qrCodePath;
		}
	}

	public function setMustHideTitle ($mustHideTitle) {

		if (in_array($mustHideTitle, $this->truePossibleValues)) {

			$this->mustHideTitle = true;

		} else {

			$this->mustHideTitle = false;

		}
	}

	public function getMustHideTitle () {

		return $this->mustHideTitle;
	}

	public function setTitle ($title) {

		if (!empty($title)) {

			$this->title = htmlspecialchars($title);
		}
	}

	public function hasLogo() {

		return !empty($this->logoPath);
	}

	public function setLogo ($logoName) {

		if ($this->displayType == 'img') {

			$logoPath = $this->logosFolder . '/png/' . $logoName . '.png';

		} else {

			$logoPath = $this->logosFolder . '/svg/' . $logoName . '.svg';
		}


		if (!file_exists($logoPath)) {

			$this->decease(_('Ce logo n\'existe pas.'));

		} else {

			$this->logoPath = $logoPath;
		}
	}

	public function getLogoPath() {

		return $this->logoPath;

	}

	public function getTitle () {

		return $this->title;
	}

	public function getMustDisplayQRCode () {

		return $this->mustDisplayQRCode;
	}

	public function getMustDisplayButton () {

		return $this->mustDisplayButton;
	}

	public function setMustDisplayPubkey ($mustDisplayPubkey) {

		if (in_array($mustDisplayPubkey, $this->truePossibleValues)) {

			$this->mustDisplayPubkey = true;

		} else {

			$this->mustDisplayPubkey = false;
		}
	}

	public function getMustDisplayPubkey () {

		return $this->mustDisplayPubkey;
	}

	public function setMustDisplayButton ($mustDisplayButton) {

		$this->mustDisplayButton = (bool) $mustDisplayButton;
	}


	public function getContributionURL () {

		if (!isset($this->contributionURL)) {

			$this->contributionURL ='https://' . $this->apiNode . '/api/#/v1/payment/' . $this->pubkey
				. '?' .
					'amount=10|20|50|100|1000'
				. '&amp;' .
					'comment=don'
				. '&amp;' .
					'redirect_url=https://%3A%252F%252F' . $this->apiNode
				. '&amp;' .
					'cancel_url=https%3A%252F%252F' . $this->apiNode;
		}

		return $this->contributionURL;

	}

	public function setPubkey ($pubkey) {

		if (empty($pubkey)) {

			$out = [];
			$out[] = _('Il manque la clé publique du compte à vérifier.');
			$out[] = _('Vérifiez votre syntaxe.');

			$this->decease($out);

		} else if (!preg_match(self::PUBKEY_FORMAT, $pubkey)) {

			$out = [];
			$out[] = _('La pubkey n\'a pas le format attendu.');
			$out[] = _('Vérifiez votre syntaxe.');
			$this->decease($out);

		} else {

			$this->pubkey = $pubkey;

		}
	}

	public function printUnit () {

		if ($this->unit == 'relative') {

			if ($this->displayType == 'img') {

				return _('DUĞ1');

			} else {

				return _('DU<sub>Ğ1</sub>');
			}

		} else {

			return _('Ğ1');
		}
	}

	protected function isDate ($date, $format){

		$a = date_parse_from_format($format, $date);

		return checkdate($a["month"], $a["day"], $a["year"]);
	}

	public function hasStartedYet () {

		if (empty($this->hasStartedYet)) {

			$this->hasStartedYet = ($this->startDate <= $this->now);

		}

		return $this->hasStartedYet;
	}

	public function isEvergreen ($bool = NULL) {

		if (isset($bool)) {

			$this->isEvergreen = $bool;

		} else {

			return $this->isEvergreen;
		}
	}

	protected function handleDates ($startDate, $endDate) {

		/* Starting date handling */

		if (empty($startDate)) {

			$this->startDate = new DateTime('first day of this month');

		} else if (!$this->isDate($startDate, self::DATE_FORMAT)) {

			$out = [];
			$out[] = _('La date de début n\'est pas correcte.');
			$out[] = _('Vérifiez votre syntaxe.');
			$this->decease($out);

		} else {

			$d = DateTime::createFromFormat(self::DATE_FORMAT, $startDate);

			if ($d === false) {

				$out = [];
				$out[] = _('La date de début n\'est pas correcte.');
				$out[] = _('Vérifiez votre syntaxe.');
				$this->decease($out);

			} else {

				$this->startDate = $d;
			}
		}

		$this->startDate->setTime(0,0,0);

		/* Ending date handling */

		if (empty($endDate)) {

			if (empty($startDate)) {

				// For everygreen campaigns (monthly crowdfunding)

				$this->isEvergreen('monthly');

			} else {

				$this->isEvergreen('forever');
			}


			$this->endDate = NULL;

		} else {

			if (!$this->isDate($endDate, self::DATE_FORMAT) ) {

				$out = [];
				$out[] = _('La date de fin est incorrecte.');
				$out[] = _(' Vérifiez votre syntaxe.');
				$this->decease($out);

			} else {

				$d = DateTime::createFromFormat(self::DATE_FORMAT, $endDate);

				if ($d === false) {

					$out = [];
					$out[] = _('La date de fin est incorrecte.');
					$out[] = _('Renseignez la au format '. self::DATE_FORMAT . '.');
					$this->decease($out);

				} else {

					$this->endDate = $d;
					$this->endDate->setTime(0,0,0);

					if (empty($startDate)) {

						$out = [];
						$out[] = _('Vous avez spécifié une date de fin, mais pas de date de début !');
						$out[] = _('Renseignez une date de début !');
						$out[] = _('Date de fin : ') . $this->endDate->format(self::DATE_FORMAT);

						$this->decease($out);

					} elseif ($this->startDate >= $this->endDate) {

						$out = [];
						$out[] = _('La date de fin est antérieure ou égale à la date de début.');
						$out[] = _('Un crowdfunding ne peut pas se terminer avant d\'avoir commencé.');
						$out[] = _('Vérifiez vos dates :');
						$out[] = _('Date de début : ') . $this->startDate->format(self::DATE_FORMAT);
						$out[] = _('Date de fin : ') . $this->endDate->format(self::DATE_FORMAT);

						$this->decease($out);

					}
				}
			}

		}
	}

	public function isOver () {

		if (empty($this->isOver)) {

			$this->isOver = (!empty($this->endDate) and ($this->endDate < $this->now));
		}

		return $this->isOver;
	}

	public function getDaysLeft () {

		if (!isset($this->daysLeft)) {

			if ($this->isEvergreen()) {

				$lastDayOfTheMonth = new DateTime($this->startDate->format('Y-m-t'));
				$this->daysLeft = intval($this->today->diff($lastDayOfTheMonth)->format('%a'));

			} elseif (empty($this->endDate)) {

				$this->daysLeft = NULL;

			} else {

				$this->daysLeft = intval($this->today->diff($this->endDate)->format('%R%a'));
			}
		}

		return $this->daysLeft;
	}

	public function getMeanDonation () {

		if (empty($this->meanDonation)) {

			$this->meanDonation = $this->amountCollected / $this->donorsNb;
		}

		return $this->meanDonation;
	}

	public function getMaxDonation () {

		if (empty($this->maxDonation)) {

			$max = 0;

			foreach ($this->donationsList as $d) {

				$max = max($max, $d->getAmount());
		   }

			$this->maxDonation = $max;
		}

		return $this->maxDonation;
	}

	public function getPubkey () {

		return $this->pubkey;
	}

	public function getMinDonation () {

		if (empty($this->minDonation)) {

			$min = 666666;

			foreach ($this->donationsList as $d) {

				$min = min($min, $d->getAmount());
		   }

			$this->minDonation = $min;
		}

		return $this->minDonation;
	}

	protected function computePercentage () {

		$this->percentage = $this->getAmountCollected() / $this->getTarget() * 100;

	}

	public function getPercentage () {

		if (!isset($this->percentage)) {

			$this->computePercentage();

		}

		return round($this->percentage);
	}

	public function getAmountCollected () {

		if (!isset($this->amountCollected)) {

			$this->fetchDonationsList();

		}

		return $this->convertIntoChosenUnit($this->amountCollected);
	}

	public function getDonorsNb () {

		if (!isset($this->donorsNb)) {

			$this->fetchDonationsList();
		}

		return $this->donorsNb;
	}

	public function fetchMonthlyMean ($monthsToConsider) {

		$nMonths = new DateInterval('P'. $monthsToConsider . 'M');
		$dateMonthsAgo = clone $this->today;
		$dateMonthsAgo->sub($nMonths);
		$dateMonthsAgo = DateTime::createFromFormat(
		                                            'Y-m-d',
		                                            $dateMonthsAgo->format('Y') . '-' .
		                                            $dateMonthsAgo->format('m') . '-' .
		                                            '01'
		                                           );

		$oneMonth = new DateInterval('P1M');
		$dateFirstDayTodaysMonth = DateTime::createFromFormat(
		                                                      'Y-m-d',
		                                                      $this->today->format('Y') . '-' .
		                                                      $this->today->format('m') . '-' .
		                                                      '01'
		                                                     );
		$dateLastDayOfPreviousMonth = clone $dateFirstDayTodaysMonth;
		$aDay = new DateInterval('P1D');
		$dateLastDayOfPreviousMonth->sub($aDay);

		$periodTotalCollected = 0;

		$tx = $this->getTransactions(
		                             $this->pubkey,
		                             $dateMonthsAgo,
		                             $dateLastDayOfPreviousMonth
		                            );

		$previousMonth = $dateMonthsAgo->format('Y-m');
		$monthlyDonors[$previousMonth] = [];
		$monthlyDonorsNb[$previousMonth] = 0;

		foreach ($tx as $t) {

			if ($t->issuers[0] != $this->pubkey) {

				foreach ($t->outputs as $o) {

					if (strstr($o, $this->pubkey)) {

						$transactionDate = new DateTime();
						$transactionDate->setTimestamp($t->time);

						$currentMonth = $transactionDate->format('Y-m');

						if ($currentMonth != $previousMonth) {

							$monthlyDonors[$currentMonth] = [];
							$monthlyDonorsNb[$currentMonth] = 0;
						}

						$donor = $t->issuers[0];

						if (!in_array($donor, $monthlyDonors[$currentMonth])) {

							$monthlyDonors[$currentMonth][] = $donor;
							++$monthlyDonorsNb[$currentMonth];
						}

						$this->addPeriodDonor($donor);

						$o = explode(':', $o);
						$transactionAmount = $o[0] / 100;

						$periodTotalCollected += $transactionAmount;

						$previousMonth = $currentMonth;
					}
				}
			}
		}

		$this->periodDonorsMean = ceil(array_sum($monthlyDonorsNb) / $monthsToConsider);

		$this->monthlyAmountCollectedMean = $this->convertIntoChosenUnit($periodTotalCollected / $monthsToConsider);

		return $this->monthlyAmountCollectedMean;
	}

	public function setButtonType ($type) {

		if (in_array($type, $this->validButtonTypes)) {

			$this->buttonType = $type;
		}
	}

	public function getButtonType () {

		return $this->buttonType;
	}

	public function getPeriodDonorsMean ($monthsToConsider) {

		return $this->periodDonorsMean;
	}

	private function addPeriodDonor ($donor) {

		if (!in_array($donor, $this->periodDonorsList)) {

			$this->periodDonorsList[] = $donor;
			++$this->periodDonorsNb;
		}
	}

	public function getPeriodDonorsNb ($monthsToConsider) {

		if (!isset($this->periodDonorsNb)) {

			$this->fetchMonthlyMean($monthsToConsider);
		}

		return $this->periodDonorsNb;
	}

	public function getMonthlyAmountCollectedMean ($monthsToConsider) {

		if (!isset($this->monthlyAmountCollectedMean)) {

			$this->fetchMonthlyMean($monthsToConsider);
		}

		return $this->monthlyAmountCollectedMean;
	}

	private function cacheIt ($json, $cacheFileFullPath) {

		if (!$this->isActivatedCache) {

			return false;
		}

		// Cache tx

		$cacheDir = substr($cacheFileFullPath, 0, strrpos($cacheFileFullPath, '/'));

		if (!file_exists($cacheDir)) {

			mkdir($cacheDir, 0777, true);

		}

		file_put_contents($cacheFileFullPath, $json);
	}

	private function getFromCache ($cacheFullPath) {

		$json = NULL;

		if (!$this->isActivatedCache) {

			return $json;
		}

		if (file_exists($cacheFullPath) and ((time() - filemtime($cacheFullPath)) < $this->cacheLongevity)) {

			$json = file_get_contents($cacheFullPath);
		}

		return $json;
	}

	private function getTransactions ($pubkey, $startDate, $endDate = NULL) {

		if ($startDate > $this->now) {

			return array();

		}

		if (!isset($endDate)) {

			$endDate = $this->today;
		}

		$txFullPath =
			$this->isOver() ?
				$this->cacheDir . 'tx/' . $pubkey . '_'  . $startDate->format('Y-m-d') . '_' . $endDate->format('Y-m-d') . '.json'
			:
				$this->cacheDir . 'tx/' . $pubkey . '_'  . $startDate->format('Y-m-d') . '.json'
		;

		$json = $this->getFromCache($txFullPath);

		if (empty($json)) {

			$jsonUri = '/tx/history/' . $pubkey . "/times/" . $startDate->getTimestamp() . "/" . $endDate->getTimestamp();
			$json = $this->fetchJson($jsonUri);
		}

		$transactions = json_decode($json);

		if (empty($transactions)) {

			throw new Exception(_("Nous n'avons pas pu récupérer la liste des transactions."));
			return array();

		} else {

			$this->cacheIt($json, $txFullPath);
			return $transactions->history->received;
		}
	}


	public function keepOnlyHighestDonations () {


	}

	public function getDonationsList () {

		if (empty($this->donationsList)) {

			try {

				$this->fetchDonationsList();

			} catch (Exception $e) {

				throw $e;
			}

		}

		return $this->donationsList;

	}

	public function getDonors () {

		try {

			if (empty($this->donorsList)) {

				$this->fetchDonationsList();
			}

		} catch (Exception $e) {

			throw $e;
		}

		return $this->donorsList;
	}

	public function getDonorCesiumPlusProfile ($pubkey) {

		if (!isset($this->donorsCesiumPlusProfiles)) {

			$this->fetchCesiumPlusProfiles();
		}

		if (isset($this->donorsCesiumPlusProfiles[$pubkey])) {

			return $this->donorsCesiumPlusProfiles[$pubkey];

		} else {

			return new Donor($pubkey);
		}
	}

	public function fetchCesiumPlusProfiles () {

		$this->donorsCesiumPlusProfiles = array();

		$queryParams = [
			'size' => $this->donorsNb,
			'query' => [
				'bool' => [
					'should' => []
				]
			],
			'_source' => [
				'city',
				'title',
				'issuer',
				'avatar',
				'geoPoint'
			]
		];

		foreach ($this->donorsList as $pubkey) {

			$queryParams['query']['bool']['should'][] = [

				'match' => [

					'issuer' => $pubkey
				]
			];
		}

		$json = $this->fetchJson('/user/profile/_search', true, $queryParams);
		$result = json_decode($json);
		$cesiumPlusProfiles = $result->hits->hits;

		foreach ($cesiumPlusProfiles as $profile) {

			$profile = $profile->_source;

			$donor = new Donor($profile->issuer);

			if (isset($profile->title)) {

				$donor->setName($profile->title);
			}

			if (isset($profile->city)) {

				$donor->setCity($profile->city);
			}

			if (isset($profile->avatar)) {

				$donor->setAvatar($profile->avatar->_content, $profile->avatar->_content_type);
			}

			if (isset($profile->geoPoint)) {

				$donor->setGeoPoint($profile->geoPoint->lon, $profile->geoPoint->lat);
			}

			$this->donorsCesiumPlusProfiles[$profile->issuer] = $donor;

		}
	}

	public function getFilteredDonationsList () {

		if (($this->getFilterMinDonation() <= 0) AND ($this->getFilterMinCommentLength() <= 0)) {

			return $this->getDonationsList();

		} else {

			return array_filter($this->getDonationsList(), function ($v) {

				return (
					($v->getAmount() >= $this->getFilterMinDonation())
					 AND
					(strlen($v->getComment()) >= $this->getFilterMinCommentLength())
				);
			});
		}

	}

	public function setMonthsToConsider ($m) {

		$this->monthsToConsider = (int) $m;
	}

	public function getMonthsToConsider () {

		if (isset($this->monthsToConsider)) {

			return $this->monthsToConsider;

		} else {

			return $this->defaultMonthsToConsider;
		}
	}


	private function fetchDonationsList () {

		$this->donationsList = array();
		$this->totalDonationPerDonor = array();
		$this->donorsList = array();
		$this->amountCollected = 0;
		$this->donorsNb = 0;

		try {

			$tx =
				$this->getTransactions(
					$this->pubkey,
					$this->startDate,
					$this->endDate
				);

		} catch (Exception $e) {

			throw $e;
		}

		foreach ($tx as $t) {

			// Do not take outgoing transactions into account
			if ($t->issuers[0] == $this->pubkey) {

				continue;
			}

			$donorPubkey = $t->issuers[0];

			foreach ($t->outputs as $o) {

				if (strstr($o, $this->pubkey)) {

					$o = explode(':', $o);

					$transactionAmount = $o[0] / 100;

					$this->donationsList[] = new Donation(

						$transactionAmount,
						$donorPubkey,
						intval($t->time),
						$t->comment
					);

					$this->amountCollected += $transactionAmount;

					if (!in_array($donorPubkey, $this->donorsList)) {

						++$this->donorsNb;

						$this->donorsList[] = $donorPubkey;

						$this->totalDonationPerDonor[$donorPubkey] = $transactionAmount;

					} else {

						$this->totalDonationPerDonor[$donorPubkey] += $transactionAmount;
					}
				}
			}
		}
	}

	public function convertIntoChosenUnit ($amountInQuantitative) {

		if ($this->unit == 'quantitative') {

			return $amountInQuantitative;

		} else {

			if (!isset($this->startDateUdAmount)) {

				$this->startDateUdAmount = $this->getUdAmount($this->startDate);
			}

			return round($amountInQuantitative / $this->startDateUdAmount, 2);
		}
	}



	public function addNode ($node) {

		$node = htmlspecialchars($node);

		$this->nodes = array_unique(
			array_merge(
				(array)$node,
				$this->nodes
			)
		);
	}



	public function addNodes ($nodes) {

		if (!is_array($nodes)) {

			$nodes = explode(' ', $nodes);
		}

		foreach ($nodes as $node) {

			$this->addNode($node);
		}

	}

	/**
	 * @return $nodes array
	 */
	public function getNodesList ($cesiumPlus = false) {

		$nodesFilename = $cesiumPlus ? 'nodes-cesiumplus' : 'nodes';
		$nodesFilename .=  '.txt';
		$nodesFullpath = $this->cacheDir . $nodesFilename;

		$nodes = $cesiumPlus ? $this->cesiumPlusNodes : $this->nodes;

		if ($this->isActivatedCache) {

			if (!file_exists($nodesFullpath)) {

				shuffle($nodes);

				$this->cacheNodes($nodes, $cesiumPlus);


			} else {

				$nodesStr = file_get_contents($nodesFullpath);

				$nodes = explode("\n", $nodesStr);
			}

		} else {

			shuffle($nodes);

		}

		return $nodes;
	}

	protected function cacheNodes ($nodes, $cesiumPlus = false) {

		$nodesFilename = $cesiumPlus ? 'nodes-cesiumplus' : 'nodes';
		$nodesFilename .=  '.txt';

		if (!file_exists($this->cacheDir)) {

			mkdir($this->cacheDir, 0777, true);

		}

		file_put_contents($this->cacheDir . $nodesFilename, implode("\n", $nodes));
	}

	protected function saveNodes ($nodes, $cesiumPlus = false) {

		if ($cesiumPlus) {

			$this->cesiumPlusNodes = $nodes;

		} else {

			$this->nodes = $nodes;
		}
	}

	protected function fetchJson_aux ($nodes, $uri, $cesiumPlus, $queryParams, $nodesNb, $nodeTimeout) {

		if ($cesiumPlus) {

			// $header = 'Content-Type: application/x-www-form-urlencoded';
			// $header = "Content-Type: text/xml\r\n";

			$opts = [
				'http' => [
					'method'  => 'POST',
					'content' => json_encode($queryParams),
					// 'header'  => $header,
					'timeout' => $nodeTimeout
				]
			];

		} else {

			$opts = [
				'http'=>[
					'timeout' => $nodeTimeout,
				]
			];
		}

		$streamContext = stream_context_create($opts);

		$i = 0;

		do {

			$json = @file_get_contents(
				"https://" . current($nodes) . $uri,
				false,
				$streamContext
			);

			if (empty($json)) {

				$nodes[] = array_shift($nodes);
				++$i;
			}

		} while (empty($json) and ($i < $nodesNb));

		if (!empty($json)) {

			// Let's save node order for other queries :
			$this->saveNodes($nodes, $cesiumPlus);

			if ($this->isActivatedCache) {

				$this->cacheNodes($nodes, $cesiumPlus);
			}
		}

		return $json;
	}


	public function fetchJson ($uri, $cesiumPlus = false, $queryParams = NULL) {

		$json = NULL;

		$nodes = $this->getNodesList($cesiumPlus);

		$nodesNb = count($nodes);

		$maxTries = 3;

		$nodeTimeout = $cesiumPlus ? $this->cesiumPlusNodeTimeout : $this->nodeTimeout;
		$nodeTimeoutIncrement = $cesiumPlus ? $this->cesiumPlusNodeTimeoutIncrement : $this->nodeTimeoutIncrement;

		for ($i = 0; ($i < 3) and empty($json); ++$i) {

			$json = $this->fetchJson_aux($nodes, $uri, $cesiumPlus, $queryParams, $nodesNb, $nodeTimeout);

			$nodeTimeout += $nodeTimeoutIncrement;
		}

		if (empty($json)) {

			$out = [];
			$out[] = _('Aucun noeud Duniter n\'a été trouvé.');
			$out[] = _('Noeud interrogés : ');

			if ($cesiumPlus) {

				$out[] = _('Paramètres de la requête : ');
				$out[] = print_r($queryParams, true);
			}

			$out = array_merge($out, $nodes);

			$this->decease($out);
		}

		return $json;
	}

	protected function fetchUdAmount ($date) {

		// On récupère les numéros de chaque blocks de DU journalier
		$json = $this->fetchJson('/blockchain/with/ud');
		$blocks = json_decode($json)->result->blocks;

		if ($date > $this->now) {

			// On récupère le dernier block
			$blockNum = end($blocks);

		} else {

			// On récupère le bloc de la date qui nous intéresse
			$blockNum = $blocks[count($blocks) - $this->today->diff($date)->format("%a") - 1];
		}

		// Puis on récupère le montant du DU
		$json = $this->fetchJson('/blockchain/block/' . $blockNum);
		$block = json_decode($json);


		return ($block->dividend / 100);
	}

	public function getStartDate () {

		return (clone $this->startDate);
	}

	public function getUdAmount ($date) {

		$udFilename = $this->getUdFilename($date);
		$udsCacheDir = $this->cacheDir . 'uds/';
		$udFullPath = $udsCacheDir . $udFilename;

		if ($this->isActivatedCache) {

			if (file_exists($udFullPath)) {

				$udCachedAmount = file_get_contents($udFullPath);

				if (is_numeric($udCachedAmount) and $udCachedAmount != 0) {

					$udAmount = floatval($udCachedAmount);
				}
			}



			if (!isset($udAmount)) {

				$udAmount = $this->fetchUdAmount($date);

				// Cache UD amount

				if (!file_exists($udsCacheDir)) {

					mkdir($udsCacheDir, 0777, true);

				}

				file_put_contents($udFullPath, $udAmount);

			}


		} else {

			$udAmount = $this->fetchUdAmount($date);

		}

		return $udAmount;
	}


	protected function getUdFilename ($date) {

		$datePreviousAutumnEquinox = new DateTime($date->format('Y') . '-09-22');
		$datePreviousSpringEquinox = new DateTime($date->format('Y') . '-03-20');

		if ($date > $datePreviousAutumnEquinox) {

			$udFilename = $date->format('Y') . '-autumn';

		} elseif ($date > $datePreviousSpringEquinox) {

			$udFilename = $date->format('Y') . '-spring';

		} else {

			$udFilename = ($date->sub(new DateInterval('P1Y'))->format('Y')). '-autumn';
		}

		return $udFilename . '.txt';

	}


}
