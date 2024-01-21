<?php

namespace Bitrix\Calendar\Access\Rule;

use Bitrix\Calendar\Access\Model\EventModel;
use Bitrix\Calendar\Access\Model\SectionModel;
use Bitrix\Calendar\Access\Rule\Traits\SharingTrait;
use Bitrix\Calendar\Sharing\SharingEventManager;
use Bitrix\Main\Access\AccessibleItem;
use Bitrix\Calendar\Access\ActionDictionary;
use Bitrix\Calendar\Access\Rule\Traits\CurrentUserTrait;

class EventDeleteRule extends \Bitrix\Main\Access\Rule\AbstractRule
{
	use CurrentUserTrait;
	use SharingTrait;

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

		return $this->controller->check(
			ActionDictionary::ACTION_EVENT_EDIT,
			$item,
		);
	}
}