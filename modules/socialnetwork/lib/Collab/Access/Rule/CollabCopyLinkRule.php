<?php

declare(strict_types=1);

namespace Bitrix\SocialNetwork\Collab\Access\Rule;

use Bitrix\Main\Access\AccessibleItem;
use Bitrix\Main\Access\Rule\AbstractRule;
use Bitrix\Socialnetwork\Permission\GroupAccessController;
use Bitrix\Socialnetwork\Permission\GroupDictionary;
use Bitrix\SocialNetwork\Collab\Access\CollabAccessController;
use Bitrix\SocialNetwork\Collab\Access\Model\CollabModel;

class CollabCopyLinkRule extends AbstractRule
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

		$groupController =  GroupAccessController::getInstance($this->user->getUserId());
		if (!$groupController->check(GroupDictionary::VIEW, $item, $params))
		{
			$this->controller->addError(static::class, 'Access denied by group controller');

			$this->controller->addErrors(...$groupController->getErrors());

			return false;
		}

		return true;
	}
}
