<?php
namespace Bitrix\Calendar\Internals;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields;

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
			'ID' => new Fields\IntegerField(
				'ID',
				[
					'primary' => true,
					'autocomplete' => true,
					'title' => Loc::getMessage('ROOM_CATEGORY_ENTITY_ID_FIELD'),
				]
			),
			'NAME' => new Fields\StringField(
				'NAME',
				[
					'validation' => [__CLASS__, 'validateName'],
					'title' => Loc::getMessage('ROOM_CATEGORY_ENTITY_NAME_FIELD'),
				]
			),
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
			new Fields\Validators\LengthValidator(null, 255),
		];
	}
}