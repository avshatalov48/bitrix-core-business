<?php
declare(strict_types=1);

namespace Bitrix\Landing\Copilot\Generation;

/**
 * Manage execution time
 */
class Timer
{
	private const MAX_EXECUTION_TIME = 30;

	private int $startTime;

	/**
	 * Set current time as start
	 * @return void
	 */
	public function start(): void
	{
		$this->startTime = (int)microtime(true);
	}

	/**
	 * Check execution time
	 * @return bool true means timer is ok, false - if time is over
	 */
	public function check(): bool
	{
		if (!isset($this->startTime))
		{
			return true;
		}

		return ((int)microtime(true) - $this->startTime) < self::MAX_EXECUTION_TIME;
	}
}