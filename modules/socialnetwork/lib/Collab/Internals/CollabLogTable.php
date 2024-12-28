<?php
namespace Bitrix\Socialnetwork\Collab\Internals;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\ArrayField;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;

/**
 * Class CollabLogTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> COLLAB_ID int mandatory
 * <li> DATETIME datetime mandatory
 * <li> TYPE string(255) optional
 * <li> USER_ID int mandatory
 * <li> ENTITY_TYPE string(255) optional
 * <li> ENTITY_ID int optional
 * <li> DATA text optional
 * </ul>
 *
 * @package Bitrix\Sonet
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_CollabLog_Query query()
 * @method static EO_CollabLog_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_CollabLog_Result getById($id)
 * @method static EO_CollabLog_Result getList(array $parameters = [])
 * @method static EO_CollabLog_Entity getEntity()
 * @method static \Bitrix\Socialnetwork\Collab\Internals\EO_CollabLog createObject($setDefaultValues = true)
 * @method static \Bitrix\Socialnetwork\Collab\Internals\EO_CollabLog_Collection createCollection()
 * @method static \Bitrix\Socialnetwork\Collab\Internals\EO_CollabLog wakeUpObject($row)
 * @method static \Bitrix\Socialnetwork\Collab\Internals\EO_CollabLog_Collection wakeUpCollection($rows)
 */

class CollabLogTable extends DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sonet_collab_log';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		$lengthValidator = function()
		{
			return [
				new LengthValidator(null, 255),
			];
		};

		return [
			(new IntegerField('ID'))
				->configurePrimary()
				->configureAutocomplete()
			,
			(new IntegerField('COLLAB_ID'))
				->configureRequired()
			,
			(new DatetimeField('DATETIME'))
				->configureRequired()
			,
			(new StringField('TYPE', ['validation' => $lengthValidator]))
			,
			(new IntegerField('USER_ID'))
				->configureRequired()
			,
			(new StringField('ENTITY_TYPE', ['validation' => $lengthValidator]))
			,
			(new IntegerField('ENTITY_ID'))
			,
			(new ArrayField('DATA'))
				->configureSerializationJson()
			,
		];
	}
}
