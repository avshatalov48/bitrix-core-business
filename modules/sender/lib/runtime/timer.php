<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2018 Bitrix
 */

namespace Bitrix\Sender\Runtime;

/**
 * Class Timer
 * @package Bitrix\Sender\Runtime
 */
class Timer
{
	/** @var  integer|null $timeout Timeout. */
	protected $timeout;

	/** @var  integer|null $timeAtStart Time at start. */
	protected $timeAtStart;

	/** @var  integer|null $limit Limit. */
	protected $limit;

	/** @var  integer $current Current. */
	protected $current = 0;

	/** @var  bool $isManualIncrement Limit inc is manual. */
	protected $isManualIncrement = false;

	/**
	 * Timer constructor.
	 *
	 * @param integer|null $timeout Timeout.
	 * @param integer|null $limit Limit.
	 */
	public function __construct($timeout = null, $limit = null)
	{
		$this->setLimit($limit)->setTimeout($timeout)->startTime();
	}

	/**
	 * Return true if timer is elapsed.
	 *
	 * @return bool
	 */
	public function isElapsed()
	{
		return ($this->isTimeout() || $this->isLimitExceeded());
	}

	/**
	 * Enable manual increment.
	 *
	 * @return $this
	 */
	public function enableManualIncrement()
	{
		$this->isManualIncrement = true;
		return $this;
	}

	/**
	 * Increment current value of limit.
	 *
	 * @return $this
	 */
	public function increment()
	{
		$this->current++;
		return $this;
	}

	/**
	 * Set limit.
	 *
	 * @param integer $limit Limit.
	 * @return $this
	 */
	public function setLimit($limit)
	{
		$this->limit = $limit;
		return $this;
	}

	/**
	 * Set timeout.
	 *
	 * @param integer $timeout Timeout.
	 * @return $this
	 */
	public function setTimeout($timeout)
	{
		$this->timeout = $timeout;
		return $this;
	}

	/**
	 * Start time watch.
	 *
	 * @return $this
	 */
	public function startTime()
	{
		if ($this->timeout)
		{
			$this->timeAtStart = getmicrotime();
			@set_time_limit(0);
		}

		return $this;
	}

	/**
	 * Check timeout.
	 *
	 * @return bool
	 */
	public function isTimeout()
	{
		if (!$this->timeout)
		{
			return false;
		}

		return (getmicrotime() - $this->timeAtStart >= $this->timeout);
	}

	/**
	 * Check limits.
	 *
	 * @return bool
	 */
	public function isLimitExceeded()
	{
		if (!$this->limit)
		{
			return false;
		}

		if (!$this->isManualIncrement)
		{
			$this->increment();
		}

		return ($this->current > $this->limit);
	}
}