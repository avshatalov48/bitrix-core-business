<?php

declare(strict_types=1);

namespace Bitrix\SocialNetwork\Collab\Access\Rule;

use Bitrix\Main\Access\AccessibleItem;
use Bitrix\Main\Access\Rule\AbstractRule;
use Bitrix\SocialNetwork\Collab\Access\CollabAccessController;
use Bitrix\SocialNetwork\Collab\Access\Model\CollabModel;

class CollabCreateRule extends AbstractRule
{
	/** @var CollabAccessController */
	protected $controller;

	public function execute(AccessibleItem $item = null, $params = null): bool
	{
		if (!$item instanceof CollabModel)
		{
			$this->controller->addError(static::class, 'Wrong instance');

			return false;
		}

		if ($this->user->isCollaber())
		{
			$this->controller->addError(static::class, 'Access denied by collaber role');

			return false;
		}

		return true;
	}
}