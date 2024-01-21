<?php

namespace Bitrix\Socialnetwork\Internals\EventService\Recepients;

use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\ORM\Query\Result;
use Bitrix\Main\UserAccessTable;
use Bitrix\Main\UserTable;

class SonetRightsRecepient  implements \Iterator
{
	private Query $query;
	/** @var array the resultset for a single row */
	private array $current = [];
	/** @var int the cursor pointer */
	private int $key = 0;
	/** @var bool flag indicating there a valid resource or not */
	private bool $valid = false;

	private Result $ormQueryResult;

	public function __construct(private int $sonetLogId)
	{
		$this->queryInit($this->sonetLogId);
		$this->next();
	}

	public function rewind(): void
	{
		$this->key = 0;
		$this->queryInit($this->sonetLogId);
		$this->next();
	}

	public function current(): Recepient
	{
		return new Recepient($this->current['ID']);
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

	private function queryInit(int $sonetLogId)
	{
		// G2 - all users
		// AU - authorised users
		$all = 'G2';
		$allAuthorised = 'AU';
		$sonetLogRights = \Bitrix\Socialnetwork\Item\LogRight::get($sonetLogId);

		if (in_array($all, $sonetLogRights) || in_array($allAuthorised, $sonetLogRights))
		{
			// for all users
			$this->query = UserTable::query()
				->setSelect([
					'ID',
				])
				->where('ACTIVE', '=', 'Y');

			$this->ormQueryResult = $this->query->exec();
			return;
		}

		// filter by user access
		$this->query = UserAccessTable::query()
			->setDistinct()
			->setSelect([
				'ID' => 'USER_ID',
			])
			->whereIn('ACCESS_CODE', $sonetLogRights);

		$this->ormQueryResult = $this->query->exec();
	}
}