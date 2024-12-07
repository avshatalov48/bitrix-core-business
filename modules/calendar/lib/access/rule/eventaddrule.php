<?php

namespace Bitrix\Calendar\Access\Rule;

use Bitrix\Calendar\Access\Model\EventModel;
use Bitrix\Calendar\Access\Model\SectionModel;
use Bitrix\Calendar\Access\Rule\Traits\CategoryTrait;
use Bitrix\Calendar\Core\Event\Tools\Dictionary;
use Bitrix\Main\Access\AccessibleItem;
use Bitrix\Calendar\Access\ActionDictionary;
use Bitrix\Calendar\Access\Rule\Traits\CurrentUserTrait;

class EventAddRule extends \Bitrix\Main\Access\Rule\AbstractRule
{
	use CurrentUserTrait;
	use CategoryTrait;

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

		// on early stages of creating open event not connected to category
		// this rule not fired on most cases, but should be presented for better consistency
		if ($item->getSectionType() === Dictionary::CALENDAR_TYPE['open_event'])
		{
			return $this->checkCategoryByEvent($item, ActionDictionary::ACTION_EVENT_CATEGORY_POST);
		}

		$section = SectionModel::createFromEventModel($item);

		return $this->controller->check(
			ActionDictionary::ACTION_SECTION_EDIT,
			$section,
		);
	}
}
