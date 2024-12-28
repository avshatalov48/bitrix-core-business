<?php

namespace Bitrix\Calendar\Access\Rule;

use Bitrix\Calendar\Access\Model\EventModel;
use Bitrix\Calendar\Access\Model\SectionModel;
use Bitrix\Calendar\Core\Event\Tools\Dictionary;
use Bitrix\Calendar\Util;
use Bitrix\Main\Access\AccessibleItem;
use Bitrix\Calendar\Access\ActionDictionary;
use Bitrix\Calendar\Access\Rule\Traits\CurrentUserTrait;

class EventEditLocationRule extends \Bitrix\Main\Access\Rule\AbstractRule
{
	use CurrentUserTrait;

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

		if ($item->getSectionType() === Dictionary::CALENDAR_TYPE['open_event'])
		{
			return $this->user->getUserId() === $item->getCreatedBy();
		}

		if ($item->getOwnerId() !== $this->user->getUserId())
		{
			return false;
		}

		if (Util::isCollabUser($this->user->getUserId()))
		{
			return false;
		}

		$section = SectionModel::createFromEventModel($item);

		return $this->controller->check(
			ActionDictionary::ACTION_SECTION_EVENT_VIEW_FULL,
			$section,
		);
	}
}
