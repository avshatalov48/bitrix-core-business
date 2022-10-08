<?php

namespace Bitrix\Calendar\Internals;

use Bitrix\Main\Localization\Loc, Bitrix\Main\ORM\Data\DataManager, Bitrix\Main\ORM\Fields\IntegerField, Bitrix\Main\ORM\Fields\StringField, Bitrix\Main\ORM\Fields\Validators\LengthValidator;

Loc::loadMessages(__FILE__);

/**
 * Class AccessTable
 *
 * Fields:
 * <ul>
 * <li> ACCESS_CODE string(100) mandatory
 * <li> TASK_ID int mandatory
 * <li> SECT_ID string(100) mandatory
 * </ul>
 *
 * @package Bitrix\Calendar
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Access_Query query()
 * @method static EO_Access_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_Access_Result getById($id)
 * @method static EO_Access_Result getList(array $parameters = [])
 * @method static EO_Access_Entity getEntity()
 * @method static \Bitrix\Calendar\Internals\EO_Access createObject($setDefaultValues = true)
 * @method static \Bitrix\Calendar\Internals\EO_Access_Collection createCollection()
 * @method static \Bitrix\Calendar\Internals\EO_Access wakeUpObject($row)
 * @method static \Bitrix\Calendar\Internals\EO_Access_Collection wakeUpCollection($rows)
 */
class AccessTable extends DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_calendar_access';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return [
			(new StringField('ACCESS_CODE'))
				->configurePrimary()
				->configureSize(100)
			,
			(new IntegerField('TASK_ID'))
				->configurePrimary()
			,
			(new StringField('SECT_ID'))
				->configurePrimary()
				->configureSize(100)
			,
		];
	}
}