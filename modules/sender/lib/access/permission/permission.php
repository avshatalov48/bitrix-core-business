<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2021 Bitrix
 */

namespace Bitrix\Sender\Access\Permission;

use Bitrix\Main\Access\Permission\AccessPermissionTable;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class PermissionTable extends AccessPermissionTable
{
	/**
	 * Get table name.
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sender_permission';
	}
}