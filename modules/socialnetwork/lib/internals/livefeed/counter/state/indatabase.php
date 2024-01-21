<?php

namespace Bitrix\Socialnetwork\Internals\LiveFeed\Counter\State;

use Bitrix\Main\ORM\Query\Result;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Socialnetwork\Internals\LiveFeed\Counter;
use Bitrix\Socialnetwork\Internals\LiveFeed\Counter\CounterState;
use Bitrix\Socialnetwork\Internals\LiveFeed\Counter\CounterTable;

class InDatabase extends CounterState
{
	private Query $query;
	/** @var array the resultset for a single row */
	private array $current = [];
	/** @var int the cursor pointer */
	private int $key = 0;
	/** @var bool flag indicating there a valid resource or not */
	private bool $valid = false;

	private Result $ormQueryResult;

	public function __construct(int $userId, Counter\Loader $loader)
	{
		parent::__construct($userId, $loader);

		$this->queryInit($userId);
		$this->next();
	}

	public function rewind(): void
	{
		$this->key = 0;
		$this->queryInit($this->userId);
		$this->next();
	}

	public function current()
	{
		return $this->current;
	}

	public function key(): int
	{
		return $this->key;
	}

	public function next(): void
	{
		$this->key++;
		$row = $this->ormQueryResult->fetch();
		if ($row === false)
		{
			$this->valid = false;
			unset($this->ormQueryResult);
			return;
		}

		$this->valid = true;
		$this->current = $row;
	}

	public function valid(): bool
	{
		return $this->valid;
	}

	public function getSize(): int
	{
		return $this->query->queryCountTotal();
	}

	public function updateState(array $rawCounters, array $types = [], array $logIds = []): void
	{
		$this->updateRawCounters();
	}

	protected function loadCounters(): void
	{
		$this->updateRawCounters();
	}

	private function queryInit(int $userId)
	{
		$this->query = CounterTable::query()
			->setSelect([
				'VALUE',
				'SONET_LOG_ID',
				'GROUP_ID',
				'TYPE'
			])
			->where('USER_ID', $userId);

		$this->ormQueryResult = $this->query->exec();
	}
}