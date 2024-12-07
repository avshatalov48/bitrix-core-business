<?php

namespace Bitrix\Calendar\Access\Rule;

use Bitrix\Calendar\Access\Model\EventModel;
use Bitrix\Calendar\Access\Rule\Traits\CategoryTrait;
use Bitrix\Calendar\Core\Event\Tools\Dictionary;
use Bitrix\Main\Access\AccessibleItem;
use Bitrix\Main\Access\Rule\AbstractRule;

final class OpenEventAttendRule extends AbstractRule
{
	use CategoryTrait;

	public function execute(AccessibleItem $item = null, $params = null): bool
	{
		if (!$item instanceof EventModel)
		{
			return false;
		}

		if ($item->getSectionType() !== Dictionary::CALENDAR_TYPE['open_event'])
		{
			return false;
		}

		if ($this->user->isAdmin())
		{
			return true;
		}

		return $this->checkCategoryByEvent($item);
	}
}
