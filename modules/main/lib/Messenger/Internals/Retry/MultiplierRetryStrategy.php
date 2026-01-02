<?php

declare(strict_types=1);

namespace Bitrix\Main\Messenger\Internals\Retry;

use Exception;

class MultiplierRetryStrategy implements RetryStrategyInterface
{
	/**
	 * @param int $maxRetries The maximum number of times to retry
	 * @param int $delayMilliseconds Milliseconds delay
	 * @param float $multiplier Causes the delay to be higher before each retry: 1 second, 2 seconds, 4 seconds
	 * @param int $maxDelayMilliseconds Maximum delay to allow (0 means no maximum)
	 * @param float $jitter The value between 0 and 1.0 is the percentage of 'delay' that will be added/subtracted
	 */
	public function __construct(
		private readonly int $maxRetries = 3,
		private readonly int $delayMilliseconds = 100,
		private readonly float $multiplier = 2,
		private readonly int $maxDelayMilliseconds = 0,
		private readonly float $jitter = 0.1
	)
	{
	}

	public function getMaxRetryCount(): int
	{
		return $this->maxRetries;
	}

	/**
	 * @param int $retry Number of current retry
	 *
	 * @return int Delay in milliseconds
	 *
	 * @throws Exception
	 */
	public function getWaitingTime(int $retry = 1): int
	{
		$delay = $this->delayMilliseconds * $this->multiplier ** $retry;

		if ($this->jitter > 0 && $this->jitter < 1)
		{
			$randomness = (int)min(\PHP_INT_MAX, $delay * $this->jitter);

			$delay += random_int(-$randomness, +$randomness);
		}

		if ($delay > $this->maxDelayMilliseconds && $this->maxDelayMilliseconds !== 0)
		{
			return $this->maxDelayMilliseconds;
		}

		return (int)min(\PHP_INT_MAX, ceil($delay));
	}
}
