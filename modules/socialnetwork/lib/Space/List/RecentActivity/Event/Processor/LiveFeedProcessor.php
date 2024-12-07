<?php

namespace Bitrix\Socialnetwork\Space\List\RecentActivity\Event\Processor;

use Bitrix\Socialnetwork\Internals\EventService\EventDictionary;
use Bitrix\Socialnetwork\Space\List\RecentActivity\Dictionary;
use Bitrix\Socialnetwork\Space\List\RecentActivity\Event\Data\LogRightProvider;
use Bitrix\Socialnetwork\Space\List\RecentActivity\Event\Trait\AccessCodeTrait;

final class LiveFeedProcessor extends AbstractProcessor
{
	use AccessCodeTrait;

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
		return in_array($eventId, ['calendar', 'tasks', 'tasks_comment', 'calendar_comment'], true);
	}

	public function process(): void
	{
		$sonetLogId = (int)($this->event->getData()['SONET_LOG_ID'] ?? null);
		$eventId = $this->event->getData()['EVENT_ID'] ?? null;
		if ($sonetLogId <= 0 || $this->doSkipEventProcessing($eventId))
		{
			return;
		}

		switch ($this->event->getType())
		{
			case EventDictionary::EVENT_SPACE_LIVEFEED_POST_DEL:
				$this->onLiveFeedPostDelete($sonetLogId);
				break;
			case EventDictionary::EVENT_SPACE_LIVEFEED_POST_REMOVE_USERS:
				$this->onLiveFeedPostRemoveUsers($sonetLogId);
				break;
			default:
				$this->onDefaultEvent($sonetLogId);
				break;
		}
	}

	private function onDefaultEvent(int $sonetLogId): void
	{
		$spaceIds = $this->getSpaceIdsFromCodes((new LogRightProvider())->get($sonetLogId), $this->recipient);

		$spaceIds = array_unique($spaceIds);
		foreach ($spaceIds as $spaceId)
		{
			$this->saveRecentActivityData($spaceId, $sonetLogId);
		}
	}

	private function onLiveFeedPostDelete(int $sonetLogId): void
	{
		$this->deleteRecentActivityData($sonetLogId);
	}

	private function onLiveFeedPostRemoveUsers(int $sonetLogId): void
	{
		$this->deleteRecentActivityData($sonetLogId);
	}
}
