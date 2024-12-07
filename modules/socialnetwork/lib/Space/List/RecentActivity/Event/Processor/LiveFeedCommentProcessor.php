<?php

namespace Bitrix\Socialnetwork\Space\List\RecentActivity\Event\Processor;

use Bitrix\Socialnetwork\Internals\EventService\EventDictionary;
use Bitrix\Socialnetwork\Space\List\RecentActivity\Dictionary;
use Bitrix\Socialnetwork\Space\List\RecentActivity\Event\Data\LogRightProvider;
use Bitrix\Socialnetwork\Space\List\RecentActivity\Event\Trait\AccessCodeTrait;

final class LiveFeedCommentProcessor extends AbstractProcessor
{
	use AccessCodeTrait;

	public function isAvailable(): bool
	{
		return true;
	}

	protected function getTypeId(): string
	{
		return Dictionary::ENTITY_TYPE['livefeed_comment'];
	}

	private function doSkipEventProcessing(?string $eventId): bool
	{
		return in_array($eventId, ['tasks_comment', 'calendar_comment'], true);
	}

	public function process(): void
	{
		$sonetLogId = (int)($this->event->getData()['SONET_LOG_ID'] ?? null);
		$eventId = $this->event->getData()['EVENT_ID'] ?? null;
		$commentId = $this->event->getData()['SONET_LOG_COMMENT_ID'] ?? null;

		if ($sonetLogId <= 0 || $commentId <= 0 ||$this->doSkipEventProcessing($eventId))
		{
			return;
		}

		switch ($this->event->getType())
		{
			case EventDictionary::EVENT_SPACE_LIVEFEED_COMMENT_ADD:
				$this->onLiveFeedPostCommentAdd($sonetLogId, $commentId);
				break;
			case EventDictionary::EVENT_SPACE_LIVEFEED_COMMENT_DEL:
				$this->onLiveFeedPostCommentDelete($commentId);
				break;
			default:
				break;
		}
	}

	private function onLiveFeedPostCommentAdd(int $sonetLogId, int $commentId): void
	{
		$spaceIds = $this->getSpaceIdsFromCodes((new LogRightProvider())->get($sonetLogId), $this->recipient);

		$spaceIds = array_unique($spaceIds);
		foreach ($spaceIds as $spaceId)
		{
			$this->saveRecentActivityData($spaceId, $commentId, $sonetLogId);
		}
	}

	private function onLiveFeedPostCommentDelete(int $commentId): void
	{
		$this->deleteRecentActivityData($commentId);
	}
}
