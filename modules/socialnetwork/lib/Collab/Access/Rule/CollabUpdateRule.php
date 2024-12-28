<?php

declare(strict_types=1);

namespace Bitrix\SocialNetwork\Collab\Access\Rule;

use Bitrix\Main\Access\AccessibleItem;
use Bitrix\Main\Access\Rule\AbstractRule;
use Bitrix\Socialnetwork\Permission\GroupAccessController;
use Bitrix\Socialnetwork\Permission\GroupDictionary;
use Bitrix\SocialNetwork\Collab\Access\CollabAccessController;
use Bitrix\SocialNetwork\Collab\Access\CollabDictionary;
use Bitrix\SocialNetwork\Collab\Access\Model\CollabModel;
use Bitrix\SocialNetwork\Collab\Access\Rule\Trait\GetOptionTrait;
use Bitrix\Socialnetwork\Collab\Controller\Collab;

class CollabUpdateRule extends AbstractRule
{
	use GetOptionTrait;

	/** @var CollabAccessController */
	protected $controller;

	public function execute(AccessibleItem $item = null, $params = null): bool
	{
		if (!$item instanceof CollabModel)
		{
			$this->controller->addError(static::class, 'Wrong instance');

			return false;
		}

		/** @var Collab $collabBefore */
		$collabBefore = $params;
		$collabAfter = $item;

		$addMembers = array_merge($collabAfter->getAddInvitedMembers(), $collabAfter->getAddMembers());
		if (
			!empty($addMembers)
			&& !$this->controller->check(CollabDictionary::INVITE, $item, $params)
		)
		{
			$this->controller->addError(static::class, 'Access denied by invite rule');

			return false;
		}

		$deleteMembers = $collabAfter->getDeleteMembers();
		if (
			!empty($deleteMembers)
			&& !$this->controller->check(CollabDictionary::EXCLUDE, $item, $params)
		)
		{
			$this->controller->addError(static::class, 'Access denied by exclude rule');

			return false;
		}

		$deleteModeratorMembers = $collabAfter->getDeleteModeratorMembers();
		if (
			!empty($deleteModeratorMembers)
			&& !$this->controller->check(CollabDictionary::EXCLUDE_MODERATOR, $item, $params)
		)
		{
			$this->controller->addError(static::class, 'Access denied by exclude moderator rule');

			return false;
		}

		$addModeratorMembers = $collabAfter->getAddModeratorMembers();
		if (
			!empty($addModeratorMembers)
			&& !$this->controller->check(CollabDictionary::SET_MODERATOR, $item, $params)
		)
		{
			$this->controller->addError(static::class, 'Access denied by set moderator rule');

			return false;
		}

		if (!$this->controller->forward(GroupAccessController::class, GroupDictionary::UPDATE, $item, $params))
		{
			$this->controller->addError(static::class, 'Access denied by group update rule');

			return false;
		}

		return true;
	}
}