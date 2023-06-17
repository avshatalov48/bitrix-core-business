<?php

namespace Bitrix\Calendar\Access\Rule;

use Bitrix\Calendar\Access\Model\EventModel;
use Bitrix\Calendar\Access\Model\SectionModel;
use Bitrix\Main\Access\AccessibleItem;
use Bitrix\Calendar\Access\ActionDictionary;
use Bitrix\Calendar\Access\Rule\Traits\CurrentUserTrait;
use Bitrix\Calendar\Core\Event\Tools\Dictionary;

class EventEditRule extends \Bitrix\Main\Access\Rule\AbstractRule
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

		if (isset($params['checkCurrentEvent']) && $params['checkCurrentEvent'] === 'Y')
		{
			$section = SectionModel::createFromEventModel($item);
		}
		else
		{
			$section = SectionModel::createFromEventModelParentFields($item);
		}

		return $this->controller->check(
			ActionDictionary::ACTION_SECTION_EDIT,
			$section,
		);
	}
}