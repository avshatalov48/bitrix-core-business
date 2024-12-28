<?php

namespace Bitrix\Calendar\Internals\Counter\Processor;

use Bitrix\Calendar\Integration\Pull\PushCommand;
use Bitrix\Calendar\Integration\Pull\PushService;
use Bitrix\Calendar\Internals\Counter;
use Bitrix\Calendar\Internals\Counter\CounterDictionary;
use Bitrix\Calendar\Internals\Counter\Event\Event;
use Bitrix\Calendar\Internals\Counter\Event\EventCollection;

class Total implements Base
{
	public function process(): void
	{
		$events = (EventCollection::getInstance())->list();
		foreach ($events as $event)
		{
			/* @var $event Event */
			$affectedUsers = $event->getData()['user_ids'] ?? [];
			$this->recountTotal($affectedUsers);
		}
	}

	private function recountTotal(array $userIds): void
	{
		if (empty($userIds))
		{
			return;
		}

		foreach ($userIds as $userId)
		{
			// total counter (left menu, top menu, etc.)
			$value = Counter::getInstance($userId)->get(CounterDictionary::COUNTER_TOTAL);
			if (!$this->isSameValueCached($value, $userId, CounterDictionary::COUNTER_TOTAL))
			{
				\CUserCounter::Set(
					$userId,
					CounterDictionary::COUNTER_TOTAL,
					$value,
					'**',
				);

				$this->sendUserCountersPush($userId);
			}

			// my calendar counter (top menu etc.)
			$value = Counter::getInstance($userId)->get(CounterDictionary::COUNTER_MY);
			if (!$this->isSameValueCached($value, $userId, CounterDictionary::COUNTER_MY))
			{
				\CUserCounter::Set(
					$userId,
					CounterDictionary::COUNTER_MY,
					$value,
					'**',
				);
			}

			// open events total (top menu etc.)
			$value = Counter::getInstance($userId)->get(CounterDictionary::COUNTER_OPEN_EVENTS);
			if (!$this->isSameValueCached($value, $userId, CounterDictionary::COUNTER_OPEN_EVENTS))
			{
				\CUserCounter::Set(
					$userId,
					CounterDictionary::COUNTER_OPEN_EVENTS,
					$value,
					'**',
				);
			}
		}
	}

	private function isSameValueCached(int $value, int $userId, string $code): bool
	{
		global $CACHE_MANAGER;

		$cache = $CACHE_MANAGER->Get('user_counter' . $userId);
		if (!$cache)
		{
			return false;
		}

		foreach ($cache as $item)
		{
			if (
				$item['CODE'] === $code
				&& $item['SITE_ID'] === '**'
				&& (int)$item['CNT'] === $value
			)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * @param int $userId
	 *
	 * @return void
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\DB\SqlQueryException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private function sendUserCountersPush(int $userId): void
	{
		PushService::addEvent($userId, [
			'module_id' => PushService::MODULE_ID,
			'command' => PushCommand::UpdateUserCounters->value,
			'params' => [
				'counters' => [
					CounterDictionary::COUNTER_TOTAL => Counter::getInstance($userId)->get(CounterDictionary::COUNTER_TOTAL),
					CounterDictionary::COUNTER_INVITES => Counter::getInstance($userId)->get(CounterDictionary::COUNTER_INVITES),
					CounterDictionary::COUNTER_SYNC_ERRORS => Counter::getInstance($userId)->get(CounterDictionary::COUNTER_SYNC_ERRORS),
				],
			],
		]);
	}
}
