<?php

namespace Bitrix\Calendar\Update;

use Bitrix\Calendar\Internals\Counter\CounterService;
use Bitrix\Calendar\Internals\Counter\Event\EventDictionary;
use Bitrix\Calendar\Internals\Log\Logger;
use Bitrix\Main\Update\Stepper;
use Bitrix\Main\UserTable;

/**
 * ReCalculateCounters is the stepper that is supposed to be scheduled manually
 * It will re-calculate old calendar counters and will add missing ones for every active user
 */
final class ReCalculateCounters extends Stepper
{
	private const LIMIT = 50;
	protected static $moduleId = 'calendar';

	private int $lastId;
	private array $users;

	public function execute(array &$option): bool
	{
		$lastId = (int)($option['lastId'] ?? 0);

		$this
			->setLastId($lastId)
			->fillUsers()
		;

		if (!count($this->users))
		{
			return self::FINISH_EXECUTION;
		}

		$this
			->reCalculateCounters()
			->updateLastId()
			->setOptions($option);

		return self::CONTINUE_EXECUTION;
	}

	private function fillUsers(): self
	{
		$this->users = [];

		try
		{
			$this->users = $this->getAllUsers();
		}
		catch (\Exception $exception)
		{
			(new Logger())->log($exception);
		}

		return $this;
	}

	private function reCalculateCounters(): self
	{
		$userIds = [];
		foreach ($this->users as $user)
		{
			$userId = (int)($user['ID'] ?? 0);
			$userIds[] = $userId;
		}

		CounterService::addEvent(EventDictionary::EVENT_ATTENDEES_UPDATED, [
			'user_ids' => $userIds,
		]);

		return $this;
	}

	private function setLastId(int $id = 0): self
	{
		$this->lastId = $id;
		return $this;
	}

	private function updateLastId(): self
	{
		$this->lastId = max(array_map(fn (array $user): int => (int)$user['ID'], $this->users));
		return $this;
	}

	private function setOptions(array &$options): self
	{
		$options['lastId'] = $this->lastId;
		return $this;
	}

	private function getAllUsers(): array
	{
		$query = UserTable::query()
			->setSelect(['ID'])
			->where('ID', '>', $this->lastId)
			->where('ACTIVE', 'Y')
			->where('IS_REAL_USER', 'Y')
			->where('UF_DEPARTMENT', '!=', false)
			->setLimit($this->getLimit());

		return $query->exec()->fetchAll();
	}

	private function getLimit(): int
	{
		$limit = \COption::GetOptionString('calendar', 'calendarReCounterStepperLimit', '');

		return $limit === ''
			? self::LIMIT
			: (int)$limit
		;
	}
}
