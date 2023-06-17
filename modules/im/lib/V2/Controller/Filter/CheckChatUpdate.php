<?php

namespace Bitrix\Im\V2\Controller\Filter;

use Bitrix\Im\V2\Chat;
use Bitrix\Main\Engine\ActionFilter\Base;
use Bitrix\Main\Error;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

class CheckChatUpdate extends Base
{
	private const UPDATE_UI = [
		'setTitle',
		'setDescription',
		'setColor',
		'setAvatar',
		'setAvatarId',
	];

	private const UPDATE_USERS = [
		'addUsers',
		'removeUsers',
	];

	private const UPDATE_SETTINGS = [
		'setOwner',
		'setManagers',
		'setManageUsers',
		'setManageUI',
		'setManageSettings',
	];

	public function onBeforeAction(Event $event)
	{
		$currentUser = $this->getAction()->getCurrentUser();
		$arguments = $this->getAction()->getArguments();
		/**
		 * @var $chat Chat
		 */
		$chat = $arguments['chat'];

		if ($chat->getAuthorId() === (int)$currentUser->getId())
		{
			return null;
		}

		$actionName = $this->getAction()->getName();
		if (in_array($actionName, self::UPDATE_UI, true))
		{
			$manageRights = $chat->getManageUI();
		}

		if (in_array($actionName, self::UPDATE_USERS, true))
		{
			$manageRights = $chat->getManageUsers();
		}

		if (in_array($actionName, self::UPDATE_SETTINGS, true))
		{
			$manageRights = $chat->getManageSettings();
		}

		if ($manageRights === Chat::MANAGE_RIGHTS_ALL)
		{
			return null;
		}

		$selfRelation = $chat->getSelfRelation();
		if (
			$manageRights === Chat::MANAGE_RIGHTS_MANAGERS
			&& $selfRelation->getManager()
		)
		{
			return null;
		}

		$this->addError(new Error(
			Chat\ChatError::ACCESS_DENIED
		));
		return new EventResult(EventResult::ERROR, null, null, $this);
	}
}
