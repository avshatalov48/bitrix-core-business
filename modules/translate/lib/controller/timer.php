<?php

namespace Bitrix\Translate\Controller;

use Bitrix\Translate;

/**
 * Timer class.
 */
class Timer implements Translate\Controller\ITimeLimit
{
	/** @var int seconds */
	private $timeLimit = 15;

	/** @var float seconds */
	private $startTime = -1;

	/**
	 * @param int $timeLimit Timeout in seconds.
	 */
	public function __construct($timeLimit = -1)
	{
		if ($timeLimit > 0)
		{
			$this->setTimeLimit($timeLimit);
		}
	}

	/**
	 * Start up timer.
	 *
	 * @param int $startingTime Starting time.
	 *
	 * @return self
	 */
	public function startTimer($startingTime = null)
	{
		if ((int)$startingTime > 0)
		{
			$this->startTime = (int)$startingTime;
		}
		else
		{
			$this->startTime = \time();
		}

		return $this;
	}

	/**
	 * Tells true if time limit reached.
	 *
	 * @return boolean
	 */
	public function hasTimeLimitReached()
	{
		if ($this->timeLimit > 0 && $this->startTime > 0)
		{
			if ((\time() - $this->startTime) >= $this->timeLimit)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Gets limitation time in seconds.
	 * @return int
	 */
	public function getTimeLimit()
	{
		return $this->timeLimit;
	}

	/**
	 * Sets limitation time in seconds.
	 *
	 * @param int $timeLimit Timeout in seconds.
	 *
	 * @return self
	 */
	public function setTimeLimit($timeLimit)
	{
		$this->timeLimit = $timeLimit;

		return $this;
	}
}

