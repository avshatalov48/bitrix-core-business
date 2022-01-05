<?php

namespace Bitrix\Im\Model;

use Bitrix\Main\ArgumentTypeException,
	Bitrix\Main\Localization\Loc,
	Bitrix\Main\ORM\Data\DataManager,
	Bitrix\Main\ORM\Fields\IntegerField,
	Bitrix\Main\ORM\Fields\StringField,
	Bitrix\Main\ORM\Fields\Validators\LengthValidator,
	Bitrix\Main\SystemException;

Loc::loadMessages(__FILE__);

/**
 * Class OptionStateTable
 *
 * Fields:
 * <ul>
 * <li> GROUP_ID int mandatory
 * <li> NAME string(64) mandatory
 * <li> VALUE string(255) optional
 * </ul>
 *
 * @package Bitrix\Im
 **/

class OptionStateTable extends DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName(): string
	{
		return 'b_im_option_state';
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
			'GROUP_ID' => (new IntegerField('GROUP_ID', [
				'primary' => true,
			])),
			'NAME' => (new StringField('NAME', [
				'primary' => true,
				'validation' => [__CLASS__, 'validateName'],
			])),
			'VALUE' => (new StringField('VALUE', [
				'validation' => [__CLASS__, 'validateValue']
			])),
		];
	}

	/**
	 * Returns validators for NAME field.
	 *
	 * @return array
	 * @throws ArgumentTypeException
	 */
	public static function validateName(): array
	{
		return [
			new LengthValidator(null, 64),
		];
	}

	/**
	 * Returns validators for VALUE field.
	 *
	 * @return array
	 * @throws ArgumentTypeException
	 */
	public static function validateValue(): array
	{
		return [
			new LengthValidator(null, 255),
		];
	}
}
