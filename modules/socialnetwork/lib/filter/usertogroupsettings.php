<?php

namespace Bitrix\Socialnetwork\Filter;

class UserToGroupSettings extends \Bitrix\Main\Filter\EntitySettings
{
	/**
	 * Get Entity Type ID.
	 * @return int
	 */
	public function getEntityTypeName()
	{
		return 'USER_TO_WORKGROUP';
	}

	/**
	 * Get User Field Entity ID.
	 * @return string
	 */
	public function getUserFieldEntityID()
	{
		return \Bitrix\Socialnetwork\UserToGroupTable::getUfId();
	}
}
