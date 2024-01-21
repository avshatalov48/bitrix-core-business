<?php

namespace Bitrix\Im\V2\Controller\Filter;

use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Chat\ChatError;
use Bitrix\Main\Engine\ActionFilter\Base;
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

	private const UPDATE_USERS_ADD = [
		'addUsers',
	];

	private const UPDATE_USERS_DELETE = [
		'deleteUser',
	];

	private const UPDATE_SETTINGS = [
		'setOwner',
		'setManagers',
		'setManageUsersAdd',
		'setManageUsersDelete',
		'setManageUI',
		'setManageSettings',
		'setDisappearingDate',
		'setCanPost',
	];

	public function onBeforeAction(Event $event)
	{
		$currentUser = $this->getAction()->getCurrentUser();
		$arguments = $this->getAction()->getArguments();
		/**
		 * @var $chat Chat
		 */
		$chat = $arguments['chat'];

		if (!$chat->getChatId())
		{
			$this->addError(new ChatError(
				ChatError::ACCESS_DENIED
			));
			return new EventResult(EventResult::ERROR, null, null, $this);
		}

		if ($currentUser->isAdmin())
		{
			return null;
		}

		if ($chat->getAuthorId() === (int)$currentUser->getId())
		{
			return null;
		}

		$actionName = $this->getAction()->getName();
		if ($this->inArrayCaseInsensitive($actionName, self::UPDATE_UI, true))
		{
			$manageRights = $chat->getManageUI();
		}

		if ($this->inArrayCaseInsensitive($actionName, self::UPDATE_USERS_ADD, true))
		{
			$manageRights = $chat->getManageUsersAdd();
		}

		if ($this->inArrayCaseInsensitive($actionName, self::UPDATE_USERS_DELETE, true))
		{
			$deleteUser = $arguments['userId'] ?? null;

			if ((int)$deleteUser === (int)$currentUser->getId())
			{
				return null;
			}

			$manageRights = $chat->getManageUsersDelete();
		}

		if ($this->inArrayCaseInsensitive($actionName, self::UPDATE_SETTINGS, true))
		{
			$manageRights = $chat->getManageSettings();
		}

		if ($manageRights === Chat::MANAGE_RIGHTS_MEMBER)
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

		$this->addError(new ChatError(
			ChatError::ACCESS_DENIED
		));
		return new EventResult(EventResult::ERROR, null, null, $this);
	}

	/**
	 * @param string $needle
	 * @param string[] $haystack
	 * @param bool $strict
	 * @return bool
	 */
	private function inArrayCaseInsensitive(string $needle, array $haystack, bool $strict = true): bool
	{
		$needle = mb_strtolower($needle);
		$haystack = array_map('mb_strtolower', $haystack);

		return in_array($needle, $haystack, $strict);
	}
}