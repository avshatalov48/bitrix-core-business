<?php

namespace Bitrix\Calendar\Access\Rule;

use Bitrix\Calendar\Access\Model\EventModel;
use Bitrix\Calendar\Access\Model\SectionModel;
use Bitrix\Calendar\Access\Rule\Traits\CollabTrait;
use Bitrix\Calendar\Access\Rule\Traits\SharingTrait;
use Bitrix\Calendar\Core\Event\Tools\Dictionary;
use Bitrix\Calendar\Sharing\SharingEventManager;
use Bitrix\Main\Access\AccessibleItem;
use Bitrix\Calendar\Access\ActionDictionary;
use Bitrix\Calendar\Access\Rule\Traits\CurrentUserTrait;

class EventEditRule extends \Bitrix\Main\Access\Rule\AbstractRule
{
	use CurrentUserTrait;
	use SharingTrait;
	use CollabTrait;

	public function execute(AccessibleItem $item = null, $params = null): bool
	{
		if (!$item instanceof EventModel)
		{
			return false;
		}

		if (!$this->hasCurrentUser())
		{
			return true;
		}

		if ($this->user->isAdmin() || $this->user->isSocNetAdmin($item->getSectionType()))
		{
			return true;
		}

		// for open events skip section check
		// only creator can edit open events
		if ($item->getSectionType() === Dictionary::CALENDAR_TYPE['open_event'])
		{
			return $this->user->getUserId() === $item->getCreatedBy();
		}

		if (!$this->isCollaberHasEditAccess($item, $this->user->getUserId()))
		{
			return false;
		}

		$doCheckCurrentEvent = isset($params['checkCurrentEvent']) && $params['checkCurrentEvent'] === 'Y';
		$isSharingEventLinkOwner =
			in_array($item->getEventType(), SharingEventManager::getSharingEventTypes())
			&& $this->isEventLinkOwner($item->getParentEventId(), $this->user->getUserId())
		;

		if ($doCheckCurrentEvent || $isSharingEventLinkOwner)
		{
			$section = SectionModel::createFromEventModel($item);
		}
		else
		{
			$section = SectionModel::createFromEventModelParentFields($item);
		}

		return $this->controller->check(
			ActionDictionary::ACTION_SECTION_EDIT,
			$section,
		);
	}
}
