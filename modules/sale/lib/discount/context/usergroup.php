<?php

namespace Bitrix\Sale\Discount\Context;

class UserGroup extends BaseContext
{
	/**
	 * UserGroup constructor.
	 *
	 * @param array $userGroups
	 */
	public function __construct(array $userGroups)
	{
		$this->userId = static::GUEST_USER_ID;
		$this->setUserGroups($userGroups);
	}
}