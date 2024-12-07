<?php

namespace Bitrix\Socialnetwork\Integration\Tasks\RecentActivity;

use Bitrix\Main\Loader;
use Bitrix\Main\Type\Collection;
use Bitrix\Socialnetwork\Internals\EventService\EventDictionary;
use Bitrix\Socialnetwork\Internals\EventService\Push\PushEventDictionary;
use Bitrix\Socialnetwork\Space\List\RecentActivity\Dictionary;
use Bitrix\Socialnetwork\Space\List\RecentActivity\Event\PreProcessor\AbstractPreProcessor;

final class TaskPreProcessor extends AbstractPreProcessor
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
		$taskId = (int)($this->event->getData()['ID'] ?? null);

		if ($taskId <= 0)
		{
			return;
		}

		switch ($this->event->getType())
		{
			case EventDictionary::EVENT_SPACE_TASK_UPDATE:
				$this->onTaskUpdate();
				break;
			default:
				break;
		}
	}

	private function onTaskUpdate(): void
	{
		$data = $this->event->getData();
		$taskId = $data['ID'] ?? null;
		$previousGroupId = $data['PREVIOUS_GROUP_ID'] ?? null;
		$groupId = $data['GROUP_ID'] ?? null;
		$oldMemberIds = $data['OLD_MEMBERS'] ?? [];
		$newMemberIds = $data['NEW_MEMBERS'] ?? [];
		Collection::normalizeArrayValuesByInt($oldMembers);
		Collection::normalizeArrayValuesByInt($newMembers);

		if ($taskId > 0 && $previousGroupId > 0 && $previousGroupId !== $groupId)
		{
			$this->processPreviousGroupId($taskId, $previousGroupId, $oldMemberIds);
		}

		$this->processRemovedFromTaskUsers($oldMemberIds, $newMemberIds);
	}

	private function processPreviousGroupId(int $taskId, int $previousGroupId, array $oldMemberIds): void
	{
		$this->service->deleteBySpaceId($previousGroupId, $this->getTypeId(), $taskId);

		$this->pushEvent(
			$oldMemberIds,
			PushEventDictionary::EVENT_SPACE_RECENT_ACTIVITY_REMOVE_FROM_SPACE,
			['spaceIdsToReload' => [$previousGroupId]],
		);
	}

	private function processRemovedFromTaskUsers(array $oldMemberIds, array $newMemberIds): void
	{
		if ($oldMemberIds === $newMemberIds)
		{
			return;
		}

		$lostAccessUsers = array_values(array_diff($oldMemberIds, $newMemberIds));

		if (empty($lostAccessUsers))
		{
			return;
		}

		\Bitrix\Socialnetwork\Internals\EventService\Service::addEvent(
			EventDictionary::EVENT_SPACE_TASK_REMOVE_USERS,
			[
				'TASK_ID' => $data['ID'] ?? null,
				'RECEPIENTS' => $lostAccessUsers,
			]
		);
	}
}
