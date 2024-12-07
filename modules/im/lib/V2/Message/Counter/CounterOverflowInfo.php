<?php

namespace Bitrix\Im\V2\Message\Counter;

class CounterOverflowInfo
{
	protected array $usersWithOverflow;
	protected array $usersWithoutOverflow;

	public function __construct(array $usersWithOverflow, array $usersWithoutOverflow)
	{
		$this->usersWithOverflow = $usersWithOverflow;
		$this->usersWithoutOverflow = $usersWithoutOverflow;
	}

	public function getUsersWithoutOverflow(): array
	{
		return $this->usersWithoutOverflow;
	}

	public function hasOverflow(int $userId): bool
	{
		return isset($this->usersWithOverflow[$userId]);
	}

	public function has(int $userId): bool
	{
		return isset($this->usersWithOverflow[$userId]) || isset($this->usersWithoutOverflow[$userId]);
	}

	public function changeOverflowStatus(int $userId, bool $hasOverflowNow): void
	{
		if ($hasOverflowNow)
		{
			$this->usersWithOverflow[$userId] = $userId;
			unset($this->usersWithoutOverflow[$userId]);
		}
		else
		{
			$this->usersWithoutOverflow[$userId] = $userId;
			unset($this->usersWithOverflow[$userId]);
		}
	}
}