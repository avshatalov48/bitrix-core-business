<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2025 Bitrix
 */

namespace Bitrix\Main;

use Bitrix\Main\ORM\Fields;
use Bitrix\Main\ORM\Query\Join;

/**
 * Class GroupTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Group_Query query()
 * @method static EO_Group_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_Group_Result getById($id)
 * @method static EO_Group_Result getList(array $parameters = [])
 * @method static EO_Group_Entity getEntity()
 * @method static \Bitrix\Main\EO_Group createObject($setDefaultValues = true)
 * @method static \Bitrix\Main\EO_Group_Collection createCollection()
 * @method static \Bitrix\Main\EO_Group wakeUpObject($row)
 * @method static \Bitrix\Main\EO_Group_Collection wakeUpCollection($rows)
 */
class GroupTable extends ORM\Data\DataManager
{
	public static function getTableName()
	{
		return 'b_group';
	}

	public static function getMap()
	{
		return [
			(new Fields\IntegerField('ID'))
				->configurePrimary()
				->configureAutocomplete()
				->configureNullable(false)
			,
			(new Fields\DatetimeField('TIMESTAMP_X'))
				->configureDefaultValueNow()
				->configureNullable()
			,
			(new Fields\BooleanField('ACTIVE'))
				->configureValues('N', 'Y')
				->configureDefaultValue('Y')
				->configureNullable(false)
			,
			(new Fields\IntegerField('C_SORT'))
				->configureDefaultValue(100)
				->configureNullable(false)
			,
			(new Fields\BooleanField('ANONYMOUS'))
				->configureValues('N', 'Y')
				->configureDefaultValue('N')
				->configureNullable(false)
			,
			(new Fields\BooleanField('IS_SYSTEM'))
				->configureValues('N', 'Y')
				->configureDefaultValue('Y')
				->configureNullable(false)
			,
			(new Fields\StringField('NAME'))
				->configureNullable(false)
			,
			(new Fields\StringField('DESCRIPTION'))
				->configureNullable()
			,
			(new Fields\TextField('SECURITY_POLICY'))
				->configureNullable()
			,
			(new Fields\StringField('STRING_ID'))
				->configureNullable()
			,
			(new Fields\Relations\Reference(
				'USER_GROUP',
				UserGroupTable::class,
				Join::on('this.ID', 'ref.GROUP_ID')
			))
				->configureJoinType(Join::TYPE_LEFT)
			,
		];
	}
}
