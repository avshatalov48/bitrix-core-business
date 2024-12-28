<?php

namespace Bitrix\Calendar\Access\Rule;

use Bitrix\Calendar\Access\Model\SectionModel;
use Bitrix\Calendar\Access\Model\TypeModel;
use Bitrix\Calendar\Access\Rule\Traits\ExtranetUserTrait;
use Bitrix\Calendar\Core\Event\Tools\Dictionary;
use Bitrix\Calendar\Integration\SocialNetwork\Collab\UserCollabs;
use Bitrix\Calendar\Util;
use Bitrix\Main\Access\AccessibleItem;
use Bitrix\Calendar\Access\ActionDictionary;
use Bitrix\Calendar\Access\Rule\Traits\CurrentUserTrait;
use Bitrix\Calendar\Access\Rule\Traits\SectionTrait;
use CCalendarSect;

class SectionAccessRule extends \Bitrix\Main\Access\Rule\AbstractRule
{
	use SectionTrait, CurrentUserTrait, ExtranetUserTrait;

	public function execute(AccessibleItem $item = null, $params = null): bool
	{
		if (!$item instanceof SectionModel)
		{
			return false;
		}

		if (!$this->hasCurrentUser())
		{
			return true;
		}

		if (!$this->canSeeOwnerIfExtranetUser($item, $this->user))
		{
			return false;
		}

		if ($this->user->isAdmin() || $this->user->isSocNetAdmin($item->getType()))
		{
			return true;
		}

		if ($this->isOwner($item, $this->user->getUserId()))
		{
			return true;
		}

		if ($item->getType() === Dictionary::CALENDAR_TYPE['open_event'])
		{
			return true;
		}

		$type = TypeModel::createFromSectionModel($item);
		$typeCheck = true;

		if ($item->getType() !== \Bitrix\Calendar\Core\Event\Tools\Dictionary::CALENDAR_TYPE['resource'])
		{
			$typeCheck = $this->controller->check(ActionDictionary::ACTION_TYPE_ACCESS, $type);
		}

		return
			$typeCheck
			&& in_array(
				ActionDictionary::getOldActionKeyByNewActionKey(ActionDictionary::ACTION_SECTION_ACCESS),
				CCalendarSect::GetOperations($item->getId(), $this->user->getUserId()),
				true
			);
	}
}
