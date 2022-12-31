<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage catalog
 * @copyright 2001-2022 Bitrix
 */
namespace Bitrix\Catalog\Access\Role;

use Bitrix\Main\Access\Role\AccessRoleTable;

/**
 * Class CatalogRoleTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Role_Query query()
 * @method static EO_Role_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_Role_Result getById($id)
 * @method static EO_Role_Result getList(array $parameters = [])
 * @method static EO_Role_Entity getEntity()
 * @method static \Bitrix\Catalog\Access\Role\Role createObject($setDefaultValues = true)
 * @method static \Bitrix\Catalog\Access\Role\EO_Role_Collection createCollection()
 * @method static \Bitrix\Catalog\Access\Role\Role wakeUpObject($row)
 * @method static \Bitrix\Catalog\Access\Role\EO_Role_Collection wakeUpCollection($rows)
 */
class RoleTable extends AccessRoleTable
{

	public static function getTableName()
	{
		return 'b_catalog_role';
	}

	public static function getObjectClass()
	{
		return Role::class;
	}
}