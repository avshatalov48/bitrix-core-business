<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Permission\Rule;

use Bitrix\Main\Access\AccessibleItem;
use Bitrix\Main\Access\Rule\AbstractRule;
use Bitrix\Socialnetwork\Permission\GroupAccessController;
use Bitrix\Socialnetwork\Permission\Model\GroupModel;

class GroupConvertRule extends AbstractRule
{
	/** @var GroupAccessController */
	protected $controller;

	public function execute(AccessibleItem $item = null, $params = null): bool
	{
		if (!$item instanceof GroupModel)
		{
			$this->controller->addError(static::class, 'Wrong instance');

			return false;
		}

		if ($this->user->getUserId() !== $item->getOwnerId())
		{
			$this->controller->addError(static::class, 'Access denied by not-owner role');

			return false;
		}

		return true;
	}
}
