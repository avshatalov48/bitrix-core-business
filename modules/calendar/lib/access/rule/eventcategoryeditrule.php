<?php

namespace Bitrix\Calendar\Access\Rule;

use Bitrix\Calendar\Access\Model\EventCategoryModel;
use Bitrix\Main\Access\AccessibleItem;
use Bitrix\Main\Access\Rule\AbstractRule;

final class EventCategoryEditRule extends AbstractRule
{
	public function execute(AccessibleItem $item = null, $params = null): bool
	{
		if (!$item instanceof EventCategoryModel)
		{
			return false;
		}

		if ($this->user->isAdmin())
		{
			return true;
		}

		if ($item->getCreatorId() === $this->user->getUserId())
		{
			return true;
		}

		return false;
	}
}
