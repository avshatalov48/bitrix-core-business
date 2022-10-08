<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2022 Bitrix
 */

namespace Bitrix\Main\Authentication\Internal;

use Bitrix\Main;
use Bitrix\Main\ORM\Data;
use Bitrix\Main\ORM\Fields;
use Bitrix\Main\ORM\Query\Join;

/**
 * Class ModuleGroupTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_ModuleGroup_Query query()
 * @method static EO_ModuleGroup_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_ModuleGroup_Result getById($id)
 * @method static EO_ModuleGroup_Result getList(array $parameters = [])
 * @method static EO_ModuleGroup_Entity getEntity()
 * @method static \Bitrix\Main\Authentication\Internal\EO_ModuleGroup createObject($setDefaultValues = true)
 * @method static \Bitrix\Main\Authentication\Internal\EO_ModuleGroup_Collection createCollection()
 * @method static \Bitrix\Main\Authentication\Internal\EO_ModuleGroup wakeUpObject($row)
 * @method static \Bitrix\Main\Authentication\Internal\EO_ModuleGroup_Collection wakeUpCollection($rows)
 */
class ModuleGroupTable extends Data\DataManager
{
	public static function getTableName()
	{
		return 'b_module_group';
	}

	public static function getMap()
	{
		return [
			(new Fields\IntegerField('ID'))
				->configurePrimary()
				->configureAutocomplete(),

			(new Fields\StringField('MODULE_ID')),

			(new Fields\IntegerField('GROUP_ID')),

			(new Fields\StringField('G_ACCESS')),

			(new Fields\StringField('SITE_ID')),

			(new Fields\Relations\Reference(
				'GROUP',
				Main\GroupTable::class,
				Join::on('this.GROUP_ID', 'ref.ID')
			))->configureJoinType(Join::TYPE_INNER),
		];
	}
}
