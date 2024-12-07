<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2024 Bitrix
 */

namespace Bitrix\Main;

use Bitrix\Main\ORM\Data;
use Bitrix\Main\ORM\Fields;

/**
 * Class ModuleTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Module_Query query()
 * @method static EO_Module_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_Module_Result getById($id)
 * @method static EO_Module_Result getList(array $parameters = [])
 * @method static EO_Module_Entity getEntity()
 * @method static \Bitrix\Main\EO_Module createObject($setDefaultValues = true)
 * @method static \Bitrix\Main\EO_Module_Collection createCollection()
 * @method static \Bitrix\Main\EO_Module wakeUpObject($row)
 * @method static \Bitrix\Main\EO_Module_Collection wakeUpCollection($rows)
 */
class ModuleTable extends Data\DataManager
{
	public static function getTableName()
	{
		return 'b_module';
	}

	public static function getMap()
	{
		return [
			(new Fields\StringField('ID'))
				->configurePrimary(),

			(new Fields\DatetimeField('DATE_ACTIVE')),
		];
	}
}
