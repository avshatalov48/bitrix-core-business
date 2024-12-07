<?php

namespace Bitrix\Calendar\Internals\Counter\Provider;

use Bitrix\Calendar\Internals\Counter\CounterDictionary;

class Sync implements Base
{
	private int $userId;
	private int $entityId;

	public function __construct(int $userId, int $entityId)
	{
		$this->userId = $userId;
		$this->entityId = $entityId;
	}

	public function getValue(): int
	{
		return \CUserCounter::GetValue($this->userId, CounterDictionary::COUNTER_SYNC_ERRORS);
	}
}