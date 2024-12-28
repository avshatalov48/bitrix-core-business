<?php

declare(strict_types=1);

namespace Bitrix\SocialNetwork\Collab\Access\Rule;

use Bitrix\Main\Access\AccessibleItem;
use Bitrix\Main\Access\Rule\AbstractRule;
use Bitrix\Main\Localization\Loc;
use Bitrix\Socialnetwork\Permission\User\UserModel;
use Bitrix\SocialNetwork\Collab\Access\CollabAccessController;
use Bitrix\SocialNetwork\Collab\Access\Model\CollabModel;
use Bitrix\SocialNetwork\Collab\Access\Rule\Trait\CanInviteTrait;
use Bitrix\SocialNetwork\Collab\Access\Rule\Trait\GetOptionTrait;
use Bitrix\SocialNetwork\Collab\Access\Rule\Trait\GetRoleTrait;
use Bitrix\Socialnetwork\Collab\Control\Option\Type\WhoCanInviteOption;
use Bitrix\Socialnetwork\Collab\Permission\UserRole;
use Bitrix\Socialnetwork\Collab\User\User;

class CollabInviteRule extends AbstractRule
{
	use GetOptionTrait;
	use GetRoleTrait;
	use CanInviteTrait;

	/** @var CollabAccessController */
	protected $controller;

	/** @var UserModel */
	protected $user;

	public function execute(AccessibleItem $item = null, $params = null): bool
	{
		if (!$item instanceof CollabModel)
		{
			$this->controller->addError(static::class, 'Wrong instance');

			return false;
		}

		if ($this->user->isAdmin())
		{
			return true;
		}

		if ($this->user->getUserId() === $item->getOwnerId())
		{
			return true;
		}

		$canInvite = $this->getCollabOption($item->getId(), WhoCanInviteOption::DB_NAME);
		if (empty($canInvite))
		{
			$this->controller->addError(
				static::class,
				Loc::getMessage('SOCIALNETWORK_COLLAB_INVITE_RULE_PERMISSION_DENIED')
			);

			return false;
		}

		if ($canInvite === UserRole::EMPLOYEE)
		{
			if (!$this->user->isIntranet())
			{
				$this->controller->addError(
					static::class,
					Loc::getMessage('SOCIALNETWORK_COLLAB_INVITE_RULE_PERMISSION_DENIED')
				);

				return false;
			}

			if (!$this->user->isMember($item->getId()))
			{
				$this->controller->addError(
					static::class,
					Loc::getMessage('SOCIALNETWORK_COLLAB_INVITE_RULE_PERMISSION_DENIED')
				);

				return false;
			}

			return true;
		}

		$userRole = $this->getUserRole($item->getId(), $this->user->getUserId());

		if (empty($userRole))
		{
			$this->controller->addError(
				static::class,
				Loc::getMessage('SOCIALNETWORK_COLLAB_INVITE_RULE_PERMISSION_DENIED')
			);

			return false;
		}

		if ($userRole > $canInvite)
		{
			$this->controller->addError(
				static::class,
				Loc::getMessage('SOCIALNETWORK_COLLAB_INVITE_RULE_PERMISSION_DENIED')
			);

			return false;
		}

		if ($this->canInvite($this->user, $item))
		{
			return true;
		}

		$this->controller->addError(
			static::class,
			Loc::getMessage('SOCIALNETWORK_COLLAB_INVITE_RULE_PERMISSION_DENIED_BY_USERS')
		);

		return false;
	}
}