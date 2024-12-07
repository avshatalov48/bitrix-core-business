<?php

namespace Bitrix\Calendar\Access\Rule;

use Bitrix\Calendar\Access\Model\EventCategoryModel;
use Bitrix\Calendar\Access\Rule\Traits\CurrentUserTrait;
use Bitrix\Calendar\OpenEvents\Service\CategoryAttendeeService;
use Bitrix\Main\Access\AccessibleItem;
use Bitrix\Main\Access\Rule\AbstractRule;

final class EventCategoryViewRule extends AbstractRule
{
	use CurrentUserTrait;

	public function execute(AccessibleItem $item = null, $params = null): bool
	{
		if (!$item instanceof EventCategoryModel)
		{
			return false;
		}

		if (!$this->hasCurrentUser())
		{
			return true;
		}

		if ($this->user->isAdmin())
		{
			return true;
		}

		if (!$item->isClosed())
		{
			return true;
		}

		return CategoryAttendeeService::getInstance()->isAttendee($item->getId(), $this->user->getUserId());
	}
}
