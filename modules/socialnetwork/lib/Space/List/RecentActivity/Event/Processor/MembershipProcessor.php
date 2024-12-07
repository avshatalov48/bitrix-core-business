<?php

namespace Bitrix\Socialnetwork\Space\List\RecentActivity\Event\Processor;

use Bitrix\Socialnetwork\Internals\EventService\EventDictionary;
use Bitrix\Socialnetwork\Space\List\RecentActivity\Dictionary;
use Bitrix\Socialnetwork\UserToGroupTable;

final class MembershipProcessor extends AbstractProcessor
{

	public function isAvailable(): bool
	{
		return true;
	}

	protected function getTypeId(): string
	{
		return Dictionary::ENTITY_TYPE['membership'];
	}

	public function process(): void
	{
		$eventType = $this->event->getType();
		switch ($eventType)
		{
			case EventDictionary::EVENT_WORKGROUP_USER_ADD:
				$this->onUserAdd();
				break;
			case EventDictionary::EVENT_WORKGROUP_USER_UPDATE:
				$this->onUserUpdate();
				break;
			case EventDictionary::EVENT_WORKGROUP_USER_DELETE:
				$this->onUserDelete();
				break;
			default:
				break;
		}
	}

	private function onUserAdd(): void
	{
		$eventData = $this->event->getData();
		$groupId = $eventData['GROUP_ID'] ?? null;
		$userId = $eventData['USER_ID'] ?? null;
		$role = $eventData['ROLE'] ?? null;
		$initiatedByType = $eventData['INITIATED_BY_TYPE'] ?? null;

		if ($role === UserToGroupTable::ROLE_REQUEST && $initiatedByType === UserToGroupTable::INITIATED_BY_USER)
		{
			$this->saveRecentActivityData($groupId, $userId);
		}
	}

	private function onUserUpdate(): void
	{
		$eventData = $this->event->getData();
		$groupId = $eventData['GROUP_ID'] ?? null;
		$userId = $eventData['USER_ID'] ?? null;
		$oldRole = $eventData['OLD_ROLE'] ?? null;
		$oldInitiatedByType = $eventData['OLD_INITIATED_BY_TYPE'] ?? null;
		$newRole = $eventData['NEW_ROLE'] ?? null;

		$wasRequest = $oldRole === UserToGroupTable::ROLE_REQUEST;
		$wasUserRequest = $oldInitiatedByType === UserToGroupTable::INITIATED_BY_USER;
		$isMember = in_array($newRole, UserToGroupTable::getRolesMember(), true);
		if ($wasRequest && $wasUserRequest && $isMember)
		{
			$this->setStubActivity($groupId, $userId);
		}
	}

	private function onUserDelete(): void
	{
		$eventData = $this->event->getData();
		$groupId = $eventData['GROUP_ID'] ?? null;
		$userId = $eventData['USER_ID'] ?? null;
		$initiatedByType = $eventData['INITIATED_BY_TYPE'] ?? null;
		$role = $eventData['ROLE'] ?? null;

		$wasRequest = $role === UserToGroupTable::ROLE_REQUEST;
		$wasUserRequest = $initiatedByType === UserToGroupTable::INITIATED_BY_USER;
		if ($wasRequest && $wasUserRequest)
		{
			$this->setStubActivity($groupId, $userId);
		}
	}

	private function setStubActivity(int $spaceId, int $entityId): void
	{
		$recentActivityData = $this->activityService->get($this->recipient->getId(), $spaceId);

		if (
			$recentActivityData->getTypeId() !== $this->getTypeId()
			|| $recentActivityData->getEntityId() !== $entityId
		)
		{
			return;
		}

		$recentActivityData->setTypeId('stub');
		$recentActivityData->setEntityId(0);

		$this->activityService->save($recentActivityData);
		$this->sendUpdatePush($recentActivityData);
	}
}