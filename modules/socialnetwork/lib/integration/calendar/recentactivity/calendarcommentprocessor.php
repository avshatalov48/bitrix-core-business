<?php

namespace Bitrix\Socialnetwork\Integration\Calendar\RecentActivity;

use Bitrix\Main\Loader;
use Bitrix\Socialnetwork\Internals\EventService\EventDictionary;
use Bitrix\Socialnetwork\Space\List\RecentActivity\Dictionary;
use Bitrix\Socialnetwork\Space\List\RecentActivity\Event\Processor\AbstractProcessor;
use Bitrix\Socialnetwork\Space\List\RecentActivity\Event\Trait\AccessCodeTrait;

final class CalendarCommentProcessor extends AbstractProcessor
{
	use AccessCodeTrait;

	public function isAvailable(): bool
	{
		return Loader::includeModule('calendar');
	}

	protected function getTypeId(): string
	{
		return Dictionary::ENTITY_TYPE['calendar_comment'];
	}

	public function process(): void
	{
		$eventId = (int)($this->event->getData()['ID'] ?? null);
		$commentId = (int)($this->event->getData()['COMMENT_ID'] ?? null);

		if ($eventId <= 0 || $commentId <= 0)
		{
			return;
		}

		switch ($this->event->getType())
		{
			case EventDictionary::EVENT_SPACE_CALENDAR_EVENT_COMMENT_ADD:
				$this->onCommentAdd($eventId, $commentId);
				break;
			case EventDictionary::EVENT_SPACE_CALENDAR_EVENT_COMMENT_DEL:
				$this->onCommentDelete($commentId);
				break;
			default:
				break;
		}
	}

	private function onCommentAdd(int $eventId, int $commentId): void
	{
		$attendeeCodes = $this->event->getData()['ATTENDEES_CODES'] ?? null;

		if (is_string($attendeeCodes))
		{
			$attendeeCodes = explode(',', $attendeeCodes);
		}
		elseif (!is_array($attendeeCodes))
		{
			$attendeeCodes = [];
		}

		$spaceIds = $this->getSpaceIdsFromCodes($attendeeCodes, $this->recipient);

		$spaceIds = array_unique($spaceIds);
		foreach ($spaceIds as $spaceId)
		{
			$this->saveRecentActivityData($spaceId, $commentId ,$eventId);
		}
	}

	private function onCommentDelete(int $commentId): void
	{
		$this->deleteRecentActivityData($commentId);
	}
}
