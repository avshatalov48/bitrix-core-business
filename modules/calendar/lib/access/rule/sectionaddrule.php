<?php

namespace Bitrix\Calendar\Access\Rule;

use Bitrix\Calendar\Access\Model\SectionModel;
use Bitrix\Calendar\Access\Model\TypeModel;
use Bitrix\Calendar\Access\Rule\Traits\CurrentUserTrait;
use Bitrix\Main\Access\AccessibleItem;
use Bitrix\Calendar\Access\ActionDictionary;
use Bitrix\Calendar\Access\Rule\Traits\SectionTrait;
use Bitrix\Calendar\Core\Event;

class SectionAddRule extends \Bitrix\Main\Access\Rule\AbstractRule
{
	use SectionTrait, CurrentUserTrait;

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

		if ($this->isOwner($item, $this->user->getUserId()))
		{
			return true;
		}
		elseif ($item->getType() === Event\Tools\Dictionary::CALENDAR_TYPE['user'])
		{
			return false;
		}

		$type = TypeModel::createFromSectionModel($item);

		return
			$this->controller->check(
				ActionDictionary::ACTION_TYPE_EDIT,
				$type,
			);
	}
}