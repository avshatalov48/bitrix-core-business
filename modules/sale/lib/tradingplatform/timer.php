<?php

namespace Bitrix\Sale\TradingPlatform;

/**
 * Class Timer
 * @package Bitrix\Sale\TradingPlatform
 */
class Timer
{
	protected $finishTime;
	protected $timeLimit;

	/**
	 * Constructor.
	 * @param int $newTimeLimit Timelimit seconds.
	 */
	public function __construct($newTimeLimit = 0, $increaseTimeLimit = true)
	{
		$startTime = (int)time();
		$currentTimeLimit = ini_get('max_execution_time');

		if($newTimeLimit > $currentTimeLimit || $newTimeLimit == 0)
			$timeLimit = $newTimeLimit;
		else
			$timeLimit = $currentTimeLimit;

		$this->timeLimit = $timeLimit;
		if($increaseTimeLimit) {
			$this->finishTime =  $startTime + (int)($timeLimit);
			@set_time_limit($timeLimit);
		}
		else {
			$this->finishTime =  $startTime + (int)($newTimeLimit);
		}

	}

	/**
	 * Checks if time is over.
	 * @param int $reserveTime Insurance time.
	 * @return bool
	 */
	public function check($reserveTime = 0)
	{
		if($this->timeLimit == 0)
			return true;

		if(time() < $this->finishTime - $reserveTime)
			return true;

		return false;
	}
} 