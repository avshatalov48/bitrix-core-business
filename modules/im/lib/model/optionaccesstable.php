<?php

namespace Bitrix\Im\Model;

use Bitrix\Main\ArgumentTypeException,
	Bitrix\Main\ORM\Data\DataManager,
	Bitrix\Main\ORM\Fields\IntegerField,
	Bitrix\Main\ORM\Fields\StringField,
	Bitrix\Main\ORM\Fields\Validators\LengthValidator,
	Bitrix\Main\SystemException;


/**
 * Class OptionAccessTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> GROUP_ID int mandatory
 * <li> ACCESS_CODE string(100) optional
 * </ul>
 *
 * @package Bitrix\Im
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_OptionAccess_Query query()
 * @method static EO_OptionAccess_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_OptionAccess_Result getById($id)
 * @method static EO_OptionAccess_Result getList(array $parameters = [])
 * @method static EO_OptionAccess_Entity getEntity()
 * @method static \Bitrix\Im\Model\EO_OptionAccess createObject($setDefaultValues = true)
 * @method static \Bitrix\Im\Model\EO_OptionAccess_Collection createCollection()
 * @method static \Bitrix\Im\Model\EO_OptionAccess wakeUpObject($row)
 * @method static \Bitrix\Im\Model\EO_OptionAccess_Collection wakeUpCollection($rows)
 */

class OptionAccessTable extends DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName(): string
	{
		return 'b_im_option_access';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 * @throws SystemException
	 */
	public static function getMap(): array
	{
		return [
			'ID' => (new IntegerField('ID', [
				'primary' => true,
				'autocomplete' => true,
			])),
			'GROUP_ID' => (new IntegerField('GROUP_ID', [
				'required' => true,
			])),
			'ACCESS_CODE' => (new StringField('ACCESS_CODE', [
				'validation' => [__CLASS__, 'validateAccessCode'],
			])),
		];
	}

	/**
	 * Returns validators for ACCESS_CODE field.
	 *
	 * @return array
	 * @throws ArgumentTypeException
	 */
	public static function validateAccessCode(): array
	{
		return [
			new LengthValidator(null, 100),
		];
	}
}
