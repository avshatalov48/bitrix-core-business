<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage catalog
 * @copyright 2001-2022 Bitrix
 */

namespace Bitrix\Catalog\Access\Role;

use Bitrix\Main\Access\Role\AccessRoleRelationTable;

/**
 * Class CatalogRoleRelationTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_RoleRelation_Query query()
 * @method static EO_RoleRelation_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_RoleRelation_Result getById($id)
 * @method static EO_RoleRelation_Result getList(array $parameters = [])
 * @method static EO_RoleRelation_Entity getEntity()
 * @method static \Bitrix\Catalog\Access\Role\RoleRelation createObject($setDefaultValues = true)
 * @method static \Bitrix\Catalog\Access\Role\EO_RoleRelation_Collection createCollection()
 * @method static \Bitrix\Catalog\Access\Role\RoleRelation wakeUpObject($row)
 * @method static \Bitrix\Catalog\Access\Role\EO_RoleRelation_Collection wakeUpCollection($rows)
 */
class RoleRelationTable extends AccessRoleRelationTable
{
	public static function getTableName()
	{
		return 'b_catalog_role_relation';
	}

	public static function getObjectClass()
	{
		return RoleRelation::class;
	}
}