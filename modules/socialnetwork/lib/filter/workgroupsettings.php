<?php

namespace Bitrix\Socialnetwork\Filter;

class WorkgroupSettings extends \Bitrix\Main\Filter\EntitySettings
{
	/**
	 * Get Entity Type ID.
	 * @return int
	 */
	public function getEntityTypeName()
	{
		return 'WORKGROUP';
	}

	/**
	 * Get User Field Entity ID.
	 * @return string
	 */
	public function getUserFieldEntityID()
	{
		return \Bitrix\Socialnetwork\WorkgroupTable::getUfId();
	}
}
