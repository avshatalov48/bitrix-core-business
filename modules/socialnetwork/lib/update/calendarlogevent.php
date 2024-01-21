<?php

namespace Bitrix\Socialnetwork\Update;

use Bitrix\Main\Application;
use Bitrix\Main\ORM\Query\Filter\Condition;
use Bitrix\Main\Update\Stepper;
use Bitrix\Socialnetwork\Internals\Log\LogCollection;
use Bitrix\Socialnetwork\LogTable;
use Exception;

class CalendarLogEvent extends Stepper
{
	private const TYPE_CALENDAR = 'calendar';
	private const LIMIT = 1000;

	protected static $moduleId = 'socialnetwork';

	private int $lastId = 0;
	private LogCollection $items;

	public function execute(array &$option): bool
	{
		$this
			->setLastId($option['lastId'] ?? 0)
			->setItems();

		if ($this->items->isEmpty())
		{
			return self::FINISH_EXECUTION;
		}

		$this
			->moveItems()
			->updateLastId()
			->setOptions($option);

		return self::CONTINUE_EXECUTION;
	}


	private function setLastId(int $id): static
	{
		$this->lastId = $id;
		return $this;
	}

	private function setItems(): static
	{
		$this->items = new LogCollection();
		try
		{
			$query = LogTable::query();
			$query
				->setSelect(['ID'])
				->where('EVENT_ID', static::TYPE_CALENDAR)
				->whereNull('MODULE_ID')
				->where(new Condition('ID', '>', $this->lastId))
				->setOrder(['ID' => 'asc'])
				->setLimit(static::LIMIT);

			$this->items = $query->exec()->fetchCollection();
		}
		catch (Exception $exception)
		{
			$this->writeToLog($exception);
			return $this;
		}

		return $this;
	}

	private function moveItems(): static
	{
		$table = LogTable::getTableName();
		$ids = implode(',', $this->items->getIdList());
		$moduleId = static::TYPE_CALENDAR;
		$sql = "update {$table} set MODULE_ID = '{$moduleId}' where id in ({$ids})";
		try
		{
			Application::getConnection()->query($sql);
		}
		catch (Exception $exception)
		{
			$this->writeToLog($exception);
		}

		return $this;
	}

	private function updateLastId(): static
	{
		$this->lastId = max($this->items->getIdList());
		return $this;
	}

	private function setOptions(array &$options): static
	{
		$options['lastId'] = $this->lastId;
		return $this;
	}
}