<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage catalog
 * @copyright 2001-2022 Bitrix
 */

namespace Bitrix\Catalog\Access\Permission;

use Bitrix\Main\Access\Permission\AccessPermissionTable;

/**
 * Class CatalogPermissionTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Permission_Query query()
 * @method static EO_Permission_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_Permission_Result getById($id)
 * @method static EO_Permission_Result getList(array $parameters = [])
 * @method static EO_Permission_Entity getEntity()
 * @method static \Bitrix\Catalog\Access\Permission\Permission createObject($setDefaultValues = true)
 * @method static \Bitrix\Catalog\Access\Permission\EO_Permission_Collection createCollection()
 * @method static \Bitrix\Catalog\Access\Permission\Permission wakeUpObject($row)
 * @method static \Bitrix\Catalog\Access\Permission\EO_Permission_Collection wakeUpCollection($rows)
 */
class PermissionTable extends AccessPermissionTable
{
	public static function getTableName()
	{
		return 'b_catalog_permission';
	}

	public static function getObjectClass()
	{
		return Permission::class;
	}
}