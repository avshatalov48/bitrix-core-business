<?php
namespace Bitrix\Calendar\Internals;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;

/**
 * Class RoomCategoryTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> NAME string(255) optional
 * </ul>
 *
 * @package Bitrix\Calendar
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_RoomCategory_Query query()
 * @method static EO_RoomCategory_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_RoomCategory_Result getById($id)
 * @method static EO_RoomCategory_Result getList(array $parameters = [])
 * @method static EO_RoomCategory_Entity getEntity()
 * @method static \Bitrix\Calendar\Internals\EO_RoomCategory createObject($setDefaultValues = true)
 * @method static \Bitrix\Calendar\Internals\EO_RoomCategory_Collection createCollection()
 * @method static \Bitrix\Calendar\Internals\EO_RoomCategory wakeUpObject($row)
 * @method static \Bitrix\Calendar\Internals\EO_RoomCategory_Collection wakeUpCollection($rows)
 */

class RoomCategoryTable extends DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_calendar_room_category';
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
				->configureTitle(Loc::getMessage('ROOM_CATEGORY_ENTITY_ID_FIELD'))
				->configurePrimary(true)
				->configureAutocomplete(true)
			,
			(new StringField('NAME',
				[
					'validation' => [__CLASS__, 'validateName']
				]
			))
				->configureTitle(Loc::getMessage('ROOM_CATEGORY_ENTITY_NAME_FIELD'))
			,
		];
	}

	/**
	 * Returns validators for NAME field.
	 *
	 * @return array
	 */
	public static function validateName(): array
	{
		return [
			new LengthValidator(null, 255),
		];
	}
}