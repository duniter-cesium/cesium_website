<?php

require_once('Graph.class.php');

class Chart {
	
	private $crowdfunding;

	private $points = NULL;
	
	private $displayTarget = true;
	
	private $graphs = [];
	
						
	public function __construct ($crowdfunding) {
		
		$this->crowdfunding = $crowdfunding;
	}
	
	
	private function addLastPointOfCumulativeGraph ($lastAmount) {
	
		$lastDay =
			$this->crowdfunding->isOver() ?
				$this->crowdfunding->getEndDate()
			:(
				$this->crowdfunding->hasStartedYet() ?
					$this->crowdfunding->today
				:
					NULL
			)
		;
		
		if (isset($lastDay)) {
			
			$followingDay = (clone $lastDay)->add(new DateInterval('P1D'));
			
			$this->points['amountCollectedByDayCumulative'][] = [

				't' => $lastDay->getTimestamp() * 1000,
				'y' => $this->crowdfunding->convertIntoChosenUnit($lastAmount)
		  	 ];
			
			$this->points['amountCollectedByDayCumulative'][] = [

				't' => $followingDay->getTimestamp() * 1000,
				'y' => $this->crowdfunding->convertIntoChosenUnit($lastAmount)
		  	 ];
	  	 }
	}
	
	
	private function addSecondPointOfTarget ($target) {
		
		$date =
			$this->crowdfunding->isOver() ?
				$this->crowdfunding->getEndDate()
			:(
				!$this->crowdfunding->isEvergreen() ?
					$this->crowdfunding->getEndDate()
				:(
					$this->crowdfunding->isEvergreen() == 'monthly' ?
						// last point will be the last day of the month the campaign starts
						new DateTime($this->crowdfunding->getStartDate()->format("Y-m-t"))
					:(
						$this->crowdfunding->hasStartedYet() ?
							$this->crowdfunding->now
						:
							// date of the last day of the month
							 new DateTime($this->getStartDate()->format("Y-m-t"))
					)
				)
			)
		;
		
		$date->add(new DateInterval('P1D'));
		
		$this->points['targetLine'][] = [
			't' => $date->getTimestamp() * 1000,
			'y' => $target
		];
	}
	
	public function displayTarget ($bool = NULL) {
		
		if (isset($bool)) {
			
			$this->displayTarget = $bool;
		
		} else {
		
			return $this->displayTarget;
		}
	}
	
	public function addGraph ($g) {
		
		$this->graphs[] = $g;
	}
	
	private function setPoints () {
		
		$dailyAmount = 0;
		$dailyAmountCumulative = 0;
		
		
		$t_0 = (clone $this->crowdfunding->getStartDate());
		$mt_0 = $t_0->getTimestamp() * 1000;
		
		if ($this->crowdfunding->hasTarget()) {
			
			// On trace la droite de l'objectif
			$this->points['targetLine'][] = [
			
					't' => $mt_0,
					'y' => $this->crowdfunding->getTarget()
			];
			
			// For x axis scaling
			$this->addSecondPointOfTarget($this->crowdfunding->getTarget());
			
		}

		/*
		$this->points['amountCollectedByDayCumulative'][] = [

			't' => $mt_0,
			'y' => 0
	  	 ];
		*/

		try {

			$tx = $this->crowdfunding->getDonationsList();

		} catch (Exception $e) {

			$tx = NULL;
		}
		
		if (empty($tx)) {
		
			// For y axis scaling
			$this->points['amountCollectedByDay'][] = [

				't' => $mt_0,
				'y' => 0
		  	 ];

	  	 } else {
			
			$currentDay = new DateTime();
			$dayBefore = clone $this->crowdfunding->getStartDate();
		  	 
			foreach ($tx as $t) {

				
				$dailyAmountCumulative += $t->getAmount();
				$dailyAmount += $t->getAmount();
				
				$currentDay->setTimestamp($t->getDate()->getTimestamp());
				$currentDay->setTime(0, 0, 0);
				
				if ($currentDay != $dayBefore) {
					
					$this->points['amountCollectedByDay'][] = [

						't' => $dayBefore->getTimestamp() * 1000,
						'y' => $this->crowdfunding->convertIntoChosenUnit($dailyAmount)
					];

					$this->points['amountCollectedByDayCumulative'][] = [

						't' => $dayBefore->getTimestamp() * 1000,
						'y' => $this->crowdfunding->convertIntoChosenUnit($dailyAmountCumulative)
				  	 ];
					
					$lastDailyAmount = $dailyAmount;
					$dailyAmount = 0;
			  	 }
				
				$dayBefore = clone $currentDay;
			}
			
			// Add latest day's tx
			
			$this->points['amountCollectedByDay'][] = [

				't' => $dayBefore->getTimestamp() * 1000,
				'y' => $this->crowdfunding->convertIntoChosenUnit($lastDailyAmount)
			];
			
			$this->addLastPointOfCumulativeGraph($dailyAmountCumulative);
		}
	}


	public function getAmountCollectedByDayPoints () {

		if (empty($this->points)) {

			$this->setPoints();
		}
		
		return json_encode($this->points['amountCollectedByDay']);

	}


	public function getAmountCollectedByDayCumulativePoints () {

		if (empty($this->points)) {

			$this->setPoints();
		}
		
		$points =
			isset($this->points['amountCollectedByDayCumulative']) ?
				$this->points['amountCollectedByDayCumulative']
			:
				[]
			;
		
		return json_encode($points);
	}


	public function getTargetLinePoints () {

		if (empty($this->points)) {

			$this->setPoints();
		}
		
		return json_encode($this->points['targetLine']);
	}
	
	public function setTargetLineColor ($colorStr) {
		
		$this->targetLineColor = new Color($colorStr);
	}
	
	
	public function getScripts ($lang, $whereToInsertChart = 'main', $dir = '') {
		
		if (empty($this->points)) {
		
			$this->setPoints();
		}
		
		$out = '<script src="'. $dir .'lib/js/moment.min.js"></script>';
		
		$out .= '<script src="'. $dir .'locales/moment.js/'. $lang .'.js"></script>';
		
		$out .= '<script src="'. $dir .'lib/js/chart.min.js"></script>';

		$out .= '<script>
		window.onload = function() {

			moment.locale(\''. $lang .'\');

			var currentLocaleData = moment.localeData();
			var dateFormat = currentLocaleData.longDateFormat(\'L\');
			var hourFormat = currentLocaleData.longDateFormat(\'LT\');
			
			var container = document.querySelector(\''. $whereToInsertChart .'\');
			
			var div = document.createElement(\'div\');
			div.classList.add(\'chart-container\');
			var canvas = document.createElement(\'canvas\');
			div.appendChild(canvas);
			container.appendChild(div);

			var chartData = {
				datasets: [';
				
				foreach ($this->graphs as $g) {
					
					$out .= $g->getGraph() . ', ';
					
				}
				
				$out .= '
				]
			};

			new Chart(canvas.getContext(\'2d\'), {
				type: \'bar\',
				data: chartData,
				options: {
					responsive: true,
					animation: {
						duration: 1800,
						easing: \'easeInCubic\'
					},
					title: {
						display: true
					},
					scales: {
						xAxes: [{
							type: \'time\',
							time: {
								minUnit: \'day\',
								tooltipFormat: dateFormat
							}
						}]
					},
					tooltips: {
						intersect: false
					}
				}
			});
		}
		</script>';
		
		return $out;
	}
}


