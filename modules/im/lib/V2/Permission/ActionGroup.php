<?php

namespace Bitrix\Im\V2\Permission;

use Bitrix\Im\V2\Chat;

enum ActionGroup: string
{
	case ManageUi = 'MANAGE_UI';
	case ManageUsersAdd = 'MANAGE_USERS_ADD';
	case ManageUsersDelete = 'MANAGE_USERS_DELETE';
	case ManageSettings = 'MANAGE_SETTINGS';
	case ManageMessages = 'MANAGE_MESSAGES';

	public static function tryFromAction(Action $action): ?ActionGroup
	{
		$actionName = $action->value;

		foreach (self::cases() as $case)
		{
			if (in_array($actionName, $case->getActions(), true))
			{
				return $case;
			}
		}

		return null;
	}

	/**
	 * @return Action[]
	 */
	public function getActions(): array
	{
		return match ($this)
		{
			self::ManageUi => [
				Action::Rename->value,
				Action::ChangeDescription->value,
				Action::ChangeColor->value,
				Action::ChangeAvatar->value
			],
			self::ManageUsersAdd => [Action::Extend->value],
			self::ManageUsersDelete => [Action::Kick->value],
			self::ManageSettings => [Action::ChangeRight->value, Action::ChangeOwner->value, Action::ChangeManagers->value],
			self::ManageMessages => [Action::Send->value, Action::PinMessage->value],
		};
	}

	public static function getDefinitions(): array
	{
		$result = [];

		foreach (self::cases() as $case)
		{
			$result[$case->value] = $case->getActions();
		}

		return $result;
	}

	public static function getDefaultPermissions(): array
	{
		return [
			self::ManageUi->value => Chat::ROLE_MEMBER,
			self::ManageUsersAdd->value => Chat::ROLE_MEMBER,
			self::ManageUsersDelete->value => Chat::ROLE_MANAGER,
			self::ManageSettings->value => Chat::ROLE_OWNER,
			self::ManageMessages->value => Chat::ROLE_MEMBER,
		];
	}
}