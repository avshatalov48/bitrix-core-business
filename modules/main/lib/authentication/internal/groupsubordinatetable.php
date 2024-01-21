<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2023 Bitrix
 */

namespace Bitrix\Main\Authentication\Internal;

use Bitrix\Main\ORM\Fields;
use Bitrix\Main\ORM\Data;

/**
 * Class GroupSubordinateTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_GroupSubordinate_Query query()
 * @method static EO_GroupSubordinate_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_GroupSubordinate_Result getById($id)
 * @method static EO_GroupSubordinate_Result getList(array $parameters = [])
 * @method static EO_GroupSubordinate_Entity getEntity()
 * @method static \Bitrix\Main\Authentication\Internal\EO_GroupSubordinate createObject($setDefaultValues = true)
 * @method static \Bitrix\Main\Authentication\Internal\EO_GroupSubordinate_Collection createCollection()
 * @method static \Bitrix\Main\Authentication\Internal\EO_GroupSubordinate wakeUpObject($row)
 * @method static \Bitrix\Main\Authentication\Internal\EO_GroupSubordinate_Collection wakeUpCollection($rows)
 */
class GroupSubordinateTable extends Data\DataManager
{
	public static function getTableName()
	{
		return 'b_group_subordinate';
	}

	public static function getMap()
	{
		return [
			(new Fields\IntegerField('ID'))
				->configurePrimary(),

			(new Fields\TextField('AR_SUBGROUP_ID')),
		];
	}
}
