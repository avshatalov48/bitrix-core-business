<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage tasks
 * @copyright 2001-2021 Bitrix
 */

namespace Bitrix\Main\UI\Selector;

class Handler
{
	public const
		ENTITY_TYPE_GROUP = 'USERGROUPS';

	public static function getProviderByEntityType($entityType)
	{
		switch($entityType)
		{
			case self::ENTITY_TYPE_GROUP:
				$provider = new UserGroups();
				break;
			default:
				$provider = false;
		}

		return $provider;
	}
}