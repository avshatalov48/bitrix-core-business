<?php

declare(strict_types=1);

namespace Bitrix\SocialNetwork\Collab\Access\Rule;

use Bitrix\Main\Access\AccessCode;
use Bitrix\Main\Access\AccessibleItem;
use Bitrix\Main\Access\Rule\AbstractRule;
use Bitrix\Socialnetwork\Permission\GroupAccessController;
use Bitrix\Socialnetwork\Permission\GroupDictionary;
use Bitrix\SocialNetwork\Collab\Access\CollabAccessController;
use Bitrix\SocialNetwork\Collab\Access\Model\CollabModel;

class CollabExcludeModeratorRule extends AbstractRule
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

		$deleteModeratorMembers = $item->getDeleteModeratorMembers();
		foreach ($deleteModeratorMembers as $accessCode)
		{
			$userId = (new AccessCode($accessCode))->getEntityId();

			if (
				!$this->controller->forward(
					GroupAccessController::class,
					GroupDictionary::REMOVE_MODERATOR,
					$item,
					['userId' => $userId]
				)
			)
			{
				$this->controller->addError(static::class, 'Access denied by group controller');

				return false;
			}
		}

		return true;
	}
}