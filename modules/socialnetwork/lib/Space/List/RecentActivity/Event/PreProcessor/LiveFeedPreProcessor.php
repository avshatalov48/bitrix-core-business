<?php

namespace Bitrix\Socialnetwork\Space\List\RecentActivity\Event\PreProcessor;

use Bitrix\Socialnetwork\Internals\EventService\EventDictionary;
use Bitrix\Socialnetwork\Internals\EventService\Push\PushEventDictionary;
use Bitrix\Socialnetwork\Item\LogRight;
use Bitrix\Socialnetwork\Space\List\RecentActivity\Dictionary;
use Bitrix\Socialnetwork\Space\List\RecentActivity\Event\Trait\LogRightTrait;

final class LiveFeedPreProcessor extends AbstractPreProcessor
{
	use LogRightTrait;

	public function isAvailable(): bool
	{
		return true;
	}

	protected function getTypeId(): string
	{
		return Dictionary::ENTITY_TYPE['livefeed'];
	}

	private function doSkipEventProcessing(?string $eventId): bool
	{
		return in_array($eventId, ['calendar', 'tasks'], true);
	}

	public function process(): void
	{
		$eventId = $this->event->getData()['EVENT_ID'] ?? null;
		if ($this->doSkipEventProcessing($eventId))
		{
			return;
		}

		switch ($this->event->getType())
		{
			case EventDictionary::EVENT_SPACE_LIVEFEED_POST_UPD:
				$this->onLiveFeedPostUpdate();
				break;
			default:
				break;
		}
	}

	private function onLiveFeedPostUpdate(): void
	{
		$sonetLogId = (int)($this->event->getData()['SONET_LOG_ID'] ?? null);
		$eventId = $this->event->getData()['EVENT_ID'] ?? null;
		$logRightsBeforeUpdate = $this->event->getData()['LOG_RIGHTS_BEFORE_UPDATE'] ?? null;

		$isItPossibleToHaveRemovedUsers = is_array($logRightsBeforeUpdate) && !empty($logRightsBeforeUpdate);
		if ($sonetLogId <= 0 || !$isItPossibleToHaveRemovedUsers || !is_string($eventId))
		{
			return;
		}

		$logRightsAfterUpdate = LogRight::get($sonetLogId);

		sort($logRightsAfterUpdate);
		sort($logRightsBeforeUpdate);

		if ($logRightsBeforeUpdate === $logRightsAfterUpdate)
		{
			return;
		}

		$usersBeforeUpdate = LogRight::getUserIdsByLogRights($logRightsBeforeUpdate);

		$this->processRemovedFromRecipientsSpaces(
			$sonetLogId,
			$logRightsBeforeUpdate,
			$logRightsAfterUpdate,
			$usersBeforeUpdate
		);

		$this->processRemovedFromRecipientsUsers($sonetLogId, $eventId, $logRightsAfterUpdate, $usersBeforeUpdate);
	}

	private function processRemovedFromRecipientsSpaces(
		int $sonetLogId,
		array $logRightsBeforeUpdate,
		array $logRightsAfterUpdate,
		array $usersBeforeUpdate,
	): void
	{
		$removedLogRights = array_diff($logRightsBeforeUpdate, $logRightsAfterUpdate);

		$removedGroupIds = [];
		foreach ($removedLogRights as $logRight)
		{
			$groupId = $this->getGroupIdFromLogRight($logRight);

			if ($groupId > 0)
			{
				$removedGroupIds[] = $groupId;
			}
		}

		$removedGroupIds = array_unique($removedGroupIds);

		if (empty($removedGroupIds))
		{
			return;
		}

		foreach ($removedGroupIds as $removedGroupId)
		{
			$this->service->deleteBySpaceId($removedGroupId, $this->getTypeId(), $sonetLogId);
		}

		$this->pushEvent(
			$usersBeforeUpdate,
			PushEventDictionary::EVENT_SPACE_RECENT_ACTIVITY_REMOVE_FROM_SPACE,
			['spaceIdsToReload' => $removedGroupIds],
		);
	}

	private function processRemovedFromRecipientsUsers(
		int $sonetLogId,
		string $eventId,
		array $logRightsAfterUpdate,
		array $usersBeforeUpdate
	)
	{
		$usersAfterUpdate = LogRight::getUserIdsByLogRights($logRightsAfterUpdate);

		sort($usersBeforeUpdate);
		sort($usersAfterUpdate);

		$lostAccessUsers = array_values(array_diff($usersBeforeUpdate, $usersAfterUpdate));

		if (empty($lostAccessUsers))
		{
			return;
		}

		\Bitrix\Socialnetwork\Internals\EventService\Service::addEvent(
			EventDictionary::EVENT_SPACE_LIVEFEED_POST_REMOVE_USERS,
			[
				'SONET_LOG_ID' => $sonetLogId,
				'EVENT_ID' => $eventId,
				'RECEPIENTS' => $lostAccessUsers,
			]
		);
	}
}
