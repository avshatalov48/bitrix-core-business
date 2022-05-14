<?php

namespace Bitrix\Socialnetwork\Internals\Counter;

use Bitrix\Socialnetwork\UserToGroupTable;

class Role
{
	public const ENTITY_WORKGROUP = 'WORKGROUP';

	public const PREFIX_WORKGROUP = 'WORKGROUP_';

	public const ALL = '';
	public const WORKGROUP_OWNER = self::PREFIX_WORKGROUP . UserToGroupTable::ROLE_OWNER;
	public const WORKGROUP_MODERATOR = self::PREFIX_WORKGROUP . UserToGroupTable::ROLE_MODERATOR;
	public const WORKGROUP_USER = self::PREFIX_WORKGROUP . UserToGroupTable::ROLE_USER;
	public const WORKGROUP_REQUEST = self::PREFIX_WORKGROUP . UserToGroupTable::ROLE_REQUEST;

	public const ROLE_MAP = [
		CounterDictionary::ENTITY_WORKGROUP_DETAIL => [
			UserToGroupTable::ROLE_OWNER => self::WORKGROUP_OWNER,
			UserToGroupTable::ROLE_MODERATOR => self::WORKGROUP_MODERATOR,
			UserToGroupTable::ROLE_USER => self::WORKGROUP_USER,
			UserToGroupTable::ROLE_REQUEST => self::WORKGROUP_REQUEST,
		],
	];

	public static function get(array $params = []): string
	{
		$entityType = $params['entityType'] ?? null;
		$role = $params['role'] ?? null;

		if (
			empty($entityType)
			|| empty($role)
			|| !isset(self::ROLE_MAP[$entityType][$role])
		)
		{
			return self::ALL;
		}

		return self::ROLE_MAP[$entityType][$role];
	}

}