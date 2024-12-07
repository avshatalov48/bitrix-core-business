<?php

namespace Bitrix\Socialnetwork\Integration\Tasks\RecentActivity;

use Bitrix\Main\Loader;
use Bitrix\Socialnetwork\Internals\EventService\EventDictionary;
use Bitrix\Socialnetwork\Space\List\RecentActivity\Dictionary;
use Bitrix\Socialnetwork\Space\List\RecentActivity\Event\Processor\AbstractProcessor;

final class TaskProcessor extends AbstractProcessor
{
	public function isAvailable(): bool
	{
		return Loader::includeModule('tasks');
	}

	protected function getTypeId(): string
	{
		return Dictionary::ENTITY_TYPE['task'];
	}

	public function process(): void
	{
		$groupId = (int)($this->event->getData()['GROUP_ID'] ?? null);
		$taskId = (int)($this->event->getData()['ID'] ?? null);

		if ($taskId <= 0 || $groupId < 0)
		{
			return;
		}

		switch ($this->event->getType())
		{
			case EventDictionary::EVENT_SPACE_TASK_DELETE:
				$this->onTaskDelete($taskId);
				break;
			case EventDictionary::EVENT_SPACE_TASK_REMOVE_USERS:
				$this->onTaskRemoveUsers($taskId);
				break;
			default:
				$this->onDefaultEvent($taskId, $groupId);
				break;
		}
	}

	private function onTaskDelete(int $taskId): void
	{
		$this->deleteRecentActivityData($taskId);
	}

	private function onTaskRemoveUsers(int $taskId): void
	{
		$this->deleteRecentActivityData($taskId);
	}

	private function onDefaultEvent(int $taskId, int $groupId): void
	{
		if ($groupId > 0)
		{
			$this->saveRecentActivityData($groupId, $taskId);
		}
		else
		{
			$this->saveRecentActivityData(0, $taskId);
		}
	}
}
