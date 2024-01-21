<?php

namespace Bitrix\Calendar\Access\Rule\Traits;

use Bitrix\Calendar\Sharing\Link\EventLink;
use Bitrix\Calendar\Sharing\Link\Factory;

trait SharingTrait
{
	private function isEventLinkOwner(int $eventId, int $userId): bool
	{
		$result = false;
		/** @var EventLink $eventLink */
		$eventLink = Factory::getInstance()->getEventLinkByEventId($eventId);
		if ($eventLink)
		{
			$result = $eventLink->getOwnerId() === $userId;
		}

		return $result;
	}
}