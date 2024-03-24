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
 **/

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
