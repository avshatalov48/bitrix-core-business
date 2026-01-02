<?php

namespace Bitrix\Calendar\Access\Rule;

use Bitrix\Calendar\Core;
use Bitrix\Calendar\Access\Model\SyncModel;
use Bitrix\Main\Access\AccessibleItem;
use Bitrix\Main\Access\Rule\AbstractRule;

final class SyncDeleteRule extends AbstractRule
{
	public function execute(AccessibleItem $item = null, $params = null): bool
	{
		if (!$item instanceof SyncModel)
		{
			return false;
		}

		if ($this->user->isAdmin())
		{
			return true;
		}

		return $item->getEntityType() === Core\Role\User::TYPE && $item->getEntityId() === $this->user->getUserId();
	}
}
