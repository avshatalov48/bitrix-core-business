<?php

namespace Bitrix\Calendar\Internals;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc,
	Bitrix\Main\ORM\Fields\IntegerField,
	Bitrix\Main\ORM\Fields\BooleanField;

Loc::loadMessages(__FILE__);

/**
 * Class LocationTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> SECTION_ID int mandatory
 * <li> NECESSITY bool ('N', 'Y') optional default 'N'
 * <li> CAPACITY int optional default 0
 * <li> CATEGORY_ID int optional default null
 * </ul>
 *
 * @package Bitrix\Calendar
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Location_Query query()
 * @method static EO_Location_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_Location_Result getById($id)
 * @method static EO_Location_Result getList(array $parameters = [])
 * @method static EO_Location_Entity getEntity()
 * @method static \Bitrix\Calendar\Internals\EO_Location createObject($setDefaultValues = true)
 * @method static \Bitrix\Calendar\Internals\EO_Location_Collection createCollection()
 * @method static \Bitrix\Calendar\Internals\EO_Location wakeUpObject($row)
 * @method static \Bitrix\Calendar\Internals\EO_Location_Collection wakeUpCollection($rows)
 */
class LocationTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_calendar_location';
	}
	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return [
			(new IntegerField('ID'))
				->configurePrimary()
				->configureAutocomplete()
			,
			(new IntegerField('SECTION_ID'))
				->configureRequired()
			,
			(new BooleanField('NECESSITY'))
				->configureStorageValues('N', 'Y')
				->configureDefaultValue('N')
			,
			(new IntegerField('CAPACITY'))
				->configureDefaultValue(0)
			,
			(new IntegerField('CATEGORY_ID'))
				->configureDefaultValue(null)
			,
		];
	}
}