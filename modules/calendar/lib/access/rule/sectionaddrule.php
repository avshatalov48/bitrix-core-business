<?php

namespace Bitrix\Calendar\Access\Rule;

use Bitrix\Calendar\Access\Model\SectionModel;
use Bitrix\Calendar\Access\Model\TypeModel;
use Bitrix\Calendar\Access\Rule\Traits\CurrentUserTrait;
use Bitrix\Calendar\Access\Rule\Traits\ExtranetUserTrait;
use Bitrix\Calendar\Core\Event\Tools\Dictionary;
use Bitrix\Calendar\Util;
use Bitrix\Main\Access\AccessibleItem;
use Bitrix\Calendar\Access\ActionDictionary;
use Bitrix\Calendar\Access\Rule\Traits\SectionTrait;

class SectionAddRule extends \Bitrix\Main\Access\Rule\AbstractRule
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

		if (Util::isCollabUser($this->user->getUserId()))
		{
			return false;
		}

		if (!$this->canSeeOwnerIfExtranetUser($item, $this->user))
		{
			return false;
		}

		if ($item->getType() === Dictionary::CALENDAR_TYPE['open_event'])
		{
			return true;
		}

		if ($item->getType() === Dictionary::CALENDAR_TYPE['user'])
		{
			return $this->isOwner($item, $this->user->getUserId());
		}

		$type = TypeModel::createFromSectionModel($item);

		return
			$this->controller->check(
				ActionDictionary::ACTION_TYPE_EDIT,
				$type,
			);
	}
}
