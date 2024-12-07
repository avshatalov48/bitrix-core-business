<?php

namespace Bitrix\Calendar\Internals\Counter\Processor\Handler;

use Bitrix\Calendar\Event\Enum\PushCommandEnum;
use Bitrix\Calendar\Integration\pull\PushService;
use Bitrix\Calendar\Internals\Counter;
use Bitrix\Main\UserTable;

class OpenEventPushScorer
{
	public function __invoke(array $userIds, array $categoryIds): void
	{
		if (empty($userIds) || empty($categoryIds))
		{
			return;
		}

		foreach($this->getUsersOnline($userIds) as $user)
		{
			$userId = (int)$user['ID'];
			$params = [];

			// counter for common category
			$params['categoriesCounter'][0] = Counter::getInstance($userId)
				->get(Counter\CounterDictionary::COUNTER_OPEN_EVENTS);

			foreach ($categoryIds as $categoryId)
			{
				$categoryId = (int)$categoryId;
				$categoryCounter = Counter::getInstance($userId)
					->get(Counter\CounterDictionary::COUNTER_OPEN_EVENTS, $categoryId);

				$params['categoriesCounter'][$categoryId] = $categoryCounter;
			}

			$this->sendCategoryCounters($userId, $params);
		}
	}

	private function getUsersOnline(array $userIds): array
	{
		if (count($userIds) === 1)
		{
			return [['ID' => $userIds[0]]];
		}

		return UserTable::query()
			->setSelect(['ID'])
			->where('ID', $userIds)
			->where('IS_ONLINE', '=', 'Y')
			->exec()->fetchAll()
		;
	}

	private function sendCategoryCounters(int $userId, array $fields): void
	{
		PushService::addEvent([$userId], [
			'module_id' => PushService::MODULE_ID,
			'command' => PushCommandEnum::OPEN_EVENT_SCORER_UPDATED->name,
			'params' => [
				'fields' => $fields
			],
		]);
	}
}
