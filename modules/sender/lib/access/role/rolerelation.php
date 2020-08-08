<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2021 Bitrix
 */

namespace Bitrix\Sender\Access\Role;

use Bitrix\Main\Access\Role\AccessRoleRelationTable;

class RoleRelationTable extends AccessRoleRelationTable
{
	/**
	 * Get table name.
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sender_role_relation';
	}
}