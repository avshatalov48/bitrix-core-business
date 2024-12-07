<?php

namespace Bitrix\Calendar\Internals\Counter\State;

use Bitrix\Calendar\Internals\Counter\CounterTable;

class Loader
{
	private const STATE_LIMIT = 4999;

	private int $userId;

	public function __construct(int $userId)
	{
		$this->userId = $userId;
	}

	public function getRawCounters(): array
	{
		return CounterTable::query()
			->setDistinct()
			->setSelect([
				'VALUE',
				'EVENT_ID',
				'PARENT_ID',
				'TYPE'
			])
			->where('USER_ID', $this->userId)
			->setLimit($this->getLimit())
			->exec()
			->fetchAll()
		;
	}

	private function getLimit(): int
	{
		return self::STATE_LIMIT;
	}
}
