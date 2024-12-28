<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Permission;

use Bitrix\Socialnetwork\UserToGroupTable;

class UserRole
{
	public const OWNER = UserToGroupTable::ROLE_OWNER;
	public const MODERATOR = UserToGroupTable::ROLE_MODERATOR;
	public const MEMBER = UserToGroupTable::ROLE_USER;
	public const REQUEST = UserToGroupTable::ROLE_REQUEST;
	/** @see SONET_ROLES_EMPLOYEE */
	public const EMPLOYEE = 'J';

	public const ALLOWED_ROLES = [
		self::OWNER,
		self::MODERATOR,
		self::MEMBER,
	];

	public const COLLAB_ROLES = [
		self::OWNER,
		self::MODERATOR,
		self::MEMBER,
	];
}