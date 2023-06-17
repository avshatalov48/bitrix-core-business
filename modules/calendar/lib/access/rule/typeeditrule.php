<?php

namespace Bitrix\Calendar\Access\Rule;

use Bitrix\Calendar\Access\Model\TypeModel;
use Bitrix\Main\Access\AccessibleItem;
use Bitrix\Calendar\Access\ActionDictionary;
use Bitrix\Calendar\Access\Rule\Traits\CurrentUserTrait;
use CCalendarType;

class TypeEditRule extends \Bitrix\Main\Access\Rule\AbstractRule
{
	use CurrentUserTrait;

	public function execute(AccessibleItem $item = null, $params = null): bool
	{
		if (!$item instanceof TypeModel)
		{
			return false;
		}

		if (!$this->hasCurrentUser())
		{
			return true;
		}

		if ($this->user->isAdmin() || $this->user->isSocNetAdmin($item->getXmlId()))
		{
			return true;
		}

		return in_array(
			ActionDictionary::getOldActionKeyByNewActionKey(ActionDictionary::ACTION_TYPE_EDIT),
			CCalendarType::GetOperations($item->getXmlId(), $this->user->getUserId()),
			true
		);
	}
}