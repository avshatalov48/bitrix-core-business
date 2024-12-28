<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Permission\Rule;

use Bitrix\Main\Access\AccessibleItem;
use Bitrix\Main\Access\Rule\AbstractRule;
use Bitrix\Socialnetwork\Permission\GroupAccessController;
use Bitrix\Socialnetwork\Permission\Model\GroupModel;
use Bitrix\Socialnetwork\Permission\Rule\Trait\AccessTrait;

class GroupRemoveModeratorRule extends AbstractRule
{
	use AccessTrait;

	/** @var GroupAccessController */
	protected $controller;

	public function execute(AccessibleItem $item = null, $params = null): bool
	{
		if (!$item instanceof GroupModel)
		{
			$this->controller->addError(static::class, 'Wrong instance');

			return false;
		}

		$target = (int)($params['userId'] ?? null);
		if ($target <= 0)
		{
			$this->controller->addError('Wrong target');

			return false;
		}

		if ($item->getId() <= 0)
		{
			$this->controller->addError(static::class, 'Group not found');

			return false;
		}

		if (!$this->getAccessManager($item, $target, $this->user->getUserId())->canRemoveModerator())
		{
			$this->controller->addError(static::class, 'Access denied by permissions');

			return false;
		}

		return true;
	}
}