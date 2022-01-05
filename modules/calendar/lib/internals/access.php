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
 **/
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
			'ACCESS_CODE' => (new StringField(
				'ACCESS_CODE', [
								 'validation' => [__CLASS__, 'validateAccessCode'],
							 ]
			))->configureTitle(Loc::getMessage('ACCESS_ENTITY_ACCESS_CODE_FIELD'))->configurePrimary(true),
			'TASK_ID' => (new IntegerField(
				'TASK_ID', []
			))->configureTitle(Loc::getMessage('ACCESS_ENTITY_TASK_ID_FIELD'))->configurePrimary(true),
			'SECT_ID' => (new StringField(
				'SECT_ID', [
							 'validation' => [__CLASS__, 'validateSectId'],
						 ]
			))->configureTitle(Loc::getMessage('ACCESS_ENTITY_SECT_ID_FIELD'))->configurePrimary(true),
		];
	}

	/**
	 * Returns validators for ACCESS_CODE field.
	 *
	 * @return array
	 */
	public static function validateAccessCode()
	{
		return [
			new LengthValidator(null, 100),
		];
	}

	/**
	 * Returns validators for SECT_ID field.
	 *
	 * @return array
	 */
	public static function validateSectId()
	{
		return [
			new LengthValidator(null, 100),
		];
	}
}