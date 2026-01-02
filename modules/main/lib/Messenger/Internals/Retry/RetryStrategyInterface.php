<?php
declare(strict_types=1);

namespace Bitrix\Main\Messenger\Internals\Retry;

interface RetryStrategyInterface
{
	public function getMaxRetryCount(): int;

	/**
	 * @param int $retry Number of current retry
	 *
	 * @return int Delay in milliseconds
	 */
	public function getWaitingTime(int $retry = 1): int;
}