<?php

namespace Bitrix\Sale\Discount\Context;

class User extends BaseContext
{
	/**
	 * User constructor.
	 *
	 * @param \CUser|int $user
	 */
	public function __construct($user)
	{
		$this->userId = static::resolveUserId($user);
		$this->setUserGroups(\CUser::getUserGroup($this->userId));
	}

	/**
	 * Resolves userId from parameter $user.
	 *
	 * @param \CUser|int $user Different types: User model, CUser, id of user.
	 * @return int|null
	 */
	private static function resolveUserId($user)
	{
		if ($user instanceof \CUser)
		{
			return (int)$user->getId();
		}
		elseif(is_numeric($user) && (int)$user > 0)
		{
			return (int)$user;
		}

		return static::GUEST_USER_ID;
	}
}