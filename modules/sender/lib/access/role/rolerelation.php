<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2021 Bitrix
 */

namespace Bitrix\Sender\Access\Role;

use Bitrix\Main\Access\Role\AccessRoleRelationTable;

/**
 * Class RoleRelationTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_RoleRelation_Query query()
 * @method static EO_RoleRelation_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_RoleRelation_Result getById($id)
 * @method static EO_RoleRelation_Result getList(array $parameters = array())
 * @method static EO_RoleRelation_Entity getEntity()
 * @method static \Bitrix\Sender\Access\Role\EO_RoleRelation createObject($setDefaultValues = true)
 * @method static \Bitrix\Sender\Access\Role\EO_RoleRelation_Collection createCollection()
 * @method static \Bitrix\Sender\Access\Role\EO_RoleRelation wakeUpObject($row)
 * @method static \Bitrix\Sender\Access\Role\EO_RoleRelation_Collection wakeUpCollection($rows)
 */
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