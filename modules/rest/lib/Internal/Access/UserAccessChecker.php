<?php

declare(strict_types=1);

namespace Bitrix\Rest\Internal\Access;

class UserAccessChecker
{
	public function __construct(private readonly int $userId)
	{
	}

	public function canAuthorize(): bool
	{
		$user = \CUser::GetByID($this->userId)->fetch();

		if (
			!empty($user['CONFIRM_CODE'])
			|| (
				!in_array($user['EXTERNAL_AUTH_ID'], \Bitrix\Main\UserTable::getExternalUserTypes(), true)
				&& (empty($user['LAST_LOGIN']) || empty($user['LAST_ACTIVITY_DATE']))
			)
		)
		{
			return false;
		}

		return (bool)$user;
	}
}
