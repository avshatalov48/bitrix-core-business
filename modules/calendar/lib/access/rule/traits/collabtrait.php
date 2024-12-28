<?php

namespace Bitrix\Calendar\Access\Rule\Traits;

use Bitrix\Calendar\Access\AccessibleEvent;
use Bitrix\Calendar\Core\Event\Tools\Dictionary;
use Bitrix\Calendar\Util;

trait CollabTrait
{
	private function isCollaberHasEditAccess(AccessibleEvent $item, int $userId): bool
	{
		return
			!in_array(
				$item->getEventType(),
				[Dictionary::EVENT_TYPE['collab'], Dictionary::EVENT_TYPE['shared_collab']],
				true
			)
			|| !Util::isCollabUser($userId)
			|| $item->getOwnerId() === $userId
			|| $item->getCreatorId() === $userId
			|| $item->hasAttendee($userId)
		;
	}
}
